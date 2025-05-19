<?php
/**
 * Custom Field Suite Extended - Core Class
 *
 * @package CFSE
 */

// 直接アクセスを防止
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Custom Field Suite Extended Core Class
 *
 * プラグインのコア機能を提供するシングルトンクラス
 */
class CFSE_Core {

    /**
     * シングルトンインスタンス
     *
     * @var CFSE_Core
     */
    private static $instance = null;

    /**
     * 登録されたフィールド定義
     *
     * @var array
     */
    private $field_definitions = [];

    /**
     * 利用可能なフィールドタイプ
     *
     * @var array
     */
    private $field_types = [];

    /**
     * レンダリングクラスのインスタンス
     *
     * @var CFSE_Render
     */
    public $render = null;

    /**
     * データ操作クラスのインスタンス
     *
     * @var CFSE_Data
     */
    public $data = null;

    /**
     * バージョン
     *
     * @var string
     */
    public $version = '1.0.0';

    /**
     * プラグインのベースディレクトリパス
     *
     * @var string
     */
    public $dir = '';

    /**
     * プラグインのURLベース
     *
     * @var string
     */
    public $url = '';

    /**
     * コンストラクタ
     *
     * シングルトンパターンのため private
     */
    private function __construct() {
        $this->dir = plugin_dir_path(CFSE_PLUGIN_FILE);
        $this->url = plugin_dir_url(CFSE_PLUGIN_FILE);

        // 初期化アクション
        add_action('init', [$this, 'init'], 10);
        add_action('admin_enqueue_scripts', [$this, 'admin_scripts'], 10);

        // カスタムフィールドの処理
        add_action('add_meta_boxes', [$this, 'add_meta_boxes'], 10);
        add_action('save_post', [$this, 'save_post'], 10, 2);

        // AJAX処理
        add_action('wp_ajax_cfse_fetch_relationship_options', [$this, 'ajax_fetch_relationship_options']);
    }

    /**
     * シングルトンインスタンスを取得
     *
     * @return CFSE_Core インスタンス
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * プラグインの初期化
     */
    public function init() {
        // 翻訳ファイルのロード
        load_plugin_textdomain('cfse', false, basename(dirname(CFSE_PLUGIN_FILE)) . '/languages');

        // コンポーネントのロード
        $this->load_components();

        // フィールドタイプの登録
        $this->register_field_types();

        // フィールド定義の登録フックを提供（優先度を高めに）
        do_action('cfse_register_field_definitions', $this);
    }

    /**
     * 依存コンポーネントのロード
     */
    private function load_components() {
        // レンダラーを初期化
        require_once $this->dir . 'includes/class-cfse-render.php';
        $this->render = new CFSE_Render($this);

        // データクラスを初期化
        require_once $this->dir . 'includes/class-cfse-data.php';
        $this->data = new CFSE_Data($this);
    }

    /**
     * 利用可能なフィールドタイプを登録
     */
    private function register_field_types() {
        // フィールドタイプの基底クラスをロード
        require_once $this->dir . 'includes/class-cfse-field-type.php';

        // 各フィールドタイプの実装クラスをロード
        $field_type_files = glob($this->dir . 'includes/field-types/class-cfse-field-*.php');

        if (!empty($field_type_files)) {
            foreach ($field_type_files as $file) {
                require_once $file;

                // クラス名をファイル名から取得（例: class-cfse-field-class-cfse-field-text.php → CFSE_Field_Text）
                $class_name = str_replace(['class-', '.php'], '', basename($file));
                $class_name = implode('_', array_map('ucfirst', explode('-', $class_name)));

                if (class_exists($class_name)) {
                    $field_type_instance = new $class_name();
                    $this->field_types[$field_type_instance->name] = $field_type_instance;
                }
            }
        }

        // サードパーティが追加のフィールドタイプを登録できるようにフィルターを提供
        $this->field_types = apply_filters('cfse_field_types', $this->field_types);
    }

    /**
     * 管理画面用のスクリプトとスタイルを登録
     */
    public function admin_scripts() {
        // 現在の画面情報を取得
        $screen = get_current_screen();

        // 投稿編集画面のみに読み込み
        if ($screen && in_array($screen->base, ['post', 'post-new'])) {
            // スタイル
            wp_enqueue_style(
                'cfse-admin',
                $this->url . 'assets/css/admin.css',
                [],
                $this->version
            );

            // 基本スクリプト
            wp_enqueue_script(
                'cfse-admin',
                $this->url . 'assets/js/admin.js',
                [],
                $this->version,
                true
            );

            // JavaScript用の変数を渡す
            wp_localize_script('cfse-admin', 'cfseConfig', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('cfse_nonce'),
                'postId' => get_the_ID(),
                'labels' => [
                    'addRow' => __('Add Row', 'cfse'),
                    'removeRow' => __('Remove', 'cfse'),
                    'toggleRow' => __('Toggle', 'cfse'),
                    'rowLabel' => __('Row', 'cfse'),
                    'confirmDelete' => __('Are you sure you want to delete this item?', 'cfse'),
                ],
            ]);

            // WordPress標準のメディアアップローダースクリプト
            wp_enqueue_media();

            // WordPressのカラーピッカー
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');

            // 日付ピッカー
            wp_enqueue_script('jquery-ui-datepicker');

            // フィールドタイプ固有のアセットを読み込む
            $this->enqueue_field_assets();
        }
    }

    /**
     * フィールドタイプ固有のアセットを読み込む
     */
    private function enqueue_field_assets() {
        foreach ($this->field_types as $type => $field_type) {
            if (method_exists($field_type, 'enqueue_assets')) {
                $field_type->enqueue_assets();
            }
        }
    }

    /**
     * メタボックスを追加
     */
    public function add_meta_boxes() {
        // 現在の投稿タイプを取得
        $post_type = get_post_type();

        if (!$post_type) {
            global $pagenow;
            if ('post-new.php' === $pagenow && isset($_GET['post_type'])) {
                $post_type = sanitize_text_field($_GET['post_type']);
            } else {
                $post_type = 'post';
            }
        }

        // この投稿タイプに適用されるフィールドグループを取得
        $field_groups = $this->get_matching_field_groups($post_type);

        if (!empty($field_groups)) {
            foreach ($field_groups as $group) {
                add_meta_box(
                    'cfse_' . $group['id'],
                    $group['title'],
                    [$this->render, 'render_meta_box'],
                    $post_type,
                    'normal',
                    'high',
                    ['field_group' => $group]
                );
            }
        }
    }

    /**
     * 投稿保存時のデータ処理
     *
     * @param int $post_id 投稿ID
     * @param WP_Post $post 投稿オブジェクト
     */
    public function save_post($post_id, $post) {
        // 自動保存なら処理しない
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // 権限チェック
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // nonceチェック
        if (!isset($_POST['cfse_nonce']) || !wp_verify_nonce($_POST['cfse_nonce'], 'cfse_save_post')) {
            return;
        }

        // この投稿タイプに適用されるフィールドグループを取得
        $field_groups = $this->get_matching_field_groups($post->post_type);

        if (empty($field_groups)) {
            return;
        }

        // POSTデータから値を取得して保存
        if (isset($_POST['cfse_fields'])) {
            $submitted_fields = $_POST['cfse_fields'];

            // 各フィールドデータの検証・保存
            foreach ($field_groups as $group) {
                $table = isset($group['table']) ? $group['table'] : 'postmeta';

                foreach ($group['fields'] as $field) {
                    $field_id = $field['id'];

                    if (isset($submitted_fields[$field_id])) {
                        $value = $submitted_fields[$field_id];

                        // フィールドタイプに応じた値の処理
                        $field_type = $this->get_field_type($field['type']);

                        if ($field_type) {
                            $processed_value = $field_type->prepare_value_for_database($value, $field);

                            // 値を保存
                            $this->data->save_field_value($post_id, $field_id, $processed_value, $table);
                        }
                    }
                }
            }
        }
    }

    /**
     * フィールド定義を登録するメソッド
     *
     * @param array $field_definition フィールド定義の配列
     * @return bool 登録が成功したかどうか
     */
    public function register_field_definition($field_definition) {
        // 必須パラメータをチェック
        if (!isset($field_definition['id']) || !isset($field_definition['title'])) {
            trigger_error(__('Field definition must have an ID and title', 'cfse'), E_USER_WARNING);
            return false;
        }

        // 重複をチェック
        if (isset($this->field_definitions[$field_definition['id']])) {
            trigger_error(
                sprintf(__("Field definition with ID '%s' already exists", 'cfse'), $field_definition['id']),
                E_USER_WARNING
            );
            return false;
        }

        // デフォルト値をセット
        $field_definition = wp_parse_args($field_definition, [
            'fields' => [],
            'placement' => [],
            'table' => 'postmeta', // デフォルトはpostmetaテーブル
        ]);

        // フィールド定義を登録
        $this->field_definitions[$field_definition['id']] = $field_definition;

        return true;
    }

    /**
     * 投稿タイプに適用されるフィールドグループを取得
     *
     * @param string $post_type 投稿タイプ
     * @return array 適用されるフィールドグループの配列
     */
    public function get_matching_field_groups($post_type) {
        $matches = [];

        foreach ($this->field_definitions as $field_definition) {
            // 配置ルールをチェック
            if ($this->does_placement_match($field_definition['placement'], $post_type)) {
                $matches[] = $field_definition;
            }
        }

        return $matches;
    }

    /**
     * 配置ルールが投稿タイプにマッチするかチェック
     *
     * @param array $placement 配置ルール
     * @param string $post_type 投稿タイプ
     * @return bool マッチするかどうか
     */
    private function does_placement_match($placement, $post_type) {
        // 配置ルールがない場合は常にマッチする
        if (empty($placement)) {
            return true;
        }

        // 投稿タイプをチェック
        if (isset($placement['post_type'])) {
            if (is_array($placement['post_type'])) {
                return in_array($post_type, $placement['post_type']);
            } else {
                return $placement['post_type'] === $post_type;
            }
        }

        // 他の条件は必要に応じて追加

        return true;
    }

    /**
     * フィールドタイプのインスタンスを取得
     *
     * @param string $type フィールドタイプの名前
     * @return CFSE_Field_Type|null フィールドタイプのインスタンスまたはnull
     */
    public function get_field_type($type) {
        return isset($this->field_types[$type]) ? $this->field_types[$type] : null;
    }

    /**
     * 利用可能なフィールドタイプの配列を取得
     *
     * @return array フィールドタイプの配列
     */
    public function get_field_types() {
        return $this->field_types;
    }

    /**
     * フィールド値を取得するメソッド（テンプレート用）
     *
     * @param string $field_id フィールドID
     * @param int|null $post_id 投稿ID（nullの場合は現在の投稿）
     * @return mixed フィールド値
     */
    public function get_field($field_id, $post_id = null) {
        if (null === $post_id) {
            $post_id = get_the_ID();
        }

        if (!$post_id) {
            return null;
        }

        // フィールド定義を取得
        $field_definition = $this->get_field_definition_by_id($field_id);

        if (!$field_definition) {
            return null;
        }

        // データを取得
        $value = $this->data->get_field_value($post_id, $field_id, $field_definition['table']);

        // フィールドタイプに応じた値の整形
        $field_type = $this->get_field_type($field_definition['type']);

        if ($field_type) {
            $value = $field_type->format_value_for_display($value, $field_definition);
        }

        return $value;
    }

    /**
     * フィールドIDからフィールド定義を取得
     *
     * @param string $field_id フィールドID
     * @return array|null フィールド定義または null
     */
    private function get_field_definition_by_id($field_id) {
        foreach ($this->field_definitions as $group) {
            foreach ($group['fields'] as $field) {
                if ($field['id'] === $field_id) {
                    return [
                        'id' => $field_id,
                        'type' => $field['type'],
                        'table' => $group['table'],
                        'options' => isset($field['options']) ? $field['options'] : [],
                    ];
                }
            }
        }

        return null;
    }

    /**
     * リレーションシップフィールドのオプションを取得するAJAXハンドラ
     */
    public function ajax_fetch_relationship_options() {
        // nonce検証
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cfse_nonce')) {
            wp_send_json_error(['message' => __('Security check failed', 'cfse')]);
        }

        $query_args = [];

        // 検索クエリがある場合
        if (isset($_POST['search'])) {
            $query_args['s'] = sanitize_text_field($_POST['search']);
        }

        // 投稿タイプ
        if (isset($_POST['post_type'])) {
            $query_args['post_type'] = sanitize_text_field($_POST['post_type']);
        } else {
            $query_args['post_type'] = 'post';
        }

        // ページ番号
        $query_args['paged'] = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $query_args['posts_per_page'] = 10;

        // クエリを実行
        $query = new WP_Query($query_args);

        $results = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $results[] = [
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'type' => get_post_type(),
                ];
            }
        }

        wp_reset_postdata();

        wp_send_json_success([
            'results' => $results,
            'total' => $query->found_posts,
            'totalPages' => $query->max_num_pages,
        ]);
    }
    }
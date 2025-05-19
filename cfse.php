<?php
/**
 * Plugin Name: Custom Field Suite Extension
 * Plugin URI: https://github.com/yourusername/custom-field-suite-extension
 * Description: Custom Field Suiteの拡張版。jQueryへの依存を解消し、フィールド定義をコードで行うシンプルな実装。
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * Text Domain: cfse
 * Domain Path: /languages
 * License: GPL2
 */

// 直接アクセスを防止
if (!defined('ABSPATH')) {
    exit;
}

// プラグイン情報の定数
define('CFSE_VERSION', '1.0.0');
define('CFSE_PLUGIN_FILE', __FILE__);
define('CFSE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CFSE_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * プラグインのメインクラス
 */
class Custom_Field_Suite_Extended {

    /**
     * シングルトンインスタンス
     *
     * @var Custom_Field_Suite_Extended
     */
    private static $instance = null;

    /**
     * CFSEコアのインスタンス
     *
     * @var CFSE_Core
     */
    private $core = null;

    /**
     * シングルトンインスタンスを取得
     *
     * @return Custom_Field_Suite_Extended
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * コンストラクタ
     */
    private function __construct() {
        // プラグイン初期化
        $this->init();
    }

    /**
     * プラグインを初期化
     */
    private function init() {
        // クラスファイルをロード
        $this->load_classes();

        // 翻訳をロード
        load_plugin_textdomain('cfse', false, dirname(plugin_basename(__FILE__)) . '/languages');

        // アクティベーション/非アクティベーションフック
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);

        // アンインストールフック
        if (function_exists('register_uninstall_hook')) {
            register_uninstall_hook(__FILE__, [__CLASS__, 'uninstall']);
        }
    }

    /**
     * 必要なクラスファイルをロード
     */
    private function load_classes() {
        // コアクラス
        require_once CFSE_PLUGIN_DIR . 'includes/class-cfse-core.php';

        // 開発環境の場合はオートローダーを設定
        if (defined('WP_DEBUG') && WP_DEBUG) {
            spl_autoload_register([$this, 'autoload']);
        }

        // core インスタンスを取得（内部でコンポーネントをロード）
        $this->core = CFSE_Core::get_instance();
    }

    /**
     * オートローダー
     *
     * @param string $class_name クラス名
     */
    public function autoload($class_name) {
        // 自身のプラグインのクラスのみを対象にする
        if (strpos($class_name, 'CFSE_') !== 0) {
            return;
        }

        // クラス名をファイル名に変換
        $file_name = 'class-' . strtolower(str_replace('_', '-', $class_name)) . '.php';

        // フィールドタイプクラスは特別な場所
        if (strpos($class_name, 'CFSE_Field_') === 0) {
            $field_type = strtolower(substr($class_name, 11)); // CFSE_Field_ の長さは11
            $path = CFSE_PLUGIN_DIR . 'includes/field-types/' . $file_name;
        } else {
            $path = CFSE_PLUGIN_DIR . 'includes/' . $file_name;
        }

        if (file_exists($path)) {
            require_once $path;
        }
    }

    /**
     * アクティベーション時の処理
     */
    public function activate() {
        // バージョン情報を保存
        update_option('cfse_version', CFSE_VERSION);

        // 初回のみフラグを設定
        if (!get_option('cfse_installed')) {
            update_option('cfse_installed', true);

            // 必要であればカスタムテーブルを作成
            $this->create_custom_tables();
        }

        // データアップグレードが必要か確認
        $old_version = get_option('cfse_version', '0');
        if (version_compare($old_version, CFSE_VERSION, '<')) {
            $this->upgrade($old_version);
        }

        // パーマリンク更新
        flush_rewrite_rules();
    }

    /**
     * 非アクティベーション時の処理
     */
    public function deactivate() {
        // パーマリンク更新
        flush_rewrite_rules();
    }

    /**
     * アンインストール時の処理
     */
    public static function uninstall() {
        // オプションを削除
        delete_option('cfse_version');
        delete_option('cfse_installed');

        // カスタムテーブルを削除（必要であれば）
        self::drop_custom_tables();
    }

    /**
     * カスタムテーブルを作成
     */
    private function create_custom_tables() {
        global $wpdb;

        // dbDeltaを使用するためにrequire
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $charset_collate = $wpdb->get_charset_collate();

        // カスタムデータ用テーブル（必要に応じて）
        $table_name = $wpdb->prefix . 'cfse_data';

        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            object_id bigint(20) unsigned NOT NULL,
            field_id varchar(255) NOT NULL,
            value longtext,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY object_id (object_id),
            KEY field_id (field_id),
            KEY object_field (object_id, field_id)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    /**
     * カスタムテーブルを削除
     */
    private static function drop_custom_tables() {
        global $wpdb;

        // カスタムデータ用テーブル（必要に応じて）
        $table_name = $wpdb->prefix . 'cfse_data';

        $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
    }

    /**
     * データアップグレード処理
     *
     * @param string $old_version 以前のバージョン
     */
    private function upgrade($old_version) {
        // バージョンに応じたアップグレード処理を行う

        // 例: バージョン 0.5.0 からのアップグレード
        if (version_compare($old_version, '0.5.0', '<')) {
            // 0.5.0 未満からのアップグレード処理
        }

        // 例: バージョン 0.8.0 からのアップグレード
        if (version_compare($old_version, '0.8.0', '<')) {
            // 0.8.0 未満からのアップグレード処理
        }
    }

    /**
     * CFSとの互換性のために、フィールド値を取得
     *
     * @param string $field_id フィールドID
     * @param int|null $post_id 投稿ID
     * @param array $options オプション
     * @return mixed フィールド値
     */
    public function get($field_id = false, $post_id = null, $options = []) {
        // フィールドIDが指定されていない場合は全てのフィールド値を取得
        if ($field_id === false) {
            return $this->get_all_fields($post_id);
        }

        // 通常のフィールド値を取得
        return $this->core->get_field($field_id, $post_id);
    }

    /**
     * CFSとの互換性のために、全てのフィールド値を取得
     *
     * @param int|null $post_id 投稿ID
     * @return array フィールド値の配列
     */
    public function get_all_fields($post_id = null) {
        if ($post_id === null) {
            $post_id = get_the_ID();
        }

        return $this->core->data->get_all_field_values($post_id);
    }

    /**
     * CFSとの互換性のために、フィールド値を保存
     *
     * @param array $field_data フィールドデータ
     * @param int|null $post_id 投稿ID
     * @param array $options オプション
     * @return bool 成功したかどうか
     */
    public function save($field_data = [], $post_id = null, $options = []) {
        if (empty($field_data) || $post_id === null) {
            return false;
        }

        $success = true;

        foreach ($field_data as $field_id => $value) {
            // フィールド定義を取得
            $field_definition = $this->get_field_definition($field_id);

            if ($field_definition) {
                // フィールドタイプを取得
                $field_type = $this->core->get_field_type($field_definition['type']);

                if ($field_type) {
                    // フィールドタイプに応じた値の処理
                    $processed_value = $field_type->prepare_value_for_database($value, $field_definition);

                    // 値を保存
                    $table = isset($field_definition['table']) ? $field_definition['table'] : 'postmeta';
                    $result = $this->core->data->save_field_value($post_id, $field_id, $processed_value, $table);

                    if (!$result) {
                        $success = false;
                    }
                }
            }
        }

        return $success;
    }

    /**
     * フィールドIDからフィールド定義を取得
     *
     * @param string $field_id フィールドID
     * @return array|null フィールド定義
     */
    private function get_field_definition($field_id) {
        // 全てのフィールドグループを取得
        $field_groups = $this->core->get_all_field_groups();

        foreach ($field_groups as $group) {
            if (isset($group['fields']) && is_array($group['fields'])) {
                foreach ($group['fields'] as $field) {
                    if ($field['id'] === $field_id) {
                        return array_merge($field, [
                            'table' => $group['table'] ?? 'postmeta',
                        ]);
                    }
                }
            }
        }

        return null;
    }

    /**
     * フィールド定義を登録
     *
     * @param array $field_definition フィールド定義
     */
    public function register_field_definition($field_definition) {
        $this->core->register_field_definition($field_definition);
    }
    }

/**
 * CFSとの互換性のために、プラグインのシングルトンインスタンスを取得
 *
 * @return Custom_Field_Suite_Extended
 */
function CFS() {
    return Custom_Field_Suite_Extended::get_instance();
}

// プラグインを初期化
CFS();

/**
 * フィールド値を取得するためのグローバル関数
 *
 * @param string $field_id フィールドID
 * @param int|null $object_id オブジェクトID（省略時は現在の投稿ID）
 * @return mixed フィールド値
 */
function cfse_get_field($field_id, $object_id = null) {
    return CFS()->get($field_id, $object_id);
}

/**
 * フィールド値が存在するか確認するグローバル関数
 *
 * @param string $field_id フィールドID
 * @param int|null $object_id オブジェクトID（省略時は現在の投稿ID）
 * @return bool 値が存在するかどうか
 */
function cfse_has_field($field_id, $object_id = null) {
    $value = cfse_get_field($field_id, $object_id);
    return $value !== null && $value !== '' && $value !== [];
}

/**
 * 繰り返しフィールドの行を処理するグローバル関数
 *
 * @param string $field_id ループフィールドID
 * @param int|null $object_id オブジェクトID（省略時は現在の投稿ID）
 * @return bool 行が存在するかどうか
 */
function cfse_have_rows($field_id, $object_id = null) {
    static $field_values = [];
    static $current_row = [];

    // キャッシュキー
    $cache_key = ($object_id ?: get_the_ID()) . '_' . $field_id;

    // 初回呼び出し時に値を取得してキャッシュ
    if (!isset($field_values[$cache_key])) {
        $field_values[$cache_key] = cfse_get_field($field_id, $object_id);
        $current_row[$cache_key] = 0;
    }

    // 行が存在するか確認
    if (is_array($field_values[$cache_key]) && isset($field_values[$cache_key][$current_row[$cache_key]])) {
        return true;
    }

    // ループ終了時にカウンターをリセット
    $current_row[$cache_key] = 0;

    return false;
}

/**
 * 繰り返しフィールドの次の行に進むグローバル関数
 *
 * @param string $field_id ループフィールドID
 * @param int|null $object_id オブジェクトID（省略時は現在の投稿ID）
 */
function cfse_the_row($field_id, $object_id = null) {
    static $current_row = [];

    // キャッシュキー
    $cache_key = ($object_id ?: get_the_ID()) . '_' . $field_id;

    // カウンターを初期化
    if (!isset($current_row[$cache_key])) {
        $current_row[$cache_key] = 0;
    }

    // カウンターをインクリメント
    $current_row[$cache_key]++;
}

/**
 * 現在の行のサブフィールド値を取得するグローバル関数
 *
 * @param string $sub_field_id サブフィールドID
 * @param string $field_id ループフィールドID
 * @param int|null $object_id オブジェクトID（省略時は現在の投稿ID）
 * @return mixed サブフィールド値
 */
function cfse_get_sub_field($sub_field_id, $field_id, $object_id = null) {
    static $field_values = [];
    static $current_row = [];

    // キャッシュキー
    $cache_key = ($object_id ?: get_the_ID()) . '_' . $field_id;

    // 初回呼び出し時に値を取得してキャッシュ
    if (!isset($field_values[$cache_key])) {
        $field_values[$cache_key] = cfse_get_field($field_id, $object_id);
        $current_row[$cache_key] = 0;
    }

    // カウンターを初期化
    if (!isset($current_row[$cache_key])) {
        $current_row[$cache_key] = 0;
    }

    // サブフィールドの値を取得
    if (is_array($field_values[$cache_key]) &&
        isset($field_values[$cache_key][$current_row[$cache_key]]) &&
        isset($field_values[$cache_key][$current_row[$cache_key]][$sub_field_id])) {
        return $field_values[$cache_key][$current_row[$cache_key]][$sub_field_id];
    }

    return null;
}
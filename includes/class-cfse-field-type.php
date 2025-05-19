<?php
/**
 * Custom Field Suite Extended - Field Type Base Class
 *
 * @package CFSE
 */

// 直接アクセスを防止
if (!defined('ABSPATH')) {
    exit;
}

/**
 * フィールドタイプの基底クラス
 *
 * すべてのフィールドタイプはこのクラスを継承して実装する
 */
abstract class CFSE_Field_Type {

    /**
     * フィールドタイプの識別名
     *
     * @var string
     */
    public $name = '';

    /**
     * フィールドタイプの表示名
     *
     * @var string
     */
    public $label = '';

    /**
     * フィールドタイプのデフォルト設定
     *
     * @var array
     */
    protected $defaults = [];

    /**
     * コンストラクタ
     */
    public function __construct() {
        // 共通の初期化処理
    }

    /**
     * フィールド入力部分のHTMLを生成
     *
     * @param array $field フィールド設定
     * @param mixed $value 現在の値
     * @return string HTML
     */
    abstract public function html($field, $value);

    /**
     * フィールド設定をデフォルト値でマージ
     *
     * @param array $field フィールド設定
     * @return array マージされたフィールド設定
     */
    protected function prepare_field($field) {
        return wp_parse_args($field, $this->defaults);
    }

    /**
     * フィールド値をデータベース保存前に処理
     *
     * @param mixed $value ユーザー入力値
     * @param array $field フィールド設定
     * @return mixed 処理された値
     */
    public function prepare_value_for_database($value, $field) {
        return $value;
    }

    /**
     * データベースから取得した値を表示用に整形
     *
     * @param mixed $value データベース値
     * @param array $field フィールド設定
     * @return mixed 表示用に整形された値
     */
    public function format_value_for_display($value, $field) {
        return $value;
    }

    /**
     * フィールドタイプに必要なアセット（CSS、JavaScript）を読み込む
     */
    public function enqueue_assets() {
        // サブクラスでオーバーライド
    }

    /**
     * フィールド設定用の入力欄をレンダリング
     *
     * @param array $field フィールド設定
     * @return string HTML
     */
    public function render_field_settings($field) {
        // デフォルトではラベルと説明文の設定のみ
        $html = '';

        // ラベル設定
        $html .= $this->render_setting_row(
            'label',
            __('Label', 'cfse'),
            $this->render_text_setting('label', $field['label'] ?? '')
        );

        // 説明文設定
        $html .= $this->render_setting_row(
            'description',
            __('Description', 'cfse'),
            $this->render_textarea_setting('description', $field['description'] ?? '')
        );

        return $html;
    }

    /**
     * 設定行のHTMLを生成
     *
     * @param string $key 設定キー
     * @param string $label 設定ラベル
     * @param string $input 入力欄HTML
     * @return string HTML
     */
    protected function render_setting_row($key, $label, $input) {
        $html = '<div class="cfse-field-setting cfse-field-setting-' . esc_attr($key) . '">';
        $html .= '<label for="cfse-setting-' . esc_attr($key) . '">' . esc_html($label) . '</label>';
        $html .= $input;
        $html .= '</div>';

        return $html;
    }

    /**
     * テキスト設定用の入力欄を生成
     *
     * @param string $key 設定キー
     * @param string $value 現在の値
     * @return string HTML
     */
    protected function render_text_setting($key, $value) {
        return sprintf(
            '<input type="text" id="cfse-setting-%1$s" name="cfse_settings[%1$s]" value="%2$s">',
            esc_attr($key),
            esc_attr($value)
        );
    }

    /**
     * テキストエリア設定用の入力欄を生成
     *
     * @param string $key 設定キー
     * @param string $value 現在の値
     * @return string HTML
     */
    protected function render_textarea_setting($key, $value) {
        return sprintf(
            '<textarea id="cfse-setting-%1$s" name="cfse_settings[%1$s]">%2$s</textarea>',
            esc_attr($key),
            esc_textarea($value)
        );
    }

    /**
     * セレクト設定用の入力欄を生成
     *
     * @param string $key 設定キー
     * @param string $value 現在の値
     * @param array $options 選択肢
     * @return string HTML
     */
    protected function render_select_setting($key, $value, $options) {
        $html = sprintf(
            '<select id="cfse-setting-%1$s" name="cfse_settings[%1$s]">',
            esc_attr($key)
        );

        foreach ($options as $option_value => $option_label) {
            $html .= sprintf(
                '<option value="%1$s" %2$s>%3$s</option>',
                esc_attr($option_value),
                selected($value, $option_value, false),
                esc_html($option_label)
            );
        }

        $html .= '</select>';

        return $html;
    }

    /**
     * チェックボックス設定用の入力欄を生成
     *
     * @param string $key 設定キー
     * @param bool $checked チェック状態
     * @param string $label ラベル
     * @return string HTML
     */
    protected function render_checkbox_setting($key, $checked, $label) {
        return sprintf(
            '<label><input type="checkbox" id="cfse-setting-%1$s" name="cfse_settings[%1$s]" value="1" %2$s> %3$s</label>',
            esc_attr($key),
            checked($checked, true, false),
            esc_html($label)
        );
    }

    /**
     * 入力値をサニタイズ
     *
     * @param mixed $value 入力値
     * @return mixed サニタイズされた値
     */
    public function sanitize_value($value) {
        // デフォルトの実装はそのまま返す
        // 子クラスでオーバーライドして型に応じた処理を実装
        return $value;
    }

    /**
     * 入力値をバリデーション
     *
     * @param mixed $value 入力値
     * @param array $field フィールド設定
     * @return bool 有効な値かどうか
     */
    public function validate_value($value, $field) {
        // デフォルトの実装は常に有効とする
        // 子クラスでオーバーライドして型に応じた検証を実装
        return true;
    }

    /**
     * HTML属性を文字列として生成
     *
     * @param array $attributes 属性の配列
     * @return string HTML属性文字列
     */
    protected function parse_attributes($attributes) {
        if (!is_array($attributes)) {
            return '';
        }

        $html = '';

        foreach ($attributes as $key => $value) {
            if (is_bool($value)) {
                if ($value) {
                    $html .= ' ' . esc_attr($key);
                }
            } else {
                $html .= sprintf(' %s="%s"', esc_attr($key), esc_attr($value));
            }
        }

        return $html;
    }

    /**
     * フィールドに説明文があれば出力
     *
     * @param array $field フィールド設定
     * @return string HTML
     */
    protected function maybe_render_description($field) {
        if (!empty($field['description'])) {
            return '<p class="cfse-field-description">' . esc_html($field['description']) . '</p>';
        }

        return '';
    }

    /**
     * フィールドコンテナのHTMLを生成
     *
     * @param array $field フィールド設定
     * @param string $input_html 入力欄HTML
     * @return string HTML
     */
    protected function render_field_container($field, $input_html) {
        $field = $this->prepare_field($field);

        $html = '<div class="cfse-field cfse-field-' . esc_attr($this->name) . '">';

        // ラベルがある場合は表示
        if (!empty($field['label'])) {
            $html .= '<label for="' . esc_attr($field['id']) . '">' . esc_html($field['label']) . '</label>';
        }

        // 入力欄
        $html .= $input_html;

        // 説明文
        $html .= $this->maybe_render_description($field);

        $html .= '</div>';

        return $html;
    }
    }
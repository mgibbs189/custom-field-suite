<?php
/**
 * Custom Field Suite Extended - Checkbox Field Type
 *
 * @package CFSE
 */

// 直接アクセスを防止
if (!defined('ABSPATH')) {
    exit;
}

/**
 * チェックボックスフィールドタイプクラス
 */
class CFSE_Field_Checkbox extends CFSE_Field_Type {

    /**
     * フィールドタイプの識別名
     *
     * @var string
     */
    public $name = 'checkbox';

    /**
     * フィールドタイプの表示名
     *
     * @var string
     */
    public $label = 'Checkbox';

    /**
     * デフォルト設定
     *
     * @var array
     */
    protected $defaults = [
        'default_value' => 0,
        'label_on' => '',
        'label_off' => '',
        'class' => '',
        'required' => false
    ];

    /**
     * フィールド入力部分のHTMLを生成
     *
     * @param array $field フィールド設定
     * @param mixed $value 現在の値
     * @return string HTML
     */
    public function html($field, $value) {
        // フィールド設定を準備
        $field = $this->prepare_field($field);

        // デフォルト値を使用
        if ($value === null) {
            $value = $field['default_value'];
        }

        // チェック状態を判定
        $checked = !empty($value);

        // フィールド名の設定
        $field_name = isset($field['name']) ? $field['name'] : 'cfse_fields[' . $field['id'] . ']';

        // 属性を準備
        $attributes = [
            'type' => 'checkbox',
            'id' => $field['id'],
            'name' => $field_name,
            'value' => '1',
            'class' => 'cfse-checkbox-field ' . $field['class']
        ];

        // チェック状態
        if ($checked) {
            $attributes['checked'] = 'checked';
        }

        // 必須フィールド
        if (!empty($field['required'])) {
            $attributes['required'] = 'required';
        }

        // HTML生成
        $html = '<label class="cfse-checkbox-wrapper">';
        $html .= '<input ' . $this->parse_attributes($attributes) . '>';

        // ラベルテキスト
        $label = !empty($field['label_on']) ? $field['label_on'] : __('Yes', 'cfse');
        $html .= ' <span class="cfse-checkbox-label">' . esc_html($label) . '</span>';

        $html .= '</label>';

        // hidden値（チェックボックスが送信されない場合の対策）
        $html .= '<input type="hidden" name="' . esc_attr($field_name) . '_exists" value="1">';

        return $html;
    }

    /**
     * データベース保存前の値を処理
     *
     * @param mixed $value ユーザー入力値
     * @param array $field フィールド設定
     * @return int 処理された値（0または1）
     */
    public function prepare_value_for_database($value, $field) {
        // チェックボックスは「1」または「0」として保存
        return !empty($value) ? 1 : 0;
    }

    /**
     * データベースから取得した値を表示用に整形
     *
     * @param mixed $value データベース値
     * @param array $field フィールド設定
     * @return bool ブール値
     */
    public function format_value_for_display($value, $field) {
        // フロントエンド表示用は「true」または「false」に変換
        return !empty($value);
    }

    /**
     * フィールド設定用の入力欄をレンダリング
     *
     * @param array $field フィールド設定
     * @return string HTML
     */
    public function render_field_settings($field) {
        // 親クラスの設定を取得
        $html = parent::render_field_settings($field);

        // 準備
        $field = $this->prepare_field($field);

        // デフォルト値設定
        $html .= $this->render_setting_row(
            'default_value',
            __('Default Value', 'cfse'),
            $this->render_checkbox_setting('default_value', $field['default_value'], __('Checked by default', 'cfse'))
        );

        // ラベル（オン）設定
        $html .= $this->render_setting_row(
            'label_on',
            __('Label (Checked)', 'cfse'),
            $this->render_text_setting('label_on', $field['label_on']) .
            '<p class="description">' . __('Text displayed next to the checkbox (default: "Yes")', 'cfse') . '</p>'
        );

        // ラベル（オフ）設定
        $html .= $this->render_setting_row(
            'label_off',
            __('Label (Unchecked)', 'cfse'),
            $this->render_text_setting('label_off', $field['label_off']) .
            '<p class="description">' . __('Text displayed for unchecked value in templates (default: "No")', 'cfse') . '</p>'
        );

        // CSSクラス設定
        $html .= $this->render_setting_row(
            'class',
            __('CSS Class', 'cfse'),
            $this->render_text_setting('class', $field['class'])
        );

        return $html;
    }

    /**
     * 入力値をバリデーション
     *
     * @param mixed $value 入力値
     * @param array $field フィールド設定
     * @return bool 有効な値かどうか
     */
    public function validate_value($value, $field) {
        // 必須フィールドのチェック
        if (!empty($field['required']) && empty($value)) {
            return false;
        }

        return true;
    }
    }
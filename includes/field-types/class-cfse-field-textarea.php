<?php
/**
 * Custom Field Suite Extended - Textarea Field Type
 *
 * @package CFSE
 */

// 直接アクセスを防止
if (!defined('ABSPATH')) {
    exit;
}

/**
 * テキストエリアフィールドタイプクラス
 */
class CFSE_Field_Textarea extends CFSE_Field_Type {

    /**
     * フィールドタイプの識別名
     *
     * @var string
     */
    public $name = 'textarea';

    /**
     * フィールドタイプの表示名
     *
     * @var string
     */
    public $label = 'Textarea';

    /**
     * デフォルト設定
     *
     * @var array
     */
    protected $defaults = [
        'default_value' => '',
        'placeholder' => '',
        'rows' => 5,
        'cols' => 0,
        'class' => '',
        'readonly' => false,
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
        if ($value === null && !empty($field['default_value'])) {
            $value = $field['default_value'];
        }

        // 値が配列の場合は文字列に変換
        if (is_array($value)) {
            $value = implode("\n", $value);
        }

        // フィールド名の設定
        $field_name = isset($field['name']) ? $field['name'] : 'cfse_fields[' . $field['id'] . ']';

        // 属性を準備
        $attributes = [
            'id' => $field['id'],
            'name' => $field_name,
            'class' => 'cfse-textarea-field ' . $field['class'],
            'rows' => $field['rows']
        ];

        // 列数が指定されている場合
        if (!empty($field['cols'])) {
            $attributes['cols'] = (int) $field['cols'];
        }

        // プレースホルダー
        if (!empty($field['placeholder'])) {
            $attributes['placeholder'] = $field['placeholder'];
        }

        // 読み取り専用
        if (!empty($field['readonly'])) {
            $attributes['readonly'] = 'readonly';
        }

        // 必須フィールド
        if (!empty($field['required'])) {
            $attributes['required'] = 'required';
        }

        // HTML生成
        $html = '<textarea ' . $this->parse_attributes($attributes) . '>' . esc_textarea($value) . '</textarea>';

        return $html;
    }

    /**
     * データベース保存前の値を処理
     *
     * @param mixed $value ユーザー入力値
     * @param array $field フィールド設定
     * @return string 処理された値
     */
    public function prepare_value_for_database($value, $field) {
        // 値が配列の場合は文字列に変換
        if (is_array($value)) {
            $value = implode("\n", $value);
        }

        // 値をサニタイズ（改行を保持）
        return sanitize_textarea_field($value);
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
            $this->render_textarea_setting('default_value', $field['default_value'])
        );

        // プレースホルダー設定
        $html .= $this->render_setting_row(
            'placeholder',
            __('Placeholder', 'cfse'),
            $this->render_text_setting('placeholder', $field['placeholder'])
        );

        // 行数設定
        $html .= $this->render_setting_row(
            'rows',
            __('Rows', 'cfse'),
            $this->render_text_setting('rows', $field['rows'])
        );

        // 列数設定
        $html .= $this->render_setting_row(
            'cols',
            __('Columns', 'cfse'),
            $this->render_text_setting('cols', $field['cols'])
        );

        // CSSクラス設定
        $html .= $this->render_setting_row(
            'class',
            __('CSS Class', 'cfse'),
            $this->render_text_setting('class', $field['class'])
        );

        // 読み取り専用設定
        $html .= $this->render_setting_row(
            'readonly',
            __('Read Only?', 'cfse'),
            $this->render_checkbox_setting('readonly', $field['readonly'], __('Make this field read only', 'cfse'))
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
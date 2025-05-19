<?php
/**
 * Custom Field Suite Extended - Select Field Type
 *
 * @package CFSE
 */

// 直接アクセスを防止
if (!defined('ABSPATH')) {
    exit;
}

/**
 * セレクトフィールドタイプクラス
 */
class CFSE_Field_Select extends CFSE_Field_Type {

    /**
     * フィールドタイプの識別名
     *
     * @var string
     */
    public $name = 'select';

    /**
     * フィールドタイプの表示名
     *
     * @var string
     */
    public $label = 'Select';

    /**
     * デフォルト設定
     *
     * @var array
     */
    protected $defaults = [
        'default_value' => '',
        'choices' => [],
        'multiple' => false,
        'placeholder' => '',
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
        if ($value === null && !empty($field['default_value'])) {
            $value = $field['default_value'];
        }

        // 複数選択の場合、値を配列に変換
        if (!is_array($value) && $field['multiple']) {
            $value = [$value];
        }

        // 単一選択の場合、値を文字列に変換
        if (is_array($value) && !$field['multiple']) {
            $value = reset($value);
        }

        // フィールド名の設定
        $field_name = isset($field['name']) ? $field['name'] : 'cfse_fields[' . $field['id'] . ']';

        // 複数選択の場合は配列形式の名前にする
        if ($field['multiple']) {
            $field_name .= '[]';
        }

        // 属性を準備
        $attributes = [
            'id' => $field['id'],
            'name' => $field_name,
            'class' => 'cfse-select-field ' . $field['class']
        ];

        // 複数選択
        if ($field['multiple']) {
            $attributes['multiple'] = 'multiple';
        }

        // 必須フィールド
        if (!empty($field['required'])) {
            $attributes['required'] = 'required';
        }

        // HTML生成開始
        $html = '<select ' . $this->parse_attributes($attributes) . '>';

        // プレースホルダオプション
        if (!empty($field['placeholder'])) {
            $html .= '<option value="">' . esc_html($field['placeholder']) . '</option>';
        }

        // 選択肢
        if (!empty($field['choices']) && is_array($field['choices'])) {
            foreach ($field['choices'] as $choice_value => $choice_label) {
                $selected = $this->is_selected($choice_value, $value) ? ' selected="selected"' : '';
                $html .= '<option value="' . esc_attr($choice_value) . '"' . $selected . '>' . esc_html($choice_label) . '</option>';
            }
        }

        $html .= '</select>';

        return $html;
    }

    /**
     * オプションが選択されているかチェック
     *
     * @param string $option_value オプション値
     * @param mixed $current_value 現在の値
     * @return bool 選択されているかどうか
     */
    private function is_selected($option_value, $current_value) {
        if (is_array($current_value)) {
            return in_array($option_value, $current_value);
        } else {
            return (string) $option_value === (string) $current_value;
        }
    }

    /**
     * データベース保存前の値を処理
     *
     * @param mixed $value ユーザー入力値
     * @param array $field フィールド設定
     * @return mixed 処理された値
     */
    public function prepare_value_for_database($value, $field) {
        // 複数選択でない場合は文字列として保存
        if (empty($field['multiple'])) {
            // 値が配列の場合は最初の要素を使用
            if (is_array($value)) {
                $value = reset($value);
            }

            return sanitize_text_field($value);
        }

        // 複数選択の場合は配列として保存
        if (!is_array($value)) {
            $value = [$value];
        }

        // 各値をサニタイズ
        foreach ($value as &$item) {
            $item = sanitize_text_field($item);
        }

        return $value;
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

        // 選択肢設定
        $choices_value = '';
        if (!empty($field['choices'])) {
            foreach ($field['choices'] as $value => $label) {
                $choices_value .= $value . ' : ' . $label . "\n";
            }
        }

        $html .= $this->render_setting_row(
            'choices',
            __('Choices', 'cfse'),
            $this->render_textarea_setting('choices', $choices_value) .
            '<p class="description">' . __('Enter each choice on a new line in the format "value : label"', 'cfse') . '</p>'
        );

        // 複数選択設定
        $html .= $this->render_setting_row(
            'multiple',
            __('Multiple Select?', 'cfse'),
            $this->render_checkbox_setting('multiple', $field['multiple'], __('Allow multiple selections', 'cfse'))
        );

        // プレースホルダー設定
        $html .= $this->render_setting_row(
            'placeholder',
            __('Placeholder', 'cfse'),
            $this->render_text_setting('placeholder', $field['placeholder'])
        );

        // デフォルト値設定
        $html .= $this->render_setting_row(
            'default_value',
            __('Default Value', 'cfse'),
            $this->render_text_setting('default_value', $field['default_value']) .
            '<p class="description">' . __('For multiple select, enter comma-separated values', 'cfse') . '</p>'
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

        // 選択肢に存在しない値はエラー
        if (!empty($field['choices']) && !empty($value)) {
            if (is_array($value)) {
                foreach ($value as $item) {
                    if (!isset($field['choices'][$item])) {
                        return false;
                    }
                }
            } else {
                if (!isset($field['choices'][$value])) {
                    return false;
                }
            }
        }

        return true;
    }
    }
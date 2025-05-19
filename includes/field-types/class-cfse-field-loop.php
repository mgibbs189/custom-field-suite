<?php
/**
 * Custom Field Suite Extended - Loop Field Type
 *
 * @package CFSE
 */

// 直接アクセスを防止
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ループフィールドタイプクラス
 */
class CFSE_Field_Loop extends CFSE_Field_Type {

    /**
     * フィールドタイプの識別名
     *
     * @var string
     */
    public $name = 'loop';

    /**
     * フィールドタイプの表示名
     *
     * @var string
     */
    public $label = 'Loop';

    /**
     * デフォルト設定
     *
     * @var array
     */
    protected $defaults = [
        'sub_fields' => [],
        'min' => 0,
        'max' => 0,
        'row_label_format' => '',
        'button_label' => '',
        'collapsed' => '',
        'class' => ''
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

        // CFSE_Coreインスタンスを取得
        $core = CFSE_Core::get_instance();

        // 値が配列でない場合は空の配列にする
        if (!is_array($value)) {
            $value = [];
        }

        // 最小数のチェック
        $min = (int) $field['min'];
        if ($min > 0 && count($value) < $min) {
            // 不足分を空の行で埋める
            for ($i = count($value); $i < $min; $i++) {
                $value[] = [];
            }
        }

        // ボタンラベル
        $button_label = !empty($field['button_label']) ? $field['button_label'] : __('Add Row', 'cfse');

        // 最大数（データ属性用）
        $max_attr = !empty($field['max']) ? ' data-max="' . (int) $field['max'] . '"' : '';
        $min_attr = !empty($field['min']) ? ' data-min="' . (int) $field['min'] . '"' : '';

        // 行ラベルフォーマット（データ属性用）
        $label_format_attr = !empty($field['row_label_format']) ? ' data-row-label-format="' . esc_attr($field['row_label_format']) . '"' : '';

        // フィールド名
        $field_name = isset($field['name']) ? $field['name'] : 'cfse_fields[' . $field['id'] . ']';

        // HTML生成開始
        $html = '<div class="cfse-loop-field ' . esc_attr($field['class']) . '" data-field-id="' . esc_attr($field['id']) . '"' . $max_attr . $min_attr . $label_format_attr . '>';

        // ループヘッダー
        $html .= '<div class="cfse-loop-header">';

        // ループ情報（行数など）
        $html .= '<div class="cfse-loop-info">';
        $html .= '<span class="cfse-loop-count">' . sprintf(__('%d rows', 'cfse'), count($value)) . '</span>';

        // 最小/最大表示
        if ($min > 0 || !empty($field['max'])) {
            $html .= ' <span class="cfse-loop-limits">(';

            if ($min > 0 && !empty($field['max'])) {
                $html .= sprintf(__('Min: %1$d, Max: %2$d', 'cfse'), $min, (int) $field['max']);
            } elseif ($min > 0) {
                $html .= sprintf(__('Min: %d', 'cfse'), $min);
            } elseif (!empty($field['max'])) {
                $html .= sprintf(__('Max: %d', 'cfse'), (int) $field['max']);
            }

            $html .= ')</span>';
        }

        $html .= '</div>'; // .cfse-loop-info

        // 追加ボタン
        $html .= '<div class="cfse-loop-actions">';

        // 最大数に達している場合はボタンを無効化
        $disabled = (!empty($field['max']) && count($value) >= (int) $field['max']) ? ' disabled="disabled"' : '';

        $html .= '<button type="button" class="cfse-loop-add-row button"' . $disabled . '>';
        $html .= esc_html($button_label);
        $html .= '</button>';

        $html .= '</div>'; // .cfse-loop-actions

        $html .= '</div>'; // .cfse-loop-header

        // ループボディ
        $html .= '<div class="cfse-loop-body">';

        // 既存の行をレンダリング
        if (!empty($value)) {
            $html .= '<div class="cfse-loop-rows">';
            $html .= $core->render->render_loop_rows($field, $value);
            $html .= '</div>'; // .cfse-loop-rows
        } else {
            $html .= '<div class="cfse-loop-rows"></div>';
        }

        // 行テンプレート（新規追加用）
        $html .= '<script type="text/html" class="cfse-loop-template">';
        $html .= $core->render->render_loop_row_template($field);
        $html .= '</script>';

        $html .= '</div>'; // .cfse-loop-body

        // 入力フィールド（JSON形式で値を保持）
        $html .= '<input type="hidden" name="' . esc_attr($field_name) . '" class="cfse-loop-value" value="' . esc_attr(json_encode($value)) . '">';

        $html .= '</div>'; // .cfse-loop-field

        return $html;
    }

    /**
     * データベース保存前の値を処理
     *
     * @param mixed $value ユーザー入力値
     * @param array $field フィールド設定
     * @return array 処理された値
     */
    public function prepare_value_for_database($value, $field) {
        // JSONから配列に変換（フォームからの送信）
        if (is_string($value) && !empty($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $value = $decoded;
            }
        }

        // 値が配列でない場合は空の配列に
        if (!is_array($value)) {
            return [];
        }

        // サブフィールドがない場合は空の配列を返す
        if (empty($field['sub_fields'])) {
            return [];
        }

        // サブフィールドの値を処理
        $processed_value = [];

        // CFSEコアインスタンスを取得
        $core = CFSE_Core::get_instance();

        // 各行のサブフィールドを処理
        foreach ($value as $row_index => $row) {
            if (!is_array($row)) {
                continue;
            }

            $processed_row = [];

            foreach ($field['sub_fields'] as $sub_field) {
                $sub_field_id = $sub_field['id'];

                // サブフィールドの値
                $sub_field_value = isset($row[$sub_field_id]) ? $row[$sub_field_id] : null;

                // サブフィールドタイプに応じた処理
                $sub_field_type = $core->get_field_type($sub_field['type']);

                if ($sub_field_type) {
                    $processed_row[$sub_field_id] = $sub_field_type->prepare_value_for_database($sub_field_value, $sub_field);
                } else {
                    $processed_row[$sub_field_id] = $sub_field_value;
                }
            }

            $processed_value[] = $processed_row;
        }

        return $processed_value;
    }

    /**
     * データベースから取得した値を表示用に整形
     *
     * @param mixed $value データベース値
     * @param array $field フィールド設定
     * @return array 表示用に整形された値
     */
    public function format_value_for_display($value, $field) {
        // 値が配列でない場合は空の配列に
        if (!is_array($value)) {
            return [];
        }

        // CFSEコアインスタンスを取得
        $core = CFSE_Core::get_instance();

        // サブフィールドの値を整形
        $formatted_value = [];

        foreach ($value as $row_index => $row) {
            if (!is_array($row)) {
                continue;
            }

            $formatted_row = [];

            // サブフィールドごとに処理
            foreach ($field['sub_fields'] as $sub_field) {
                $sub_field_id = $sub_field['id'];

                // サブフィールドの値
                $sub_field_value = isset($row[$sub_field_id]) ? $row[$sub_field_id] : null;

                // サブフィールドタイプに応じた処理
                $sub_field_type = $core->get_field_type($sub_field['type']);

                if ($sub_field_type) {
                    $formatted_row[$sub_field_id] = $sub_field_type->format_value_for_display($sub_field_value, $sub_field);
                } else {
                    $formatted_row[$sub_field_id] = $sub_field_value;
                }
            }

            $formatted_value[] = $formatted_row;
        }

        return $formatted_value;
    }

    /**
     * フィールドタイプに必要なアセット（CSS、JavaScript）を読み込む
     */
    public function enqueue_assets() {
        // 特別なCSSやJSがあれば読み込む
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

        // サブフィールド設定
        // （実際の実装では、サブフィールドの管理UIが必要）
        $html .= $this->render_setting_row(
            'sub_fields',
            __('Sub Fields', 'cfse'),
            '<p class="description">' . __('Sub fields are defined in code.', 'cfse') . '</p>'
        );

        // 最小数設定
        $html .= $this->render_setting_row(
            'min',
            __('Minimum Rows', 'cfse'),
            $this->render_text_setting('min', $field['min']) .
            '<p class="description">' . __('Minimum number of rows (0 = no minimum)', 'cfse') . '</p>'
        );

        // 最大数設定
        $html .= $this->render_setting_row(
            'max',
            __('Maximum Rows', 'cfse'),
            $this->render_text_setting('max', $field['max']) .
            '<p class="description">' . __('Maximum number of rows (0 = no maximum)', 'cfse') . '</p>'
        );

        // 行ラベルフォーマット設定
        $html .= $this->render_setting_row(
            'row_label_format',
            __('Row Label Format', 'cfse'),
            $this->render_text_setting('row_label_format', $field['row_label_format']) .
            '<p class="description">' . __('Format for row labels (e.g. "Item: {title}") - use {field_id} placeholders', 'cfse') . '</p>'
        );

        // ボタンラベル設定
        $html .= $this->render_setting_row(
            'button_label',
            __('Button Label', 'cfse'),
            $this->render_text_setting('button_label', $field['button_label']) .
            '<p class="description">' . __('Label for the "Add Row" button (default: "Add Row")', 'cfse') . '</p>'
        );

        // 縮小表示設定
        $html .= $this->render_setting_row(
            'collapsed',
            __('Collapsed', 'cfse'),
            $this->render_text_setting('collapsed', $field['collapsed']) .
            '<p class="description">' . __('Sub field ID to use for collapsed row labels', 'cfse') . '</p>'
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
        // 値が配列でない場合はエラー
        if (!is_array($value)) {
            return false;
        }

        // 最小数のチェック
        if (!empty($field['min']) && count($value) < (int) $field['min']) {
            return false;
        }

        // 最大数のチェック
        if (!empty($field['max']) && count($value) > (int) $field['max']) {
            return false;
        }

        // サブフィールドのバリデーション
        foreach ($value as $row) {
            if (!is_array($row)) {
                return false;
            }

            // 必須サブフィールドのチェック
            foreach ($field['sub_fields'] as $sub_field) {
                if (!empty($sub_field['required']) && empty($row[$sub_field['id']])) {
                    return false;
                }
            }
        }

        return true;
    }
    }
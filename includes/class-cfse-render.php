<?php
/**
 * Custom Field Suite Extended - Render Class
 *
 * @package CFSE
 */

// 直接アクセスを防止
if (!defined('ABSPATH')) {
    exit;
}

/**
 * フィールドのレンダリングを担当するクラス
 *
 * 管理画面でのフィールド表示処理を行う
 */
class CFSE_Render {

    /**
     * CFSEコアのインスタンス
     *
     * @var CFSE_Core
     */
    private $core;

    /**
     * コンストラクタ
     *
     * @param CFSE_Core $core CFSEコアのインスタンス
     */
    public function __construct($core) {
        $this->core = $core;
    }

    /**
     * メタボックスをレンダリング
     *
     * @param WP_Post $post 投稿オブジェクト
     * @param array $metabox メタボックス情報
     */
    public function render_meta_box($post, $metabox) {
        $field_group = $metabox['args']['field_group'];

        // フィールドグループにフィールドがない場合は終了
        if (empty($field_group['fields'])) {
            echo '<p>' . esc_html__('No fields defined for this field group.', 'cfse') . '</p>';
            return;
        }

        // nonceフィールド
        wp_nonce_field('cfse_save_post', 'cfse_nonce');

        echo '<div class="cfse-fields-container" data-group-id="' . esc_attr($field_group['id']) . '">';

        // フィールドグループの説明があれば表示
        if (!empty($field_group['description'])) {
            echo '<div class="cfse-group-description">';
            echo wp_kses_post($field_group['description']);
            echo '</div>';
        }

        // 各フィールドをレンダリング
        foreach ($field_group['fields'] as $field) {
            $this->render_field($field, $post->ID, $field_group['table']);
        }

        echo '</div>';
    }

    /**
     * フィールドをレンダリング
     *
     * @param array $field フィールド設定
     * @param int $object_id オブジェクトID
     * @param string $table テーブル名
     */
    public function render_field($field, $object_id, $table = 'postmeta') {
        // 必須パラメータをチェック
        if (!isset($field['id']) || !isset($field['type'])) {
            return;
        }

        // フィールドタイプを取得
        $field_type = $this->core->get_field_type($field['type']);

        if (!$field_type) {
            // 未知のフィールドタイプの場合はエラーメッセージを表示
            echo '<div class="cfse-field-error">';
            echo sprintf(
                esc_html__('Unknown field type: %s', 'cfse'),
                esc_html($field['type'])
            );
            echo '</div>';
            return;
        }

        // 現在の値を取得
        $value = $this->core->data->get_field_value($object_id, $field['id'], $table);

        // フィールドの外側コンテナ
        echo '<div class="cfse-field-wrapper cfse-field-type-' . esc_attr($field['type']) . '" data-field-id="' . esc_attr($field['id']) . '">';

        // 条件付き表示の設定があれば属性を追加
        if (!empty($field['conditional_logic'])) {
            echo ' data-conditional-logic="' . esc_attr(json_encode($field['conditional_logic'])) . '"';
        }

        // フィールドラベル
        if (!empty($field['label'])) {
            echo '<div class="cfse-field-label">';
            echo '<label for="cfse_' . esc_attr($field['id']) . '">' . esc_html($field['label']) . '</label>';

            // 必須フィールド
            if (!empty($field['required'])) {
                echo '<span class="cfse-required">*</span>';
            }

            echo '</div>';
        }

        // フィールド入力部分
        echo '<div class="cfse-field-input">';

        // hidden input for field ID
        echo '<input type="hidden" name="cfse_field_ids[]" value="' . esc_attr($field['id']) . '">';

        // フィールドタイプに応じたHTMLを生成
        echo $field_type->html($field, $value);

        echo '</div>';

        // フィールドの説明
        if (!empty($field['description'])) {
            echo '<div class="cfse-field-description">';
            echo wp_kses_post($field['description']);
            echo '</div>';
        }

        echo '</div>'; // .cfse-field-wrapper
    }

    /**
     * フロントエンド用にフィールド値を整形して表示
     *
     * @param mixed $value フィールド値
     * @param array $field フィールド設定
     * @return string HTML
     */
    public function format_value_for_frontend($value, $field) {
        // フィールドタイプを取得
        $field_type = $this->core->get_field_type($field['type']);

        if (!$field_type) {
            // 未知のフィールドタイプの場合はそのまま返す
            return $value;
        }

        // フィールドタイプに応じた処理を行う
        $formatted_value = $field_type->format_value_for_display($value, $field);

        /**
         * フロントエンド表示用のフィールド値をフィルタリング
         *
         * @param mixed $formatted_value 整形された値
         * @param mixed $value 元の値
         * @param array $field フィールド設定
         */
        return apply_filters('cfse_format_value_for_frontend', $formatted_value, $value, $field);
    }

    /**
     * 利用可能なフィールドタイプの選択肢を生成
     *
     * @return array フィールドタイプの選択肢
     */
    public function get_field_type_choices() {
        $choices = [];

        foreach ($this->core->get_field_types() as $type => $field_type) {
            $choices[$type] = $field_type->label;
        }

        return $choices;
    }

    /**
     * ループフィールドの行ラベルをフォーマット
     *
     * @param string $format フォーマット文字列
     * @param array $row_value 行の値
     * @return string フォーマットされたラベル
     */
    private function format_row_label($format, $row_value) {
        // プレースホルダを実際の値で置換
        $label = $format;

        // {field_id} 形式のプレースホルダを検索
        preg_match_all('/{([^}]+)}/', $label, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $field_id) {
                $value = isset($row_value[$field_id]) ? $row_value[$field_id] : '';

                // 値が配列の場合は文字列に変換
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }

                // 値が長すぎる場合は切り詰める
                if (mb_strlen($value) > 20) {
                    $value = mb_substr($value, 0, 20) . '...';
                }

                $label = str_replace('{' . $field_id . '}', $value, $label);
            }
        }

        return $label;
    }

    /**
     * ループフィールドの行をレンダリング
     *
     * @param array $field ループフィールド設定
     * @param array $values 行の値の配列
     * @return string HTML
     */
    public function render_loop_rows($field, $values) {
        $html = '';

        if (!is_array($values)) {
            return $html;
        }

        foreach ($values as $row_index => $row_value) {
            $html .= '<div class="cfse-loop-row" data-row-index="' . esc_attr($row_index) . '">';

            // 行のヘッダー
            $html .= '<div class="cfse-loop-row-header">';

            // 行のラベル（カスタマイズ可能にする）
            $row_label_format = $field['row_label_format'] ?? '';
            $row_label = !empty($row_label_format) ? $this->format_row_label($row_label_format, $row_value) : __('Row', 'cfse') . ' #' . ($row_index + 1);

            $html .= '<span class="cfse-loop-row-label">' . esc_html($row_label) . '</span>';

            // 行の操作ボタン
            $html .= '<div class="cfse-loop-row-actions">';
            $html .= '<button type="button" class="cfse-loop-row-toggle button" title="' . esc_attr__('Toggle', 'cfse') . '">';
            $html .= '<span class="dashicons dashicons-arrow-down-alt2"></span>';
            $html .= '</button>';

            $html .= '<button type="button" class="cfse-loop-row-remove button" title="' . esc_attr__('Remove', 'cfse') . '">';
            $html .= '<span class="dashicons dashicons-trash"></span>';
            $html .= '</button>';

            $html .= '<button type="button" class="cfse-loop-row-handle button" title="' . esc_attr__('Drag to reorder', 'cfse') . '">';
            $html .= '<span class="dashicons dashicons-menu"></span>';
            $html .= '</button>';

            $html .= '</div>'; // .cfse-loop-row-actions

            $html .= '</div>'; // .cfse-loop-row-header

            // 行のコンテンツ
            $html .= '<div class="cfse-loop-row-content">';

            // サブフィールドがあれば表示
            if (!empty($field['sub_fields']) && is_array($field['sub_fields'])) {
                foreach ($field['sub_fields'] as $sub_field) {
                    // サブフィールドの名前を調整
                    $sub_field_name = $field['id'] . '[' . $row_index . '][' . $sub_field['id'] . ']';
                    $sub_field_id = $field['id'] . '_' . $row_index . '_' . $sub_field['id'];

                    // サブフィールド用の設定をマージ
                    $sub_field_config = array_merge($sub_field, [
                        'id' => $sub_field_id,
                        'name' => $sub_field_name,
                    ]);

                    // サブフィールドの値
                    $sub_field_value = isset($row_value[$sub_field['id']]) ? $row_value[$sub_field['id']] : '';

                    // フィールドタイプを取得
                    $field_type = $this->core->get_field_type($sub_field['type']);

                    if ($field_type) {
                        $html .= '<div class="cfse-loop-sub-field cfse-field-type-' . esc_attr($sub_field['type']) . '">';

                        // サブフィールドラベル
                        if (!empty($sub_field['label'])) {
                            $html .= '<div class="cfse-field-label">';
                            $html .= '<label for="' . esc_attr($sub_field_id) . '">' . esc_html($sub_field['label']) . '</label>';

                            // 必須フィールド
                            if (!empty($sub_field['required'])) {
                                $html .= '<span class="cfse-required">*</span>';
                            }

                            $html .= '</div>';
                        }

                        // サブフィールド入力部分
                        $html .= '<div class="cfse-field-input">';
                        $html .= $field_type->html($sub_field_config, $sub_field_value);
                        $html .= '</div>';

                        // サブフィールドの説明
                        if (!empty($sub_field['description'])) {
                            $html .= '<div class="cfse-field-description">';
                            $html .= wp_kses_post($sub_field['description']);
                            $html .= '</div>';
                        }

                        $html .= '</div>'; // .cfse-loop-sub-field
                    }
                }
            }

            $html .= '</div>'; // .cfse-loop-row-content

            $html .= '</div>'; // .cfse-loop-row
        }

        return $html;
    }

    /**
     * ループフィールドのテンプレート行をレンダリング
     *
     * @param array $field ループフィールド設定
     * @param int $row_index 行インデックス（新規行の場合はテンプレート用の値）
     * @return string HTML
     */
    public function render_loop_row_template($field, $row_index = '{{index}}') {
        $html = '<div class="cfse-loop-row" data-row-index="' . esc_attr($row_index) . '">';

        // 行のヘッダー
        $html .= '<div class="cfse-loop-row-header">';

        // 行のラベル（カスタマイズ可能にする）
        $row_label_format = $field['row_label_format'] ?? '';
        $row_label = !empty($row_label_format) ? $row_label_format : __('Row', 'cfse') . ' #' . (is_numeric($row_index) ? ($row_index + 1) : $row_index);

        $html .= '<span class="cfse-loop-row-label">' . esc_html($row_label) . '</span>';

        // 行の操作ボタン
        $html .= '<div class="cfse-loop-row-actions">';
        $html .= '<button type="button" class="cfse-loop-row-toggle button" title="' . esc_attr__('Toggle', 'cfse') . '">';
        $html .= '<span class="dashicons dashicons-arrow-down-alt2"></span>';
        $html .= '</button>';

        $html .= '<button type="button" class="cfse-loop-row-remove button" title="' . esc_attr__('Remove', 'cfse') . '">';
        $html .= '<span class="dashicons dashicons-trash"></span>';
        $html .= '</button>';

        $html .= '<button type="button" class="cfse-loop-row-handle button" title="' . esc_attr__('Drag to reorder', 'cfse') . '">';
        $html .= '<span class="dashicons dashicons-menu"></span>';
        $html .= '</button>';

        $html .= '</div>'; // .cfse-loop-row-actions

        $html .= '</div>'; // .cfse-loop-row-header

        // 行のコンテンツ
        $html .= '<div class="cfse-loop-row-content">';

        // サブフィールドがあれば表示
        if (!empty($field['sub_fields']) && is_array($field['sub_fields'])) {
            foreach ($field['sub_fields'] as $sub_field) {
                // サブフィールドの名前を調整
                $sub_field_name = $field['id'] . '[' . $row_index . '][' . $sub_field['id'] . ']';
                $sub_field_id = $field['id'] . '_' . $row_index . '_' . $sub_field['id'];

                // サブフィールド用の設定をマージ
                $sub_field_config = array_merge($sub_field, [
                    'id' => $sub_field_id,
                    'name' => $sub_field_name,
                ]);

                // サブフィールドの値（テンプレートの場合は空）
                $sub_field_value = '';

                // フィールドタイプを取得
                $field_type = $this->core->get_field_type($sub_field['type']);

                if ($field_type) {
                    $html .= '<div class="cfse-loop-sub-field cfse-field-type-' . esc_attr($sub_field['type']) . '">';

                    // サブフィールドラベル
                    if (!empty($sub_field['label'])) {
                        $html .= '<div class="cfse-field-label">';
                        $html .= '<label for="' . esc_attr($sub_field_id) . '">' . esc_html($sub_field['label']) . '</label>';

                        // 必須フィールド
                        if (!empty($sub_field['required'])) {
                            $html .= '<span class="cfse-required">*</span>';
                        }

                        $html .= '</div>';
                    }

                    // サブフィールド入力部分
                    $html .= '<div class="cfse-field-input">';
                    $html .= $field_type->html($sub_field_config, $sub_field_value);
                    $html .= '</div>';

                    // サブフィールドの説明
                    if (!empty($sub_field['description'])) {
                        $html .= '<div class="cfse-field-description">';
                        $html .= wp_kses_post($sub_field['description']);
                        $html .= '</div>';
                    }

                    $html .= '</div>'; // .cfse-loop-sub-field
                }
            }
        }

        $html .= '</div>'; // .cfse-loop-row-content

        $html .= '</div>'; // .cfse-loop-row

        return $html;
    }

    /**
     * リレーションシップフィールドのアイテム表示
     *
     * @param int $post_id 投稿ID
     * @param string $post_type 投稿タイプ
     * @return string HTML
     */
    public function render_relationship_item($post_id, $post_type = 'post') {
        $post = get_post($post_id);

        if (!$post) {
            return '';
        }

        $html = '<div class="cfse-relationship-item" data-id="' . esc_attr($post_id) . '" data-type="' . esc_attr($post_type) . '">';

        // 削除ボタン
        $html .= '<button type="button" class="cfse-relationship-remove button" title="' . esc_attr__('Remove', 'cfse') . '">';
        $html .= '<span class="dashicons dashicons-no-alt"></span>';
        $html .= '</button>';

        // 並べ替えハンドル
        $html .= '<span class="cfse-relationship-handle" title="' . esc_attr__('Drag to reorder', 'cfse') . '">';
        $html .= '<span class="dashicons dashicons-menu"></span>';
        $html .= '</span>';

        // 投稿タイトル
        $html .= '<span class="cfse-relationship-title">' . esc_html($post->post_title) . '</span>';

        // 投稿タイプラベル
        $post_type_obj = get_post_type_object($post_type);
        $post_type_label = $post_type_obj ? $post_type_obj->labels->singular_name : $post_type;

        $html .= '<span class="cfse-relationship-type">' . esc_html($post_type_label) . '</span>';

        $html .= '</div>';

        return $html;
    }

    /**
     * ファイルフィールドのプレビューを表示
     *
     * @param int $attachment_id 添付ファイルID
     * @return string HTML
     */
    public function render_file_preview($attachment_id) {
        $html = '';

        if (!$attachment_id) {
            return $html;
        }

        $attachment = get_post($attachment_id);

        if (!$attachment) {
            return $html;
        }

        $file_url = wp_get_attachment_url($attachment_id);
        $file_type = wp_check_filetype(get_post_meta($attachment_id, '_wp_attached_file', true));
        $file_icon = wp_mime_type_icon($attachment_id);

        $html .= '<div class="cfse-file-preview" data-id="' . esc_attr($attachment_id) . '">';

        // 削除ボタン
        $html .= '<button type="button" class="cfse-file-remove button" title="' . esc_attr__('Remove', 'cfse') . '">';
        $html .= '<span class="dashicons dashicons-no-alt"></span>';
        $html .= '</button>';

        // 画像の場合はサムネイルを表示
        if (strpos($attachment->post_mime_type, 'image/') === 0) {
            $thumbnail = wp_get_attachment_image($attachment_id, [80, 80]);
            $html .= '<div class="cfse-file-thumbnail">' . $thumbnail . '</div>';
        } else {
            // その他のファイルタイプの場合はアイコンを表示
            $html .= '<div class="cfse-file-icon">';
            $html .= '<img src="' . esc_attr($file_icon) . '" alt="">';
            $html .= '</div>';
        }

        // ファイル情報
        $html .= '<div class="cfse-file-info">';
        $html .= '<span class="cfse-file-name">' . esc_html(basename($file_url)) . '</span>';

        if (!empty($file_type['ext'])) {
            $html .= '<span class="cfse-file-type">' . esc_html(strtoupper($file_type['ext'])) . '</span>';
        }

        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }

    /**
     * 表示用のHTMLエスケープ処理
     *
     * @param string $content HTMLコンテンツ
     * @param array $allowed_tags 許可するHTMLタグ
     * @return string エスケープされたHTML
     */
    public function escape_html($content, $allowed_tags = []) {
        if (empty($allowed_tags)) {
            // デフォルトの許可タグ
            $allowed_tags = [
                'a' => [
                    'href' => true,
                    'title' => true,
                    'target' => true,
                    'rel' => true,
                    'class' => true,
                ],
                'br' => [],
                'em' => [],
                'strong' => [],
                'p' => [
                    'class' => true,
                ],
                'span' => [
                    'class' => true,
                ],
                'div' => [
                    'class' => true,
                ],
                'ul' => [
                    'class' => true,
                ],
                'ol' => [
                    'class' => true,
                ],
                'li' => [
                    'class' => true,
                ],
            ];
        }

        return wp_kses($content, $allowed_tags);
    }

    /**
     * 条件付き表示用の JavaScript データを生成
     *
     * @param array $field フィールド設定
     * @return string JSON エンコードされたデータ
     */
    public function get_conditional_logic_data($field) {
        if (empty($field['conditional_logic'])) {
            return '{}';
        }

        $conditional_logic = $field['conditional_logic'];

        // 条件ロジックがフォーマットに従っていることを確認
        if (!isset($conditional_logic['groups']) || !is_array($conditional_logic['groups'])) {
            return '{}';
        }

        // 適切なデータ構造を構築
        $data = [
            'action' => isset($conditional_logic['action']) ? $conditional_logic['action'] : 'show',
            'groups' => [],
        ];

        foreach ($conditional_logic['groups'] as $group) {
            $rules = [];

            foreach ($group as $rule) {
                if (isset($rule['field'], $rule['operator'])) {
                    $rules[] = [
                        'field' => $rule['field'],
                        'operator' => $rule['operator'],
                        'value' => isset($rule['value']) ? $rule['value'] : '',
                    ];
                }
            }

            if (!empty($rules)) {
                $data['groups'][] = $rules;
            }
        }

        return json_encode($data);
    }
    }
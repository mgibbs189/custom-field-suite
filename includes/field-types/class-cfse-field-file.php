<?php
/**
 * Custom Field Suite Extended - File Field Type
 *
 * @package CFSE
 */

// 直接アクセスを防止
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ファイルフィールドタイプクラス
 */
class CFSE_Field_File extends CFSE_Field_Type {

    /**
     * フィールドタイプの識別名
     *
     * @var string
     */
    public $name = 'file';

    /**
     * フィールドタイプの表示名
     *
     * @var string
     */
    public $label = 'File';

    /**
     * デフォルト設定
     *
     * @var array
     */
    protected $defaults = [
        'return_format' => 'id',
        'library' => 'all',
        'min_size' => '',
        'max_size' => '',
        'mime_types' => '',
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

        // フィールド名の設定
        $field_name = isset($field['name']) ? $field['name'] : 'cfse_fields[' . $field['id'] . ']';

        // HTML生成開始
        $html = '<div class="cfse-file-field ' . esc_attr($field['class']) . '">';

        // 隠しフィールド（ファイルID用）
        $html .= '<input type="hidden" name="' . esc_attr($field_name) . '" id="' . esc_attr($field['id']) . '" value="' . esc_attr($value) . '">';

        // ボタン
        $html .= '<div class="cfse-file-buttons">';

        // アップロードボタン
        $upload_button_style = !empty($value) ? ' style="display:none;"' : '';
        $html .= '<button type="button" class="cfse-file-upload-button button"' . $upload_button_style . '>';
        $html .= esc_html__('Select File', 'cfse');
        $html .= '</button>';

        // 削除ボタン
        $remove_button_style = empty($value) ? ' style="display:none;"' : '';
        $html .= '<button type="button" class="cfse-file-remove-button button"' . $remove_button_style . '>';
        $html .= esc_html__('Remove File', 'cfse');
        $html .= '</button>';

        $html .= '</div>'; // .cfse-file-buttons

        // プレビュー
        $html .= '<div class="cfse-file-preview">';

        if (!empty($value)) {
            $html .= $this->get_file_preview($value);
        }

        $html .= '</div>'; // .cfse-file-preview

        $html .= '</div>'; // .cfse-file-field

        return $html;
    }

    /**
     * ファイルプレビューのHTMLを取得
     *
     * @param int $attachment_id 添付ファイルID
     * @return string HTML
     */
    private function get_file_preview($attachment_id) {
        // 添付ファイル情報を取得
        $attachment = get_post($attachment_id);

        if (!$attachment) {
            return '';
        }

        // ファイルURL
        $file_url = wp_get_attachment_url($attachment_id);

        // ファイル情報
        $file_type = wp_check_filetype(get_post_meta($attachment_id, '_wp_attached_file', true));
        $file_icon = wp_mime_type_icon($attachment_id);

        $html = '<div class="cfse-file-preview-content" data-id="' . esc_attr($attachment_id) . '">';

        // 画像の場合はサムネイルを表示
        if (strpos($attachment->post_mime_type, 'image/') === 0) {
            $thumbnail = wp_get_attachment_image($attachment_id, [80, 80]);
            $html .= '<div class="cfse-file-thumbnail">' . $thumbnail . '</div>';
        } else {
            // その他のファイルタイプの場合はアイコンを表示
            $html .= '<div class="cfse-file-icon">';
            $html .= '<img src="' . esc_url($file_icon) . '" alt="">';
            $html .= '</div>';
        }

        // ファイル情報
        $html .= '<div class="cfse-file-info">';
        $html .= '<span class="cfse-file-name">' . esc_html(basename($file_url)) . '</span>';

        if (!empty($file_type['ext'])) {
            $html .= '<span class="cfse-file-type">' . esc_html(strtoupper($file_type['ext'])) . '</span>';
        }

        $html .= '</div>'; // .cfse-file-info

        $html .= '</div>'; // .cfse-file-preview-content

        return $html;
    }

    /**
     * データベース保存前の値を処理
     *
     * @param mixed $value ユーザー入力値
     * @param array $field フィールド設定
     * @return mixed 処理された値
     */
    public function prepare_value_for_database($value, $field) {
        // ファイルIDをそのまま返す
        if (empty($value)) {
            return '';
        }

        return (int) $value;
    }

    /**
     * データベースから取得した値を表示用に整形
     *
     * @param mixed $value データベース値
     * @param array $field フィールド設定
     * @return mixed 表示用に整形された値
     */
    public function format_value_for_display($value, $field) {
        if (empty($value)) {
            return null;
        }

        $attachment_id = (int) $value;

        // 戻り値のフォーマットに応じて処理
        if (!empty($field['return_format'])) {
            switch ($field['return_format']) {
                case 'id':
                    return $attachment_id;

                case 'url':
                    return wp_get_attachment_url($attachment_id);

                case 'array':
                    $attachment = get_post($attachment_id);

                    if (!$attachment) {
                        return null;
                    }

                    return [
                        'id' => $attachment_id,
                        'title' => $attachment->post_title,
                        'filename' => basename(get_attached_file($attachment_id)),
                        'url' => wp_get_attachment_url($attachment_id),
                        'alt' => get_post_meta($attachment_id, '_wp_attachment_image_alt', true),
                        'author' => $attachment->post_author,
                        'description' => $attachment->post_content,
                        'caption' => $attachment->post_excerpt,
                        'mime_type' => $attachment->post_mime_type,
                        'type' => wp_attachment_is_image($attachment_id) ? 'image' : 'file',
                        'icon' => wp_mime_type_icon($attachment_id),
                        'sizes' => wp_attachment_is_image($attachment_id) ? $this->get_image_sizes($attachment_id) : null,
                    ];
            }
        }

        // デフォルトはID
        return $attachment_id;
    }

    /**
     * 画像サイズ情報を取得
     *
     * @param int $attachment_id 添付ファイルID
     * @return array サイズ情報
     */
    private function get_image_sizes($attachment_id) {
        $sizes = [];

        // 登録済みの画像サイズを取得
        $image_sizes = get_intermediate_image_sizes();

        // フルサイズ
        $img_url = wp_get_attachment_url($attachment_id);
        $img_meta = wp_get_attachment_metadata($attachment_id);

        $sizes['full'] = [
            'url' => $img_url,
            'width' => $img_meta['width'] ?? 0,
            'height' => $img_meta['height'] ?? 0,
        ];

        // その他のサイズ
        foreach ($image_sizes as $size) {
            $img = wp_get_attachment_image_src($attachment_id, $size);

            if ($img) {
                $sizes[$size] = [
                    'url' => $img[0],
                    'width' => $img[1],
                    'height' => $img[2],
                ];
            }
        }

        return $sizes;
    }

    /**
     * フィールドタイプに必要なアセット（CSS、JavaScript）を読み込む
     */
    public function enqueue_assets() {
        // WordPressのメディアライブラリJSを読み込み
        if (function_exists('wp_enqueue_media')) {
            wp_enqueue_media();
        }
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

        // 戻り値フォーマット設定
        $html .= $this->render_setting_row(
            'return_format',
            __('Return Format', 'cfse'),
            $this->render_select_setting('return_format', $field['return_format'], [
                'id' => __('Attachment ID', 'cfse'),
                'url' => __('File URL', 'cfse'),
                'array' => __('File Array', 'cfse')
            ])
        );

        // ライブラリ設定
        $html .= $this->render_setting_row(
            'library',
            __('Library', 'cfse'),
            $this->render_select_setting('library', $field['library'], [
                'all' => __('All files', 'cfse'),
                'uploadedTo' => __('Files uploaded to this post', 'cfse')
            ])
        );

        // 最小サイズ設定
        $html .= $this->render_setting_row(
            'min_size',
            __('Minimum File Size', 'cfse'),
            $this->render_text_setting('min_size', $field['min_size']) .
            '<p class="description">' . __('Minimum file size in MB (0 = no limit)', 'cfse') . '</p>'
        );

        // 最大サイズ設定
        $html .= $this->render_setting_row(
            'max_size',
            __('Maximum File Size', 'cfse'),
            $this->render_text_setting('max_size', $field['max_size']) .
            '<p class="description">' . __('Maximum file size in MB (0 = no limit)', 'cfse') . '</p>'
        );

        // MIMEタイプ設定
        $html .= $this->render_setting_row(
            'mime_types',
            __('Allowed File Types', 'cfse'),
            $this->render_text_setting('mime_types', $field['mime_types']) .
            '<p class="description">' . __('Comma-separated list of allowed file types (e.g. jpg, gif, pdf)', 'cfse') . '</p>'
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

        if (empty($value)) {
            return true;
        }

        // 添付ファイルの存在確認
        $attachment = get_post($value);

        if (!$attachment || $attachment->post_type !== 'attachment') {
            return false;
        }

        // MIMEタイプのバリデーション
        if (!empty($field['mime_types'])) {
            $allowed_types = array_map('trim', explode(',', $field['mime_types']));
            $file_type = wp_check_filetype(get_post_meta($value, '_wp_attached_file', true));

            if (!in_array($file_type['ext'], $allowed_types)) {
                return false;
            }
        }

        // ファイルサイズのバリデーション
        $file_path = get_attached_file($value);

        if (file_exists($file_path)) {
            $file_size = filesize($file_path) / (1024 * 1024); // MB単位

            // 最小サイズ
            if (!empty($field['min_size']) && $file_size < (float) $field['min_size']) {
                return false;
            }

            // 最大サイズ
            if (!empty($field['max_size']) && $file_size > (float) $field['max_size']) {
                return false;
            }
        }

        return true;
    }
    }
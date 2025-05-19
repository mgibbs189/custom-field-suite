<?php
/**
 * Custom Field Suite Extended - Data Class
 *
 * @package CFSE
 */

// 直接アクセスを防止
if (!defined('ABSPATH')) {
    exit;
}

/**
 * データ操作を担当するクラス
 *
 * フィールド値の保存・取得処理を行う
 */
class CFSE_Data {

    /**
     * CFSEコアのインスタンス
     *
     * @var CFSE_Core
     */
    private $core;

    /**
     * 内部キャッシュ
     *
     * @var array
     */
    private $cache = [];

    /**
     * コンストラクタ
     *
     * @param CFSE_Core $core CFSEコアのインスタンス
     */
    public function __construct($core) {
        $this->core = $core;
    }

    /**
     * フィールド値を保存
     *
     * @param int $object_id オブジェクトID（投稿ID、ユーザーIDなど）
     * @param string $field_id フィールドID
     * @param mixed $value 値
     * @param string $table テーブル名
     * @return bool 成功したかどうか
     */
    public function save_field_value($object_id, $field_id, $value, $table = 'postmeta') {
        // 値を準備（配列や複雑なデータ構造はシリアライズ）
        $value = $this->prepare_value_for_database($value);

        // テーブルに応じて保存方法を変える
        switch ($table) {
            case 'postmeta':
                // 値が配列または nullの場合は削除して再登録（配列でも一度削除して同じkeyに入れる）
                if (is_array($value) || is_null($value)) {
                    delete_post_meta($object_id, $field_id);

                    if (!is_null($value)) {
                        return add_post_meta($object_id, $field_id, $value);
                    }

                    return true;
                }

                return update_post_meta($object_id, $field_id, $value);

            case 'usermeta':
                // 値が配列または nullの場合は削除して再登録
                if (is_array($value) || is_null($value)) {
                    delete_user_meta($object_id, $field_id);

                    if (!is_null($value)) {
                        return add_user_meta($object_id, $field_id, $value);
                    }

                    return true;
                }

                return update_user_meta($object_id, $field_id, $value);

            case 'termmeta':
                // 値が配列または nullの場合は削除して再登録
                if (is_array($value) || is_null($value)) {
                    delete_term_meta($object_id, $field_id);

                    if (!is_null($value)) {
                        return add_term_meta($object_id, $field_id, $value);
                    }

                    return true;
                }

                return update_term_meta($object_id, $field_id, $value);

            default:
                // カスタムテーブルの場合はフィルターを適用
                $result = apply_filters('cfse_save_custom_table_value', false, $object_id, $field_id, $value, $table);

                if (!$result) {
                    // カスタムテーブルのデフォルト実装
                    return $this->save_to_custom_table($object_id, $field_id, $value, $table);
                }

                return $result;
        }
    }

    /**
     * フィールド値を取得
     *
     * @param int $object_id オブジェクトID（投稿ID、ユーザーIDなど）
     * @param string $field_id フィールドID
     * @param string $table テーブル名
     * @return mixed フィールド値
     */
    public function get_field_value($object_id, $field_id, $table = 'postmeta') {
        // キャッシュキー
        $cache_key = $table . '|' . $object_id . '|' . $field_id;

        // キャッシュにあればそれを返す
        if (isset($this->cache[$cache_key])) {
            return $this->cache[$cache_key];
        }

        // テーブルに応じて取得方法を変える
        $value = null;

        switch ($table) {
            case 'postmeta':
                $value = get_post_meta($object_id, $field_id, true);
                break;

            case 'usermeta':
                $value = get_user_meta($object_id, $field_id, true);
                break;

            case 'termmeta':
                $value = get_term_meta($object_id, $field_id, true);
                break;

            default:
                // カスタムテーブルの場合はフィルターを適用
                $value = apply_filters('cfse_get_custom_table_value', null, $object_id, $field_id, $table);

                if (is_null($value)) {
                    // カスタムテーブルのデフォルト実装
                    $value = $this->get_from_custom_table($object_id, $field_id, $table);
                }
                break;
        }

        // 値を適切な形式に変換
        $value = $this->prepare_value_from_database($value);

        // キャッシュに保存
        $this->cache[$cache_key] = $value;

        return $value;
    }

    /**
     * データベース保存前に値を準備
     *
     * @param mixed $value 値
     * @return mixed 準備された値
     */
    private function prepare_value_for_database($value) {
        // 空の配列は null として保存
        if (is_array($value) && empty($value)) {
            return null;
        }

        return $value;
    }

    /**
     * データベースから取得した値を適切な形式に変換
     *
     * @param mixed $value データベースの値
     * @return mixed 変換された値
     */
    private function prepare_value_from_database($value) {
        // falseまたは空文字列はnullとして扱う
        if ($value === false || $value === '') {
            return null;
        }

        // 既にシリアライズされている場合は自動的にアンシリアライズしない
        // WordPressのメタ関数は自動的にアンシリアライズするため、ここでは処理しない

        return $value;
    }

    /**
     * カスタムテーブルに値を保存
     *
     * @param int $object_id オブジェクトID
     * @param string $field_id フィールドID
     * @param mixed $value 値
     * @param string $table テーブル名
     * @return bool 成功したかどうか
     */
    private function save_to_custom_table($object_id, $field_id, $value, $table) {
        global $wpdb;

        // テーブル名のプレフィックスを追加
        $table_name = $wpdb->prefix . $table;

        // テーブルが存在するか確認
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'");

        if (!$table_exists) {
            // テーブルが存在しない場合は作成
            $this->create_custom_table($table);
        }

        // 値が NULL の場合は削除
        if (is_null($value)) {
            return $wpdb->delete(
                $table_name,
                [
                    'object_id' => $object_id,
                    'field_id' => $field_id,
                ],
                [
                    '%d',
                    '%s',
                ]
            );
        }

        // 既存のレコードがあるか確認
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_name} WHERE object_id = %d AND field_id = %s",
            $object_id,
            $field_id
        ));

        // 配列の場合はシリアライズ
        if (is_array($value)) {
            $value = maybe_serialize($value);
        }

        if ($exists) {
            // 更新
            return $wpdb->update(
                $table_name,
                [
                    'value' => $value,
                    'updated_at' => current_time('mysql'),
                ],
                [
                    'object_id' => $object_id,
                    'field_id' => $field_id,
                ],
                [
                    '%s',
                    '%s',
                ],
                [
                    '%d',
                    '%s',
                ]
            );
        } else {
            // 挿入
            return $wpdb->insert(
                $table_name,
                [
                    'object_id' => $object_id,
                    'field_id' => $field_id,
                    'value' => $value,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql'),
                ],
                [
                    '%d',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                ]
            );
        }
    }

    /**
     * カスタムテーブルから値を取得
     *
     * @param int $object_id オブジェクトID
     * @param string $field_id フィールドID
     * @param string $table テーブル名
     * @return mixed 値
     */
    private function get_from_custom_table($object_id, $field_id, $table) {
        global $wpdb;

        // テーブル名のプレフィックスを追加
        $table_name = $wpdb->prefix . $table;

        // テーブルが存在するか確認
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'");

        if (!$table_exists) {
            return null;
        }

        // 値を取得
        $value = $wpdb->get_var($wpdb->prepare(
            "SELECT value FROM {$table_name} WHERE object_id = %d AND field_id = %s",
            $object_id,
            $field_id
        ));

        // シリアライズされた値をアンシリアライズ
        if ($value && is_serialized($value)) {
            return maybe_unserialize($value);
        }

        return $value;
    }

    /**
     * カスタムテーブルを作成
     *
     * @param string $table テーブル名
     * @return bool 成功したかどうか
     */
    private function create_custom_table($table) {
        global $wpdb;

        // テーブル名のプレフィックスを追加
        $table_name = $wpdb->prefix . $table;

        // テーブルが既に存在するか確認
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'");

        if ($table_exists) {
            return true;
        }

        // テーブル作成用のSQL
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            object_id bigint(20) unsigned NOT NULL,
            field_id varchar(255) NOT NULL,
            value longtext,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY object_id (object_id),
            KEY field_id (field_id),
            KEY object_field (object_id, field_id)
        ) {$charset_collate};";

        // dbDelta関数を使用してテーブルを作成
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // テーブルが正常に作成されたか確認
        return (bool) $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'");
    }

    /**
     * キャッシュをクリア
     *
     * @param string|null $table 特定のテーブルのキャッシュのみクリアする場合
     * @param int|null $object_id 特定のオブジェクトのキャッシュのみクリアする場合
     */
    public function clear_cache($table = null, $object_id = null) {
        if (is_null($table) && is_null($object_id)) {
            // 全てのキャッシュをクリア
            $this->cache = [];
            return;
        }

        foreach ($this->cache as $key => $value) {
            $parts = explode('|', $key);

            if ((!is_null($table) && $parts[0] === $table) ||
                (!is_null($object_id) && $parts[1] == $object_id)) {
                unset($this->cache[$key]);
            }
        }
    }

    /**
     * 指定したフィールドのキャッシュをクリア
     *
     * @param int $object_id オブジェクトID
     * @param string $field_id フィールドID
     * @param string $table テーブル名
     */
    public function clear_field_cache($object_id, $field_id, $table = 'postmeta') {
        $cache_key = $table . '|' . $object_id . '|' . $field_id;

        if (isset($this->cache[$cache_key])) {
            unset($this->cache[$cache_key]);
        }
    }

    /**
     * オブジェクトの全フィールド値を取得
     *
     * @param int $object_id オブジェクトID
     * @param string $table テーブル名
     * @return array フィールド値の配列
     */
    public function get_all_field_values($object_id, $table = 'postmeta') {
        $values = [];

        switch ($table) {
            case 'postmeta':
                $meta = get_post_meta($object_id);

                foreach ($meta as $key => $value_array) {
                    // カスタムフィールドのプレフィックスをチェック
                    if (strpos($key, '_') !== 0) { // 非表示フィールドを除外
                        $values[$key] = $this->prepare_value_from_database($value_array[0] ?? null);
                    }
                }
                break;

            case 'usermeta':
                $meta = get_user_meta($object_id);

                foreach ($meta as $key => $value_array) {
                    // WordPressの内部メタを除外
                    if (strpos($key, 'wp_') !== 0) {
                        $values[$key] = $this->prepare_value_from_database($value_array[0] ?? null);
                    }
                }
                break;

            case 'termmeta':
                $meta = get_term_meta($object_id);

                foreach ($meta as $key => $value_array) {
                    $values[$key] = $this->prepare_value_from_database($value_array[0] ?? null);
                }
                break;

            default:
                // カスタムテーブルの場合
                $values = apply_filters('cfse_get_all_custom_table_values', [], $object_id, $table);

                if (empty($values)) {
                    $values = $this->get_all_from_custom_table($object_id, $table);
                }
                break;
        }

        return $values;
    }

    /**
     * カスタムテーブルから全フィールド値を取得
     *
     * @param int $object_id オブジェクトID
     * @param string $table テーブル名
     * @return array フィールド値の配列
     */
    private function get_all_from_custom_table($object_id, $table) {
        global $wpdb;

        // テーブル名のプレフィックスを追加
        $table_name = $wpdb->prefix . $table;

        // テーブルが存在するか確認
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'");

        if (!$table_exists) {
            return [];
        }

        // 値を取得
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT field_id, value FROM {$table_name} WHERE object_id = %d",
            $object_id
        ));

        $values = [];

        foreach ($results as $row) {
            $value = $row->value;

            // シリアライズされた値をアンシリアライズ
            if ($value && is_serialized($value)) {
                $value = maybe_unserialize($value);
            }

            $values[$row->field_id] = $value;
        }

        return $values;
    }
    }
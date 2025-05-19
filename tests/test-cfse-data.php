<?php
/**
 * CFSE_Data クラスのテスト
 *
 * @package CFSE
 */

use PHPUnit\Framework\TestCase;

class CFSE_Data_Test extends TestCase {

    /**
     * 各テスト前の準備処理
     */
    public function setUp(): void {
        parent::setUp();
        WP_Mock::setUp();
    }

    /**
     * 各テスト後のクリーンアップ
     */
    public function tearDown(): void {
        WP_Mock::tearDown();
        parent::tearDown();
    }

    /**
     * save_field_value()メソッドがpostmetaテーブルに正しく保存することをテスト
     */
    public function test_save_field_value_postmeta() {
        // モックのコアインスタンス
        $core_mock = $this->createMock(CFSE_Core::class);

        // データクラスのインスタンスを作成
        $data = new CFSE_Data($core_mock);

        // 通常の値を保存する場合
        WP_Mock::userFunction('update_post_meta', [
            'times' => 1,
            'args' => [123, 'test_field', 'test value'],
            'return' => true
        ]);

        $result = $data->save_field_value(123, 'test_field', 'test value', 'postmeta');
        $this->assertTrue($result);

        // 配列の値を保存する場合
        WP_Mock::userFunction('delete_post_meta', [
            'times' => 1,
            'args' => [123, 'array_field'],
            'return' => true
        ]);

        WP_Mock::userFunction('add_post_meta', [
            'times' => 1,
            'args' => [123, 'array_field', ['item1', 'item2']],
            'return' => true
        ]);

        $result = $data->save_field_value(123, 'array_field', ['item1', 'item2'], 'postmeta');
        $this->assertTrue($result);

        // nullの値を保存する場合
        WP_Mock::userFunction('delete_post_meta', [
            'times' => 1,
            'args' => [123, 'null_field'],
            'return' => true
        ]);

        $result = $data->save_field_value(123, 'null_field', null, 'postmeta');
        $this->assertTrue($result);
    }

    /**
     * save_field_value()メソッドがusermetaテーブルに正しく保存することをテスト
     */
    public function test_save_field_value_usermeta() {
        // モックのコアインスタンス
        $core_mock = $this->createMock(CFSE_Core::class);

        // データクラスのインスタンスを作成
        $data = new CFSE_Data($core_mock);

        // 通常の値を保存する場合
        WP_Mock::userFunction('update_user_meta', [
            'times' => 1,
            'args' => [123, 'test_field', 'test value'],
            'return' => true
        ]);

        $result = $data->save_field_value(123, 'test_field', 'test value', 'usermeta');
        $this->assertTrue($result);

        // 配列の値を保存する場合
        WP_Mock::userFunction('delete_user_meta', [
            'times' => 1,
            'args' => [123, 'array_field'],
            'return' => true
        ]);

        WP_Mock::userFunction('add_user_meta', [
            'times' => 1,
            'args' => [123, 'array_field', ['item1', 'item2']],
            'return' => true
        ]);

        $result = $data->save_field_value(123, 'array_field', ['item1', 'item2'], 'usermeta');
        $this->assertTrue($result);
    }

    /**
     * save_field_value()メソッドがtermmeta テーブルに正しく保存することをテスト
     */
    public function test_save_field_value_termmeta() {
        // モックのコアインスタンス
        $core_mock = $this->createMock(CFSE_Core::class);

        // データクラスのインスタンスを作成
        $data = new CFSE_Data($core_mock);

        // 通常の値を保存する場合
        WP_Mock::userFunction('update_term_meta', [
            'times' => 1,
            'args' => [123, 'test_field', 'test value'],
            'return' => true
        ]);

        $result = $data->save_field_value(123, 'test_field', 'test value', 'termmeta');
        $this->assertTrue($result);
    }

    /**
     * save_field_value()メソッドがカスタムテーブルに正しく保存することをテスト
     */
    public function test_save_field_value_custom_table() {
        // モックのコアインスタンス
        $core_mock = $this->createMock(CFSE_Core::class);

        // データクラスのインスタンスを作成
        $data = new CFSE_Data($core_mock);

        // カスタムテーブルを使用する場合はフィルターを適用
        WP_Mock::onFilter('cfse_save_custom_table_value')
            ->with(false, 123, 'test_field', 'test value', 'custom_table')
            ->reply(true);

        $result = $data->save_field_value(123, 'test_field', 'test value', 'custom_table');
        $this->assertTrue($result);
    }

    /**
     * get_field_value()メソッドがpostmetaテーブルから正しく値を取得することをテスト
     */
    public function test_get_field_value_postmeta() {
        // モックのコアインスタンス
        $core_mock = $this->createMock(CFSE_Core::class);

        // データクラスのインスタンスを作成
        $data = new CFSE_Data($core_mock);

        // 値を取得する場合
        WP_Mock::userFunction('get_post_meta', [
            'times' => 1,
            'args' => [123, 'test_field', true],
            'return' => 'test value'
        ]);

        $value = $data->get_field_value(123, 'test_field', 'postmeta');
        $this->assertEquals('test value', $value);

        // 空の値を取得する場合（falseが返される）
        WP_Mock::userFunction('get_post_meta', [
            'times' => 1,
            'args' => [123, 'empty_field', true],
            'return' => false
        ]);

        $value = $data->get_field_value(123, 'empty_field', 'postmeta');
        $this->assertNull($value);
    }

    /**
     * get_field_value()メソッドがusermetaテーブルから正しく値を取得することをテスト
     */
    public function test_get_field_value_usermeta() {
        // モックのコアインスタンス
        $core_mock = $this->createMock(CFSE_Core::class);

        // データクラスのインスタンスを作成
        $data = new CFSE_Data($core_mock);

        // 値を取得する場合
        WP_Mock::userFunction('get_user_meta', [
            'times' => 1,
            'args' => [123, 'test_field', true],
            'return' => 'test value'
        ]);

        $value = $data->get_field_value(123, 'test_field', 'usermeta');
        $this->assertEquals('test value', $value);
    }

    /**
     * get_field_value()メソッドがtermmetaテーブルから正しく値を取得することをテスト
     */
    public function test_get_field_value_termmeta() {
        // モックのコアインスタンス
        $core_mock = $this->createMock(CFSE_Core::class);

        // データクラスのインスタンスを作成
        $data = new CFSE_Data($core_mock);

        // 値を取得する場合
        WP_Mock::userFunction('get_term_meta', [
            'times' => 1,
            'args' => [123, 'test_field', true],
            'return' => 'test value'
        ]);

        $value = $data->get_field_value(123, 'test_field', 'termmeta');
        $this->assertEquals('test value', $value);
    }

    /**
     * get_field_value()メソッドがカスタムテーブルから正しく値を取得することをテスト
     */
    public function test_get_field_value_custom_table() {
        // モックのコアインスタンス
        $core_mock = $this->createMock(CFSE_Core::class);

        // データクラスのインスタンスを作成
        $data = new CFSE_Data($core_mock);

        // カスタムテーブルを使用する場合はフィルターを適用
        WP_Mock::onFilter('cfse_get_custom_table_value')
            ->with(null, 123, 'test_field', 'custom_table')
            ->reply('custom value');

        $value = $data->get_field_value(123, 'test_field', 'custom_table');
        $this->assertEquals('custom value', $value);
    }

    /**
     * キャッシュ機能が正しく動作することをテスト
     */
    public function test_caching() {
        // モックのコアインスタンス
        $core_mock = $this->createMock(CFSE_Core::class);

        // データクラスのインスタンスを作成
        $data = new CFSE_Data($core_mock);

        // 最初の呼び出し時はget_post_metaが呼ばれる
        WP_Mock::userFunction('get_post_meta', [
            'times' => 1,
            'args' => [123, 'cached_field', true],
            'return' => 'cached value'
        ]);

        // 1回目の呼び出し
        $value1 = $data->get_field_value(123, 'cached_field', 'postmeta');
        $this->assertEquals('cached value', $value1);

        // 2回目の呼び出し（キャッシュから取得されるため、get_post_metaは呼ばれない）
        $value2 = $data->get_field_value(123, 'cached_field', 'postmeta');
        $this->assertEquals('cached value', $value2);

        // キャッシュをクリア
        $data->clear_cache();

        // キャッシュをクリアした後の呼び出し
        WP_Mock::userFunction('get_post_meta', [
            'times' => 1,
            'args' => [123, 'cached_field', true],
            'return' => 'updated value'
        ]);

        $value3 = $data->get_field_value(123, 'cached_field', 'postmeta');
        $this->assertEquals('updated value', $value3);
    }

    /**
     * get_all_field_values()メソッドが正しく動作することをテスト
     */
    public function test_get_all_field_values() {
        // モックのコアインスタンス
        $core_mock = $this->createMock(CFSE_Core::class);

        // データクラスのインスタンスを作成
        $data = new CFSE_Data($core_mock);

        // postmetaの全値を取得
        WP_Mock::userFunction('get_post_meta', [
            'times' => 1,
            'args' => [123],
            'return' => [
                'field1' => ['value1'],
                'field2' => ['value2'],
                '_hidden_field' => ['hidden_value']
            ]
        ]);

        $values = $data->get_all_field_values(123, 'postmeta');

        // 正しい値が返されることを確認
        $this->assertArrayHasKey('field1', $values);
        $this->assertArrayHasKey('field2', $values);
        $this->assertArrayNotHasKey('_hidden_field', $values); // 非表示フィールドは除外される
        $this->assertEquals('value1', $values['field1']);
        $this->assertEquals('value2', $values['field2']);
    }
    }
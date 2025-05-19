<?php
/**
 * CFSE_Core クラスのテスト
 *
 * @package CFSE
 */

use PHPUnit\Framework\TestCase;

class CFSE_Core_Test extends TestCase {

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
     * get_instance()メソッドがシングルトンインスタンスを返すことをテスト
     */
    public function test_get_instance_returns_singleton() {
        // 必要なモックを設定
        WP_Mock::userFunction('plugin_dir_path', [
            'return' => '/path/to/plugin/'
        ]);

        WP_Mock::userFunction('plugin_dir_url', [
            'return' => 'http://example.com/wp-content/plugins/plugin/'
        ]);

        // アクションやフィルターが呼び出されることを期待
        WP_Mock::expectActionAdded('init', [CFSE_Core::get_instance(), 'init'], 10);
        WP_Mock::expectActionAdded('admin_enqueue_scripts', [CFSE_Core::get_instance(), 'admin_scripts'], 10);
        WP_Mock::expectActionAdded('add_meta_boxes', [CFSE_Core::get_instance(), 'add_meta_boxes'], 10);
        WP_Mock::expectActionAdded('save_post', [CFSE_Core::get_instance(), 'save_post'], 10, 2);
        WP_Mock::expectActionAdded('wp_ajax_cfse_fetch_relationship_options', [CFSE_Core::get_instance(), 'ajax_fetch_relationship_options']);

        // 最初のインスタンスを取得
        $instance1 = CFSE_Core::get_instance();

        // 2回目のインスタンスを取得（同じインスタンスが返されるはず）
        $instance2 = CFSE_Core::get_instance();

        // 同じインスタンスであることを確認
        $this->assertSame($instance1, $instance2);

        // CFSEクラスのインスタンスであることを確認
        $this->assertInstanceOf(CFSE_Core::class, $instance1);
    }

    /**
     * register_field_definition()メソッドが正しく動作することをテスト
     */
    public function test_register_field_definition() {
        // 必要なモックを設定
        WP_Mock::userFunction('plugin_dir_path', [
            'return' => '/path/to/plugin/'
        ]);

        WP_Mock::userFunction('plugin_dir_url', [
            'return' => 'http://example.com/wp-content/plugins/plugin/'
        ]);

        WP_Mock::userFunction('wp_parse_args', [
            'args' => [
                ['id' => 'test_field', 'title' => 'Test Field', 'fields' => [['id' => 'sub_field', 'type' => 'text']]],
                ['fields' => [], 'placement' => [], 'table' => 'postmeta']
            ],
            'return' => [
                'id' => 'test_field',
                'title' => 'Test Field',
                'fields' => [['id' => 'sub_field', 'type' => 'text']],
                'placement' => [],
                'table' => 'postmeta'
            ]
        ]);

        // インスタンスを取得
        $core = CFSE_Core::get_instance();

        // リフレクションを使って非公開プロパティにアクセス
        $reflection = new ReflectionClass($core);
        $property = $reflection->getProperty('field_definitions');
        $property->setAccessible(true);

        // 初期状態で空の配列であることを確認
        $this->assertEquals([], $property->getValue($core));

        // フィールド定義を登録
        $result = $core->register_field_definition([
            'id' => 'test_field',
            'title' => 'Test Field',
            'fields' => [
                [
                    'id' => 'sub_field',
                    'type' => 'text'
                ]
            ]
        ]);

        // 登録成功を確認
        $this->assertTrue($result);

        // フィールド定義が格納されていることを確認
        $field_definitions = $property->getValue($core);
        $this->assertArrayHasKey('test_field', $field_definitions);
        $this->assertEquals('Test Field', $field_definitions['test_field']['title']);
    }

    /**
     * 重複するフィールド定義の登録を拒否することをテスト
     */
    public function test_register_duplicate_field_definition() {
        // 必要なモックを設定
        WP_Mock::userFunction('plugin_dir_path', [
            'return' => '/path/to/plugin/'
        ]);

        WP_Mock::userFunction('plugin_dir_url', [
            'return' => 'http://example.com/wp-content/plugins/plugin/'
        ]);

        WP_Mock::userFunction('wp_parse_args', [
            'args' => [
                ['id' => 'test_field', 'title' => 'Test Field'],
                ['fields' => [], 'placement' => [], 'table' => 'postmeta']
            ],
            'return' => [
                'id' => 'test_field',
                'title' => 'Test Field',
                'fields' => [],
                'placement' => [],
                'table' => 'postmeta'
            ]
        ]);

        // インスタンスを取得
        $core = CFSE_Core::get_instance();

        // リフレクションを使って非公開プロパティにアクセス
        $reflection = new ReflectionClass($core);
        $property = $reflection->getProperty('field_definitions');
        $property->setAccessible(true);

        // フィールド定義を手動でセット
        $property->setValue($core, [
            'test_field' => [
                'id' => 'test_field',
                'title' => 'Test Field',
                'fields' => [],
                'placement' => [],
                'table' => 'postmeta'
            ]
        ]);

        // 重複するフィールド定義を登録（エラーが発生するはず）
        WP_Mock::userFunction('trigger_error', [
            'times' => 1,
            'args' => [WP_Mock\Functions::type('string'), E_USER_WARNING]
        ]);

        $result = $core->register_field_definition([
            'id' => 'test_field',
            'title' => 'Duplicate Field'
        ]);

        // 登録失敗を確認
        $this->assertFalse($result);
    }

    /**
     * get_matching_field_groups()メソッドが正しく動作することをテスト
     */
    public function test_get_matching_field_groups() {
        // 必要なモックを設定
        WP_Mock::userFunction('plugin_dir_path', [
            'return' => '/path/to/plugin/'
        ]);

        WP_Mock::userFunction('plugin_dir_url', [
            'return' => 'http://example.com/wp-content/plugins/plugin/'
        ]);

        // インスタンスを取得
        $core = CFSE_Core::get_instance();

        // リフレクションを使って非公開プロパティにアクセス
        $reflection = new ReflectionClass($core);
        $property = $reflection->getProperty('field_definitions');
        $property->setAccessible(true);

        // フィールド定義を手動でセット
        $property->setValue($core, [
            'post_fields' => [
                'id' => 'post_fields',
                'title' => 'Post Fields',
                'placement' => ['post_type' => 'post'],
                'fields' => []
            ],
            'page_fields' => [
                'id' => 'page_fields',
                'title' => 'Page Fields',
                'placement' => ['post_type' => 'page'],
                'fields' => []
            ],
            'all_fields' => [
                'id' => 'all_fields',
                'title' => 'All Fields',
                'placement' => [],
                'fields' => []
            ]
        ]);

        // 投稿タイプ 'post' に一致するフィールドグループを取得
        $matching_post = $core->get_matching_field_groups('post');

        // 'post_fields' と 'all_fields' が含まれていることを確認
        $this->assertCount(2, $matching_post);
        $this->assertEquals('post_fields', $matching_post[0]['id']);
        $this->assertEquals('all_fields', $matching_post[1]['id']);

        // 投稿タイプ 'page' に一致するフィールドグループを取得
        $matching_page = $core->get_matching_field_groups('page');

        // 'page_fields' と 'all_fields' が含まれていることを確認
        $this->assertCount(2, $matching_page);
        $this->assertEquals('page_fields', $matching_page[0]['id']);
        $this->assertEquals('all_fields', $matching_page[1]['id']);
    }

    /**
     * get_field()メソッドが正しく動作することをテスト
     */
    public function test_get_field() {
        // 必要なモックを設定
        WP_Mock::userFunction('plugin_dir_path', [
            'return' => '/path/to/plugin/'
        ]);

        WP_Mock::userFunction('plugin_dir_url', [
            'return' => 'http://example.com/wp-content/plugins/plugin/'
        ]);

        WP_Mock::userFunction('get_the_ID', [
            'return' => 123
        ]);

        // インスタンスを取得
        $core = CFSE_Core::get_instance();

        // データクラスのモックを作成
        $data_mock = $this->createMock(CFSE_Data::class);
        $data_mock->expects($this->once())
            ->method('get_field_value')
            ->with(123, 'test_field', 'postmeta')
            ->willReturn('test value');

        // リフレクションを使って非公開プロパティにアクセス
        $reflection = new ReflectionClass($core);
        $data_property = $reflection->getProperty('data');
        $data_property->setAccessible(true);
        $data_property->setValue($core, $data_mock);

        $field_def_property = $reflection->getProperty('field_definitions');
        $field_def_property->setAccessible(true);
        $field_def_property->setValue($core, [
            'group1' => [
                'id' => 'group1',
                'title' => 'Group 1',
                'table' => 'postmeta',
                'fields' => [
                    [
                        'id' => 'test_field',
                        'type' => 'text'
                    ]
                ]
            ]
        ]);

        // フィールド値を取得
        $value = $core->get_field('test_field');

        // 正しい値が返されることを確認
        $this->assertEquals('test value', $value);
    }
    }
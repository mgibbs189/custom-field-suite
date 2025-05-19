<?php
/**
 * CFSE_Render クラスのテスト
 *
 * @package CFSE
 */

use PHPUnit\Framework\TestCase;

class CFSE_Render_Test extends TestCase {

    /**
     * 各テスト前の準備処理
     */
    public function setUp(): void {
        parent::setUp();
        WP_Mock::setUp();

        // テスト用のフィールドタイプをロード
        setup_test_field_types();
    }

    /**
     * 各テスト後のクリーンアップ
     */
    public function tearDown(): void {
        WP_Mock::tearDown();
        parent::tearDown();
    }

    /**
     * render_meta_box()メソッドが正しくメタボックスをレンダリングすることをテスト
     */
    public function test_render_meta_box() {
        // コアモックを作成
        $core_mock = $this->createMock(CFSE_Core::class);

        // レンダラーのインスタンスを作成
        $render = new CFSE_Render($core_mock);

        // WP_Postモックを作成
        $post_mock = $this->createMock(WP_Post::class);
        $post_mock->ID = 123;

        // フィールドグループの設定
        $field_group = [
            'id' => 'test_group',
            'title' => 'Test Group',
            'description' => 'This is a test group',
            'fields' => [
                [
                    'id' => 'test_field',
                    'label' => 'Test Field',
                    'type' => 'text'
                ]
            ],
            'table' => 'postmeta'
        ];

        // メタボックスの引数
        $metabox = [
            'args' => [
                'field_group' => $field_group
            ]
        ];

        // nonceフィールドのモック
        WP_Mock::userFunction('wp_nonce_field', [
            'times' => 1,
            'args' => ['cfse_save_post', 'cfse_nonce'],
            'return' => '<input type="hidden" name="cfse_nonce" value="nonce_value">'
        ]);

        // wp_kses_postのモック
        WP_Mock::userFunction('wp_kses_post', [
            'args' => ['This is a test group'],
            'return' => 'This is a test group'
        ]);

        // レンダリング結果をキャプチャするためにバッファリング開始
        ob_start();
        $render->render_meta_box($post_mock, $metabox);
        $output = ob_get_clean();

        // 生成されたHTMLに必要な要素が含まれていることを確認
        $this->assertStringContainsString('cfse-fields-container', $output);
        $this->assertStringContainsString('data-group-id="test_group"', $output);
        $this->assertStringContainsString('cfse-group-description', $output);
        $this->assertStringContainsString('This is a test group', $output);
        $this->assertStringContainsString('cfse_nonce', $output);
    }

    /**
     * render_field()メソッドが正しくフィールドをレンダリングすることをテスト
     */
    public function test_render_field() {
        // テキストフィールドのモックを作成
        $text_field_mock = $this->createMock(CFSE_Field_Text::class);
        $text_field_mock->method('html')
            ->willReturn('<input type="text" name="cfse_fields[test_field]" value="test value">');

        // コアモックを作成
        $core_mock = $this->createMock(CFSE_Core::class);
        $core_mock->method('get_field_type')
            ->willReturn($text_field_mock);

        // データモックを作成
        $data_mock = $this->createMock(CFSE_Data::class);
        $data_mock->method('get_field_value')
            ->willReturn('test value');

        // コアモックのdataプロパティを設定
        $reflection = new ReflectionClass($core_mock);
        $property = $reflection->getProperty('data');
        $property->setAccessible(true);
        $property->setValue($core_mock, $data_mock);

        // レンダラーのインスタンスを作成
        $render = new CFSE_Render($core_mock);

        // フィールドの設定
        $field = [
            'id' => 'test_field',
            'label' => 'Test Field',
            'type' => 'text',
            'description' => 'This is a test field'
        ];

        // wp_kses_postのモック
        WP_Mock::userFunction('wp_kses_post', [
            'args' => ['This is a test field'],
            'return' => 'This is a test field'
        ]);

        // レンダリング結果をキャプチャするためにバッファリング開始
        ob_start();
        $render->render_field($field, 123, 'postmeta');
        $output = ob_get_clean();

        // 生成されたHTMLに必要な要素が含まれていることを確認
        $this->assertStringContainsString('cfse-field-wrapper', $output);
        $this->assertStringContainsString('cfse-field-type-text', $output);
        $this->assertStringContainsString('data-field-id="test_field"', $output);
        $this->assertStringContainsString('cfse-field-label', $output);
        $this->assertStringContainsString('Test Field', $output);
        $this->assertStringContainsString('cfse-field-input', $output);
        $this->assertStringContainsString('cfse-field-description', $output);
        $this->assertStringContainsString('This is a test field', $output);
        $this->assertStringContainsString('<input type="text" name="cfse_fields[test_field]" value="test value">', $output);
    }

    /**
     * 未知のフィールドタイプを処理できることをテスト
     */
    public function test_render_unknown_field_type() {
        // コアモックを作成
        $core_mock = $this->createMock(CFSE_Core::class);
        $core_mock->method('get_field_type')
            ->willReturn(null);

        // レンダラーのインスタンスを作成
        $render = new CFSE_Render($core_mock);

        // 未知のフィールドタイプの設定
        $field = [
            'id' => 'unknown_field',
            'label' => 'Unknown Field',
            'type' => 'unknown_type'
        ];

        // レンダリング結果をキャプチャするためにバッファリング開始
        ob_start();
        $render->render_field($field, 123, 'postmeta');
        $output = ob_get_clean();

        // エラーメッセージが表示されることを確認
        $this->assertStringContainsString('cfse-field-error', $output);
        $this->assertStringContainsString('Unknown field type', $output);
    }

    /**
     * 必須フィールドが正しくレンダリングされることをテスト
     */
    public function test_render_required_field() {
        // テキストフィールドのモックを作成
        $text_field_mock = $this->createMock(CFSE_Field_Text::class);
        $text_field_mock->method('html')
            ->willReturn('<input type="text" name="cfse_fields[required_field]" required>');

        // コアモックを作成
        $core_mock = $this->createMock(CFSE_Core::class);
        $core_mock->method('get_field_type')
            ->willReturn($text_field_mock);

        // データモックを作成
        $data_mock = $this->createMock(CFSE_Data::class);
        $data_mock->method('get_field_value')
            ->willReturn('');

        // コアモックのdataプロパティを設定
        $reflection = new ReflectionClass($core_mock);
        $property = $reflection->getProperty('data');
        $property->setAccessible(true);
        $property->setValue($core_mock, $data_mock);

        // レンダラーのインスタンスを作成
        $render = new CFSE_Render($core_mock);

        // 必須フィールドの設定
        $field = [
            'id' => 'required_field',
            'label' => 'Required Field',
            'type' => 'text',
            'required' => true
        ];

        // レンダリング結果をキャプチャするためにバッファリング開始
        ob_start();
        $render->render_field($field, 123, 'postmeta');
        $output = ob_get_clean();

        // 必須マークが表示されることを確認
        $this->assertStringContainsString('cfse-required', $output);
        $this->assertStringContainsString('*', $output);
    }

    /**
     * 条件付き表示設定を持つフィールドが正しくレンダリングされることをテスト
     */
    public function test_render_conditional_field() {
        // テキストフィールドのモックを作成
        $text_field_mock = $this->createMock(CFSE_Field_Text::class);
        $text_field_mock->method('html')
            ->willReturn('<input type="text" name="cfse_fields[conditional_field]">');

        // コアモックを作成
        $core_mock = $this->createMock(CFSE_Core::class);
        $core_mock->method('get_field_type')
            ->willReturn($text_field_mock);

        // データモックを作成
        $data_mock = $this->createMock(CFSE_Data::class);
        $data_mock->method('get_field_value')
            ->willReturn('');

        // コアモックのdataプロパティを設定
        $reflection = new ReflectionClass($core_mock);
        $property = $reflection->getProperty('data');
        $property->setAccessible(true);
        $property->setValue($core_mock, $data_mock);

        // レンダラーのインスタンスを作成
        $render = new CFSE_Render($core_mock);

        // 条件付き表示設定を持つフィールド
        $field = [
            'id' => 'conditional_field',
            'label' => 'Conditional Field',
            'type' => 'text',
            'conditional_logic' => [
                'action' => 'show',
                'groups' => [
                    [
                        [
                            'field' => 'toggle_field',
                            'operator' => '==',
                            'value' => '1'
                        ]
                    ]
                ]
            ]
        ];

        // json_encodeのモック
        WP_Mock::userFunction('json_encode', [
            'args' => [$field['conditional_logic']],
            'return' => '{"action":"show","groups":[[{"field":"toggle_field","operator":"==","value":"1"}]]}'
        ]);

        // レンダリング結果をキャプチャするためにバッファリング開始
        ob_start();
        $render->render_field($field, 123, 'postmeta');
        $output = ob_get_clean();

        // 条件付き表示データ属性が含まれていることを確認
        $this->assertStringContainsString('data-conditional-logic=', $output);
        $this->assertStringContainsString('show', $output);
        $this->assertStringContainsString('toggle_field', $output);
    }

    /**
     * format_value_for_frontend()メソッドが正しく値を整形することをテスト
     */
    public function test_format_value_for_frontend() {
        // テキストフィールドのモックを作成
        $text_field_mock = $this->createMock(CFSE_Field_Text::class);
        $text_field_mock->method('format_value_for_display')
            ->willReturn('Formatted Text');

        // コアモックを作成
        $core_mock = $this->createMock(CFSE_Core::class);
        $core_mock->method('get_field_type')
            ->willReturn($text_field_mock);

        // レンダラーのインスタンスを作成
        $render = new CFSE_Render($core_mock);

        // フィールドの設定
        $field = [
            'id' => 'test_field',
            'type' => 'text'
        ];

        // フィルターのモック
        WP_Mock::onFilter('cfse_format_value_for_frontend')
            ->with('Formatted Text', 'Raw Text', $field)
            ->reply('Filtered Text');

        // 値を整形
        $formatted_value = $render->format_value_for_frontend('Raw Text', $field);

        // フィルター後の値が返されることを確認
        $this->assertEquals('Filtered Text', $formatted_value);

        // 未知のフィールドタイプの場合
        $core_mock = $this->createMock(CFSE_Core::class);
        $core_mock->method('get_field_type')
            ->willReturn(null);

        $render = new CFSE_Render($core_mock);

        // フィルターのモック（未知のタイプの場合）
        WP_Mock::onFilter('cfse_format_value_for_frontend')
            ->with('Unknown Type Value', 'Unknown Type Value', ['id' => 'unknown_field', 'type' => 'unknown_type'])
            ->reply('Unknown Type Value');

        // 未知のタイプの値を整形
        $unknown_value = $render->format_value_for_frontend('Unknown Type Value', ['id' => 'unknown_field', 'type' => 'unknown_type']);

        // 元の値がそのまま返されることを確認
        $this->assertEquals('Unknown Type Value', $unknown_value);
    }

    /**
     * render_loop_row_template()メソッドが正しくループフィールドのテンプレート行をレンダリングすることをテスト
     */
    public function test_render_loop_row_template() {
        // テキストフィールドのモックを作成
        $text_field_mock = $this->createMock(CFSE_Field_Text::class);
        $text_field_mock->method('html')
            ->willReturn('<input type="text" name="test_loop[{{index}}][sub_field]" value="">');

        // コアモックを作成
        $core_mock = $this->createMock(CFSE_Core::class);
        $core_mock->method('get_field_type')
            ->willReturn($text_field_mock);

        // レンダラーのインスタンスを作成
        $render = new CFSE_Render($core_mock);

        // ループフィールドの設定
        $field = [
            'id' => 'test_loop',
            'label' => 'Test Loop',
            'type' => 'loop',
            'sub_fields' => [
                [
                    'id' => 'sub_field',
                    'label' => 'Sub Field',
                    'type' => 'text'
                ]
            ]
        ];

        // テンプレート行をレンダリング
        $template = $render->render_loop_row_template($field);

        // 生成されたHTMLに必要な要素が含まれていることを確認
        $this->assertStringContainsString('cfse-loop-row', $template);
        $this->assertStringContainsString('data-row-index="{{index}}"', $template);
        $this->assertStringContainsString('cfse-loop-row-header', $template);
        $this->assertStringContainsString('cfse-loop-row-label', $template);
        $this->assertStringContainsString('cfse-loop-row-actions', $template);
        $this->assertStringContainsString('cfse-loop-row-toggle', $template);
        $this->assertStringContainsString('cfse-loop-row-remove', $template);
        $this->assertStringContainsString('cfse-loop-row-handle', $template);
        $this->assertStringContainsString('cfse-loop-row-content', $template);
        $this->assertStringContainsString('cfse-loop-sub-field', $template);
        $this->assertStringContainsString('Sub Field', $template);
        $this->assertStringContainsString('<input type="text" name="test_loop[{{index}}][sub_field]" value="">', $template);
    }

    /**
     * render_loop_rows()メソッドが正しくループフィールドの既存行をレンダリングすることをテスト
     */
    public function test_render_loop_rows() {
        // テキストフィールドのモックを作成
        $text_field_mock = $this->createMock(CFSE_Field_Text::class);
        $text_field_mock->method('html')
            ->willReturnCallback(function ($field, $value) {
                return '<input type="text" name="' . $field['name'] . '" value="' . $value . '">';
            });

        // コアモックを作成
        $core_mock = $this->createMock(CFSE_Core::class);
        $core_mock->method('get_field_type')
            ->willReturn($text_field_mock);

        // レンダラーのインスタンスを作成
        $render = new CFSE_Render($core_mock);

        // ループフィールドの設定
        $field = [
            'id' => 'test_loop',
            'label' => 'Test Loop',
            'type' => 'loop',
            'sub_fields' => [
                [
                    'id' => 'sub_field',
                    'label' => 'Sub Field',
                    'type' => 'text'
                ]
            ]
        ];

        // 行データ
        $values = [
            [
                'sub_field' => 'Value 1'
            ],
            [
                'sub_field' => 'Value 2'
            ]
        ];

        // 行をレンダリング
        $rows_html = $render->render_loop_rows($field, $values);

        // 生成されたHTMLに必要な要素が含まれていることを確認
        $this->assertStringContainsString('cfse-loop-row', $rows_html);
        $this->assertStringContainsString('data-row-index="0"', $rows_html);
        $this->assertStringContainsString('data-row-index="1"', $rows_html);
        $this->assertStringContainsString('cfse-loop-row-label', $rows_html);
        $this->assertStringContainsString('Row #1', $rows_html);
        $this->assertStringContainsString('Row #2', $rows_html);
        $this->assertStringContainsString('<input type="text" name="test_loop[0][sub_field]" value="Value 1">', $rows_html);
        $this->assertStringContainsString('<input type="text" name="test_loop[1][sub_field]" value="Value 2">', $rows_html);
    }

    /**
     * format_row_label()メソッドが正しくループフィールドの行ラベルをフォーマットすることをテスト
     */
    public function test_format_row_label() {
        // コアモックを作成
        $core_mock = $this->createMock(CFSE_Core::class);

        // レンダラーのインスタンスを作成
        $render = new CFSE_Render($core_mock);

        // リフレクションを使って非公開メソッドにアクセス
        $reflection = new ReflectionClass($render);
        $method = $reflection->getMethod('format_row_label');
        $method->setAccessible(true);

        // シンプルなフォーマット文字列
        $format = 'Item: {item_name}';
        $row_value = [
            'item_name' => 'Test Item',
            'item_price' => '100'
        ];

        $label = $method->invoke($render, $format, $row_value);

        // プレースホルダが値で置換されていることを確認
        $this->assertEquals('Item: Test Item', $label);

        // 複数のプレースホルダを含むフォーマット文字列
        $format = 'Item: {item_name} - Price: {item_price}';

        $label = $method->invoke($render, $format, $row_value);

        // 複数のプレースホルダが値で置換されていることを確認
        $this->assertEquals('Item: Test Item - Price: 100', $label);

        // 存在しないプレースホルダを含むフォーマット文字列
        $format = 'Item: {item_name} - Category: {item_category}';

        $label = $method->invoke($render, $format, $row_value);

        // 存在しないプレースホルダが空文字で置換されていることを確認
        $this->assertEquals('Item: Test Item - Category: ', $label);

        // 長い値が切り詰められることを確認
        $row_value_long = [
            'item_name' => 'This is a very long item name that should be truncated in the label'
        ];

        $label = $method->invoke($render, 'Item: {item_name}', $row_value_long);

        // 長い値が切り詰められていることを確認
        $this->assertEquals('Item: This is a very long...', $label);
    }

    /**
     * escape_html()メソッドが正しくHTMLをエスケープすることをテスト
     */
    public function test_escape_html() {
        // コアモックを作成
        $core_mock = $this->createMock(CFSE_Core::class);

        // レンダラーのインスタンスを作成
        $render = new CFSE_Render($core_mock);

        // wp_ksesのモック
        WP_Mock::userFunction('wp_kses', [
            'args' => [
                '<p>Test <b>HTML</b> with <script>alert("XSS");</script></p>',
                [
                    'p' => [],
                    'b' => []
                ]
            ],
            'return' => '<p>Test <b>HTML</b> with alert("XSS");</p>'
        ]);

        // HTMLをエスケープ
        $escaped_html = $render->escape_html('<p>Test <b>HTML</b> with <script>alert("XSS");</script></p>', [
            'p' => [],
            'b' => []
        ]);

        // スクリプトタグが削除されていることを確認
        $this->assertEquals('<p>Test <b>HTML</b> with alert("XSS");</p>', $escaped_html);

        // デフォルトの許可タグでのエスケープ
        WP_Mock::userFunction('wp_kses', [
            'args' => [
                '<a href="https://example.com">Link</a> <script>alert("XSS");</script>',
                WP_Mock\Functions::type('array')
            ],
            'return' => '<a href="https://example.com">Link</a> alert("XSS");'
        ]);

        $escaped_default = $render->escape_html('<a href="https://example.com">Link</a> <script>alert("XSS");</script>');

        // スクリプトタグが削除され、アンカータグが保持されていることを確認
        $this->assertEquals('<a href="https://example.com">Link</a> alert("XSS");', $escaped_default);
    }
    }
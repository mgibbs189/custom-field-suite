<?php
/**
 * CFSE_Field_Type および実装クラスのテスト
 *
 * @package CFSE
 */

use PHPUnit\Framework\TestCase;

class CFSE_Field_Type_Test extends TestCase {

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
     * CFSE_Field_Text::html()メソッドが正しくHTMLを生成することをテスト
     */
    public function test_text_field_html() {
        $text_field = new CFSE_Field_Text();

        // 基本的なフィールド
        $field = [
            'id' => 'test_text',
            'label' => 'Test Text',
            'type' => 'text'
        ];

        $html = $text_field->html($field, 'sample text');

        // 生成されたHTMLに必要な要素が含まれていることを確認
        $this->assertStringContainsString('type="text"', $html);
        $this->assertStringContainsString('id="test_text"', $html);
        $this->assertStringContainsString('name="cfse_fields[test_text]"', $html);
        $this->assertStringContainsString('value="sample text"', $html);
        $this->assertStringContainsString('class="cfse-text-field', $html);

        // プレースホルダー付きのフィールド
        $field_with_placeholder = [
            'id' => 'test_text',
            'label' => 'Test Text',
            'type' => 'text',
            'placeholder' => 'Enter text here'
        ];

        $html = $text_field->html($field_with_placeholder, '');

        // プレースホルダーが含まれていることを確認
        $this->assertStringContainsString('placeholder="Enter text here"', $html);

        // 最大文字数付きのフィールド
        $field_with_maxlength = [
            'id' => 'test_text',
            'label' => 'Test Text',
            'type' => 'text',
            'maxlength' => 100
        ];

        $html = $text_field->html($field_with_maxlength, '');

        // 最大文字数が含まれていることを確認
        $this->assertStringContainsString('maxlength="100"', $html);

        // 読み取り専用のフィールド
        $field_readonly = [
            'id' => 'test_text',
            'label' => 'Test Text',
            'type' => 'text',
            'readonly' => true
        ];

        $html = $text_field->html($field_readonly, 'readonly text');

        // 読み取り専用属性が含まれていることを確認
        $this->assertStringContainsString('readonly="readonly"', $html);

        // 必須フィールド
        $field_required = [
            'id' => 'test_text',
            'label' => 'Test Text',
            'type' => 'text',
            'required' => true
        ];

        $html = $text_field->html($field_required, '');

        // 必須属性が含まれていることを確認
        $this->assertStringContainsString('required="required"', $html);
    }

    /**
     * CFSE_Field_Textarea::html()メソッドが正しくHTMLを生成することをテスト
     */
    public function test_textarea_field_html() {
        $textarea_field = new CFSE_Field_Textarea();

        // 基本的なフィールド
        $field = [
            'id' => 'test_textarea',
            'label' => 'Test Textarea',
            'type' => 'textarea'
        ];

        $html = $textarea_field->html($field, 'sample text content');

        // 生成されたHTMLに必要な要素が含まれていることを確認
        $this->assertStringContainsString('<textarea', $html);
        $this->assertStringContainsString('id="test_textarea"', $html);
        $this->assertStringContainsString('name="cfse_fields[test_textarea]"', $html);
        $this->assertStringContainsString('sample text content', $html);
        $this->assertStringContainsString('class="cfse-textarea-field', $html);

        // 行数・列数指定のフィールド
        $field_with_rows_cols = [
            'id' => 'test_textarea',
            'label' => 'Test Textarea',
            'type' => 'textarea',
            'rows' => 10,
            'cols' => 50
        ];

        $html = $textarea_field->html($field_with_rows_cols, '');

        // 行数・列数が含まれていることを確認
        $this->assertStringContainsString('rows="10"', $html);
        $this->assertStringContainsString('cols="50"', $html);
    }

    /**
     * CFSE_Field_Loop::html()メソッドが正しくHTMLを生成することをテスト
     */
    public function test_loop_field_html() {
        // CFSE_Coreのモックを作成
        $core_mock = $this->createMock(CFSE_Core::class);

        // レンダークラスのモックを作成
        $render_mock = $this->createMock(CFSE_Render::class);
        $render_mock->method('render_loop_rows')->willReturn('<div class="test-loop-rows">Rendered rows</div>');
        $render_mock->method('render_loop_row_template')->willReturn('<div class="test-loop-template">Row template</div>');

        // コアモックのrenderプロパティに設定
        $reflection = new ReflectionClass($core_mock);
        $property = $reflection->getProperty('render');
        $property->setAccessible(true);
        $property->setValue($core_mock, $render_mock);

        // WP_Mockの設定
        WP_Mock::userFunction('get_current_screen', [
            'return' => (object)['base' => 'post']
        ]);

        // CFSE_Coreのget_instance()の戻り値を設定
        WP_Mock::userFunction('CFSE_Core::get_instance', [
            'return' => $core_mock
        ]);

        // フィールドの設定
        $field = [
            'id' => 'test_loop',
            'label' => 'Test Loop',
            'type' => 'loop',
            'sub_fields' => [
                [
                    'id' => 'sub_field1',
                    'label' => 'Sub Field 1',
                    'type' => 'text'
                ],
                [
                    'id' => 'sub_field2',
                    'label' => 'Sub Field 2',
                    'type' => 'textarea'
                ]
            ]
        ];

        // 値（行の配列）
        $value = [
            [
                'sub_field1' => 'Row 1 Field 1',
                'sub_field2' => 'Row 1 Field 2'
            ],
            [
                'sub_field1' => 'Row 2 Field 1',
                'sub_field2' => 'Row 2 Field 2'
            ]
        ];

        // ループフィールドのインスタンスを作成
        $loop_field = new CFSE_Field_Loop();

        // HTML生成
        $html = $loop_field->html($field, $value);

        // 生成されたHTMLに必要な要素が含まれていることを確認
        $this->assertStringContainsString('class="cfse-loop-field"', $html);
        $this->assertStringContainsString('data-field-id="test_loop"', $html);
        $this->assertStringContainsString('class="cfse-loop-header"', $html);
        $this->assertStringContainsString('class="cfse-loop-body"', $html);
        $this->assertStringContainsString('class="cfse-loop-add-row"', $html);
        $this->assertStringContainsString('Rendered rows', $html);
        $this->assertStringContainsString('Row template', $html);
        $this->assertStringContainsString('class="cfse-loop-value"', $html);

        // JSONエンコードされた値が含まれていることを確認
        $this->assertStringContainsString(htmlspecialchars(json_encode($value)), $html);
    }

    /**
     * CFSE_Field_Text::prepare_value_for_database()メソッドが正しく値を処理することをテスト
     */
    public function test_text_field_prepare_value_for_database() {
        $text_field = new CFSE_Field_Text();

        // 通常の値
        $field = ['id' => 'test_text', 'type' => 'text'];
        $value = 'Sample text with <script>alert("xss");</script>';

        // sanitize_text_field()のモック
        WP_Mock::userFunction('sanitize_text_field', [
            'args' => [$value],
            'return' => 'Sample text with alert("xss");'
        ]);

        $processed_value = $text_field->prepare_value_for_database($value, $field);

        // サニタイズされた値が返されることを確認
        $this->assertEquals('Sample text with alert("xss");', $processed_value);

        // 配列の値（最初の要素が使用される）
        $array_value = ['item1', 'item2'];

        WP_Mock::userFunction('sanitize_text_field', [
            'args' => ['item1'],
            'return' => 'item1'
        ]);

        $processed_array_value = $text_field->prepare_value_for_database($array_value, $field);

        // 配列の最初の要素がサニタイズされて返されることを確認
        $this->assertEquals('item1', $processed_array_value);
    }

    /**
     * CFSE_Field_Loop::prepare_value_for_database()メソッドが正しく値を処理することをテスト
     */
    public function test_loop_field_prepare_value_for_database() {
        // モックの設定
        $text_field_mock = $this->createMock(CFSE_Field_Text::class);
        $text_field_mock->method('prepare_value_for_database')
            ->willReturnCallback(function($value, $field) {
                return 'sanitized: ' . $value;
            });

        $textarea_field_mock = $this->createMock(CFSE_Field_Textarea::class);
        $textarea_field_mock->method('prepare_value_for_database')
            ->willReturnCallback(function($value, $field) {
                return 'sanitized textarea: ' . $value;
            });

        // CFSE_Coreのモックを作成
        $core_mock = $this->createMock(CFSE_Core::class);
        $core_mock->method('get_field_type')
            ->willReturnCallback(function($type) use ($text_field_mock, $textarea_field_mock) {
                if ($type === 'text') {
                    return $text_field_mock;
                } elseif ($type === 'textarea') {
                    return $textarea_field_mock;
                }
                return null;
            });

        // WP_Mockの設定
        WP_Mock::userFunction('CFSE_Core::get_instance', [
            'return' => $core_mock
        ]);

        // ループフィールドの設定
        $field = [
            'id' => 'test_loop',
            'type' => 'loop',
            'sub_fields' => [
                [
                    'id' => 'sub_field1',
                    'label' => 'Sub Field 1',
                    'type' => 'text'
                ],
                [
                    'id' => 'sub_field2',
                    'label' => 'Sub Field 2',
                    'type' => 'textarea'
                ]
            ]
        ];

        // 値（JSONエンコードされた文字列）
        $json_value = json_encode([
            [
                'sub_field1' => 'Row 1 Field 1',
                'sub_field2' => 'Row 1 Field 2'
            ],
            [
                'sub_field1' => 'Row 2 Field 1',
                'sub_field2' => 'Row 2 Field 2'
            ]
        ]);

        // ループフィールドのインスタンスを作成
        $loop_field = new CFSE_Field_Loop();

        // 値を処理
        $processed_value = $loop_field->prepare_value_for_database($json_value, $field);

        // 正しい形式の配列が返されることを確認
        $this->assertIsArray($processed_value);
        $this->assertCount(2, $processed_value);

        // 各サブフィールドの値が正しく処理されていることを確認
        $this->assertEquals('sanitized: Row 1 Field 1', $processed_value[0]['sub_field1']);
        $this->assertEquals('sanitized textarea: Row 1 Field 2', $processed_value[0]['sub_field2']);
        $this->assertEquals('sanitized: Row 2 Field 1', $processed_value[1]['sub_field1']);
        $this->assertEquals('sanitized textarea: Row 2 Field 2', $processed_value[1]['sub_field2']);

        // 無効なJSON
        $invalid_json = '{invalid json';
        $processed_invalid = $loop_field->prepare_value_for_database($invalid_json, $field);

        // 無効なJSONの場合は空の配列が返されることを確認
        $this->assertIsArray($processed_invalid);
        $this->assertEmpty($processed_invalid);

        // 配列値
        $array_value = [
            [
                'sub_field1' => 'Direct Array 1',
                'sub_field2' => 'Direct Array 2'
            ]
        ];

        $processed_array = $loop_field->prepare_value_for_database($array_value, $field);

        // 配列の場合はそのまま処理されることを確認
        $this->assertIsArray($processed_array);
        $this->assertCount(1, $processed_array);
        $this->assertEquals('sanitized: Direct Array 1', $processed_array[0]['sub_field1']);
    }
    }
# Custom Field Suite Extended - 実装概要
Custom Field Suite Extendedは、オリジナルのCustom Field Suiteプラグインを拡張し、jQueryへの依存を解消しつつ、よりコード中心のアプローチでカスタムフィールドを管理するプラグインです。以下に主要なファイルと機能を示します。

## 主要ファイル

- custom-field-suite-extension.php - メインプラグインファイル

  - プラグインの初期化
  - CFS()互換関数の提供
  - 公開APIの提供


- class-cfse-core.php - コアクラス

  - フィールド定義の登録と管理
  - フィールドタイプのロード
  - メタボックスの表示


- class-cfse-data.php - データ操作クラス

  - データベースとの連携
  - カスタムテーブル対応
  - キャッシュ管理

- class-cfse-render.php - レンダリングクラス

  - フィールドのHTMLレンダリング
  - ループフィールドの構築
  - 条件付き表示の処理


- class-cfse-field-type.php - フィールドタイプ基底クラス

  - 共通機能の提供
  - サブクラスで拡張可能なインターフェース

- admin.js - 管理画面JavaScript

  - jQueryを使用しないUI処理
  - ループフィールドの操作
  - 条件付き表示のロジック

- admin.css - 管理画面スタイル

  - UIの視覚的スタイリング

## 使用方法

### フィールド定義

フィールド定義はCFS()->register_field_definition()関数を使ってコードで行います：
```php
// functions.phpまたは独自プラグインで
function register_my_field_definitions() {
    CFS()->register_field_definition([
        'id' => 'article_fields',
        'title' => '記事設定',
        'placement' => ['post_type' => 'post'],
        'table' => 'postmeta',
        'fields' => [
            [
                'id' => 'article_subtitle',
                'label' => 'サブタイトル',
                'type' => 'text',
            ],
            [
                'id' => 'article_sections',
                'label' => 'セクション',
                'type' => 'loop',
                'sub_fields' => [
                    ['id' => 'section_title', 'label' => 'タイトル', 'type' => 'text'],
                    ['id' => 'section_content', 'label' => '内容', 'type' => 'wysiwyg'],
                ]
            ],
        ],
    ]);
}
add_action('init', 'register_my_field_definitions', 20);
```

## 値の取得

テンプレートでの値の取得はオリジナルのCFSと同様に行えます

```php
// 単一フィールドの取得
$subtitle = CFS()->get('article_subtitle');
echo '<h2>' . esc_html($subtitle) . '</h2>';

// ループフィールドの取得
$sections = CFS()->get('article_sections');
if (!empty($sections)) {
    foreach ($sections as $section) {
        echo '<h3>' . esc_html($section['section_title']) . '</h3>';
        echo '<div class="content">' . $section['section_content'] . '</div>';
    }
}

// 代替API
if (cfse_have_rows('article_sections')) {
    while (cfse_have_rows('article_sections')) {
        cfse_the_row('article_sections');

        echo '<h3>' . esc_html(cfse_get_sub_field('section_title', 'article_sections')) . '</h3>';
        echo '<div class="content">' . cfse_get_sub_field('section_content', 'article_sections') . '</div>';
    }
}
```

## 主な機能と特徴
- jQuery非依存:

  - 純粋なJavaScriptでUI操作を実装
  - WordPressの標準機能のみを使用
- コード中心の設計:
  - フィールド定義をコードで管理
  - バージョン管理が容易
  - 環境間の移行が簡単
- 拡張性:
  - 新しいフィールドタイプを簡単に追加可能
  - フィルターフックによるカスタマイズ
  - カスタムテーブル対応
- CFS互換性
  - オリジナルのCFS()関数でアクセス可能
  - 既存のコードとの互換性を維持
- 今後の開発課題:

テスト: 各フィールドタイプの徹底的なテスト
追加フィールドタイプ: 追加のフィールドタイプの実装
ドキュメント: より詳細な開発者ドキュメントの作成
インポート/エクスポート: フィールド定義のインポート/エクスポート機能
REST API対応: REST APIからのアクセスサポート

このプラグインは、Custom Field Suiteの使いやすさを保ちながら、より現代的で軽量なアプローチを提供することを目指しています。コードでのフィールド定義により、開発者にとって使いやすく、バージョン管理と環境間の一貫性を確保するための選択肢となっています。
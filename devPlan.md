# Custom Field Suite 拡張プラグイン - 設計概要

## プロジェクト目標
https://github.com/mgibbs189/custom-field-suite
上記のプラグインを拡張して、下記の特徴を持つプラグインを開発:
* jQueryへの依存を解消
* データ型、フィールド名、表示名、格納先テーブル名などの定義をユーザーがカスタム定義する
* 投稿タイプに対する新規カスタムフィールドの追加は管理画面UIではなくfunctions.phpにコードを書いて行う
* フィールドの値編集、ループフィールドの並び替えなどについてオリジナルの実装を踏襲
* 可能な限りシンプルな実装を行う
* コーディングルールとして、if文のネスト禁止を取り入れる

## 基本設計

### アーキテクチャ

- シングルトンパターンでメインクラスを実装
- モジュール方式でフィールドタイプを分離
- WordPress標準のフックを活用（actions/filters）
- コーディングでフィールド定義を行うAPI設計

### 主要コンポーネント

- コアクラス：プラグインの中心となるシングルトンクラス
- フィールドタイプクラス：各フィールドタイプを表現する抽象クラスと実装クラス群
- レンダリングシステム：管理画面用のフィールド表示機能
- 保存システム：フィールド値の保存・取得処理
- JavaScript API：jQuery不使用の純粋JavaScriptによるUI操作

### ファイル構造

```
custom-field-suite-extended/
├── cfse.php       # メインプラグインファイル
├── includes/
│   ├── class-cfse-core.php                # コアクラス
│   ├── class-cfse-field-type.php          # フィールドタイプ基底クラス
│   ├── class-cfse-render.php              # レンダリングクラス
│   ├── class-cfse-data.php                # データ操作クラス
│   └── field-types/                       # フィールドタイプ実装
│       ├── class-cfse-field-text.php
│       ├── class-cfse-field-textarea.php
│       ├── class-cfse-field-wysiwyg.php
│       ├── class-cfse-field-select.php
│       ├── class-cfse-field-checkbox.php
│       ├── class-cfse-field-radio.php
│       ├── class-cfse-field-date.php
│       ├── class-cfse-field-color.php
│       ├── class-cfse-field-file.php
│       ├── class-cfse-field-loop.php
│       └── class-cfse-field-relationship.php
├── assets/
│   ├── css/
│   │   └── admin.css                      # 管理画面スタイル
│   └── js/
│       ├── admin.js                       # 管理画面共通スクリプト
│       └── fields/                        # フィールド固有スクリプト
│           ├── loop.js                    # ループフィールド用
│           ├── date.js                    # 日付ピッカー用
│           ├── color.js                   # カラーピッカー用
│           ├── file.js                    # ファイルアップロード用
│           └── relationship.js            # リレーションシップ用
└── languages/                             # 翻訳ファイル
```

### 主要クラスの責任範囲
- CFSE_Core
  - プラグイン初期化
  - フィールド定義の登録と管理
  - プラグインが提供する機能群のコーディネート
  - 公開APIの提供
- CFSE_Field_Type（抽象クラス）
  - フィールドタイプの基本機能を定義
  - HTML生成、値の処理、バリデーションなどの共通インターフェース
- CFSE_Render
  - 管理画面でのフィールド表示処理
  - メタボックスの生成とフィールドの配置
- CFSE_Data
  - データベースとの連携
  - カスタムテーブル対応
  - データのフォーマット変換

### 実装手順

1.  準備段階
   - オリジナルCFSの動作分析とコード調査
   - 依存関係の特定（特にjQuery依存箇所）
   - コア機能の洗い出し

1. 基盤開発
   - コアクラスの実装
   - フィールドタイプの基底クラス実装
   - データ操作クラスの実装
1. 基本フィールドタイプの実装
   - テキスト、テキストエリア、セレクトなどの基本タイプ
   - レンダリング処理の実装
1. JavaScript実装
   - jQuery依存のコードを純粋JavaScriptに置き換え
   - イベント処理の実装
   - UI操作の実装
1. 高度なフィールドタイプの実装
   - ループフィールド
   - リレーションシップフィールド
   - ファイルアップロード
1. テスト・最適化
   - 動作確認と不具合修正
   - パフォーマンス最適化

### 技術的な重要ポイント

1. jQuery依存の解消
   - DOM操作はdocument.querySelectorなどのネイティブAPIを使用
   - イベント処理はaddEventListenerを使用
   - アニメーションはCSSトランジションを活用
   - AJAXリクエストはfetchAPIを使用
1. フィールド定義のコード化
```php
// 使用例
$cfse->register_field_definition([
    'id' => 'article_fields',
    'title' => '記事設定',
    'placement' => [
        'post_type' => 'post',
    ],
    'table' => 'postmeta',
    'fields' => [
        [
            'id' => 'article_subtitle',
            'label' => 'サブタイトル',
            'type' => 'text',
        ],
        // 他のフィールド...
    ],
]);
```
1. ループフィールドの実装
   - テンプレートベースのシステム
   - 行の追加・削除・並べ替え機能を管理画面にUIとして提供
   - ネストしたフィールドの処理
1. カスタムテーブル対応
   - テーブル名を指定するだけで保存先を切り替え可能
   - フィルターを使って独自テーブルへの保存にも対応

1. フロントエンド用API
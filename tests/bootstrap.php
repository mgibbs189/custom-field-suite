<?php
/**
 * PHPUnitテスト用のブートストラップファイル
 */

// Composer autoloaderを読み込み
require_once dirname(__DIR__) . '/vendor/autoload.php';

// WP_Mockをセットアップ
WP_Mock::bootstrap();

// プラグインファイルをロード
require_once dirname(__DIR__) . '/includes/class-cfse-field-type.php';
require_once dirname(__DIR__) . '/includes/class-cfse-data.php';
require_once dirname(__DIR__) . '/includes/class-cfse-render.php';
require_once dirname(__DIR__) . '/includes/class-cfse-core.php';
require_once dirname(__DIR__) . '/cfse.php';

/**
 * テスト用ユーティリティ関数
 */
function setup_test_field_types() {
    // テスト用のフィールドタイプを作成
    require_once dirname(__DIR__) . '/includes/field-types/class-cfse-field-text.php';
    require_once dirname(__DIR__) . '/includes/field-types/class-cfse-field-textarea.php';
    require_once dirname(__DIR__) . '/includes/field-types/class-cfse-field-loop.php';
}
/**
 * Custom Field Suite - 実装モジュールのテスト
 *
 * @package CFS
 * @version 3.0.0
 */

import sortable from '../sortable.js';
import tab from '../tab.js';
import dateField from '../date.js';
import colorField from '../color.js';
import { qs, qsa, addClass, removeClass } from '../utils.js';

// Sortableモジュールのテスト
describe('Sortable Module', () => {

  // テスト用DOMのセットアップ
  beforeEach(() => {
    document.body.innerHTML = `
      <div class="cfs-loop">
        <div class="cfs-loop-rows">
          <div class="cfs-row cfs-sortable-item">
            <div class="cfs-sortable-handle">Item 1</div>
          </div>
          <div class="cfs-row cfs-sortable-item">
            <div class="cfs-sortable-handle">Item 2</div>
          </div>
          <div class="cfs-row cfs-sortable-item">
            <div class="cfs-sortable-handle">Item 3</div>
          </div>
        </div>
      </div>
    `;

    // ドラッグ＆ドロップAPIのモック
    const originalDataTransfer = window.DataTransfer;
    window.DataTransfer = function() {
      this.data = {};
      this.effectAllowed = '';
      this.dropEffect = '';
    };
    window.DataTransfer.prototype.setData = function(format, data) {
      this.data[format] = data;
    };
    window.DataTransfer.prototype.getData = function(format) {
      return this.data[format] || '';
    };

    // dragstartイベントのモック
    Element.prototype.getBoundingClientRect = jest.fn().mockImplementation(function() {
      return {
        width: 100,
        height: 30,
        top: 0,
        left: 0,
        bottom: 30,
        right: 100
      };
    });
  });

  // テスト後のクリーンアップ
  afterEach(() => {
    document.body.innerHTML = '';
    jest.clearAllMocks();
  });

  test('should initialize sortable containers', () => {
    const container = qs('.cfs-loop-rows');
    sortable.init(container);

    // コンテナにクラスが追加されたことを確認
    expect(container.classList.contains('cfs-sortable-container')).toBe(true);

    // ハンドルにdraggable属性が設定されていることを確認
    const handles = qsa('.cfs-sortable-handle', container);
    expect(handles.length).toBe(3);
    expect(handles[0].getAttribute('draggable')).toBe('true');
  });

  test('should create placeholder on drag start', () => {
    const container = qs('.cfs-loop-rows');
    sortable.init(container);

    // ドラッグ開始イベントのシミュレーション
    const handle = qs('.cfs-sortable-handle', container);
    const item = handle.closest('.cfs-sortable-item');

    const dragStartEvent = new Event('dragstart', { bubbles: true });
    dragStartEvent.dataTransfer = new DataTransfer();

    handle.dispatchEvent(dragStartEvent);

    // プレースホルダーが作成されたことを確認
    expect(container.querySelector('.cfs-sortable-placeholder')).not.toBeNull();

    // ドラッグ要素にクラスが追加されたことを確認
    expect(item.classList.contains('cfs-sortable-dragging')).toBe(true);
  });
});

// Tabモジュールのテスト
describe('Tab Module', () => {

  // テスト用DOMのセットアップ
  beforeEach(() => {
    document.body.innerHTML = `
      <div class="cfs-tabs">
        <div class="cfs-tab" rel="tab1">Tab 1</div>
        <div class="cfs-tab" rel="tab2">Tab 2</div>
        <div class="cfs-tab" rel="tab3">Tab 3</div>
      </div>
      <div class="cfs-tab-content cfs-tab-content-tab1">Content 1</div>
      <div class="cfs-tab-content cfs-tab-content-tab2">Content 2</div>
      <div class="cfs-tab-content cfs-tab-content-tab3">Content 3</div>
    `;
  });

  // テスト後のクリーンアップ
  afterEach(() => {
    document.body.innerHTML = '';
  });

  test('should activate the first tab on initialization', () => {
    tab.init();

    // 最初のタブがアクティブになっていることを確認
    const firstTab = qs('.cfs-tab');
    expect(firstTab.classList.contains('active')).toBe(true);

    // 対応するコンテンツがアクティブになっていることを確認
    const firstContent = qs('.cfs-tab-content-tab1');
    expect(firstContent.classList.contains('active')).toBe(true);
  });

  test('should switch tabs when clicked', () => {
    tab.init();

    // 2番目のタブをクリック
    const secondTab = qsa('.cfs-tab')[1];
    secondTab.click();

    // 2番目のタブがアクティブになっていることを確認
    expect(secondTab.classList.contains('active')).toBe(true);

    // 1番目のタブが非アクティブになっていることを確認
    const firstTab = qsa('.cfs-tab')[0];
    expect(firstTab.classList.contains('active')).toBe(false);

    // 対応するコンテンツがアクティブになっていることを確認
    const secondContent = qs('.cfs-tab-content-tab2');
    expect(secondContent.classList.contains('active')).toBe(true);

    // 1番目のコンテンツが非アクティブになっていることを確認
    const firstContent = qs('.cfs-tab-content-tab1');
    expect(firstContent.classList.contains('active')).toBe(false);
  });

  test('should select tab programmatically', () => {
    tab.init();

    // プログラムで3番目のタブを選択
    tab.selectTab(2);

    // 3番目のタブがアクティブになっていることを確認
    const thirdTab = qsa('.cfs-tab')[2];
    expect(thirdTab.classList.contains('active')).toBe(true);

    // 対応するコンテンツがアクティブになっていることを確認
    const thirdContent = qs('.cfs-tab-content-tab3');
    expect(thirdContent.classList.contains('active')).toBe(true);
  });
});

// Date Fieldモジュールのテスト
describe('Date Field Module', () => {

  // テスト用DOMのセットアップ
  beforeEach(() => {
    document.body.innerHTML = `
      <div class="cfs_date">
        <input type="text" name="date_field" value="2025-05-15">
      </div>
      <div class="cfs_date">
        <input type="text" name="empty_date_field" value="">
      </div>
    `;

    // Dateピッカーコンポーネントのモック
    global.CFSDatepicker = jest.fn().mockImplementation(() => {
      return {
        element: document.createElement('div'),
        setDate: jest.fn(),
        focus: jest.fn()
      };
    });
  });

  // テスト後のクリーンアップ
  afterEach(() => {
    document.body.innerHTML = '';
    jest.clearAllMocks();
    delete global.CFSDatepicker;
  });

  test('should initialize date fields', () => {
    dateField.init();

    // フィールドが初期化されたことを確認
    const dateFields = qsa('.cfs_date');
    expect(dateFields[0].classList.contains('cfs-date-initialized')).toBe(true);
    expect(dateFields[1].classList.contains('cfs-date-initialized')).toBe(true);
  });

  test('should show datepicker when field is clicked', () => {
    dateField.init();

    // 入力フィールドをクリック
    const input = qs('input[name="date_field"]');
    input.click();

    // アクティブフィールドが設定されたことを確認
    expect(dateField.activeField).toBe(input);

    // コンテナが表示されていることを確認
    expect(dateField.container.style.display).not.toBe('none');
  });

  test('should parse date string correctly', () => {
    // 日付文字列のパース
    const isoDate = dateField.parseDate('2025-05-15');
    expect(isoDate instanceof Date).toBe(true);
    expect(isoDate.getFullYear()).toBe(2025);
    expect(isoDate.getMonth()).toBe(4); // 0-based (May = 4)
    expect(isoDate.getDate()).toBe(15);

    // MM/DD/YYYY形式
    const usDate = dateField.parseDate('05/15/2025');
    expect(usDate instanceof Date).toBe(true);
    expect(usDate.getFullYear()).toBe(2025);
    expect(usDate.getMonth()).toBe(4);
    expect(usDate.getDate()).toBe(15);

    // 無効な日付
    const invalidDate = dateField.parseDate('invalid');
    expect(invalidDate).toBeNull();
  });

  test('should format date correctly', () => {
    // 日付のフォーマット
    const date = new Date(2025, 4, 15); // May 15, 2025

    dateField.options.dateFormat = 'YYYY-MM-DD';
    expect(dateField.formatDate(date)).toBe('2025-05-15');

    dateField.options.dateFormat = 'MM/DD/YYYY';
    expect(dateField.formatDate(date)).toBe('05/15/2025');

    // 無効な日付
    expect(dateField.formatDate(null)).toBe('');
    expect(dateField.formatDate(new Date('invalid'))).toBe('');
  });
});

// Color Fieldモジュールのテスト
describe('Color Field Module', () => {

  // テスト用DOMのセットアップ
  beforeEach(() => {
    document.body.innerHTML = `
      <div class="cfs_color">
        <input type="text" name="color_field" value="#FF5733">
      </div>
      <div class="cfs_color">
        <input type="text" name="empty_color_field" value="">
      </div>
    `;

    // Colorピッカーコンポーネントのモック
    global.CFSColorpicker = jest.fn().mockImplementation(() => {
      return {
        element: document.createElement('div'),
        setColor: jest.fn()
      };
    });
  });

  // テスト後のクリーンアップ
  afterEach(() => {
    document.body.innerHTML = '';
    jest.clearAllMocks();
    delete global.CFSColorpicker;
  });

  test('should initialize color fields', () => {
    colorField.init();

    // フィールドが初期化されたことを確認
    const colorFields = qsa('.cfs_color');
    expect(colorFields[0].classList.contains('cfs-color-initialized')).toBe(true);
    expect(colorFields[1].classList.contains('cfs-color-initialized')).toBe(true);

    // プレビュー要素が作成されたことを確認
    const preview = qs('.cfs-color-preview', colorFields[0]);
    expect(preview).not.toBeNull();
    expect(preview.style.backgroundColor).toBe('rgb(255, 87, 51)');
  });

  test('should show colorpicker when field is clicked', () => {
    colorField.init();

    // 入力フィールドをクリック
    const input = qs('input[name="color_field"]');
    input.click();

    // アクティブフィールドが設定されたことを確認
    expect(colorField.activeField).toBe(input);

    // コンテナが表示されていることを確認
    expect(colorField.container.style.display).toBe('block');

    // カラーピッカーに色が設定されたことを確認
    expect(colorField.colorpicker.setColor).toHaveBeenCalledWith('#FF5733');
  });

  test('should validate color codes', () => {
    // 有効な色コード
    expect(colorField.validateColor('#FF5733')).toBe(true);
    expect(colorField.validateColor('FF5733')).toBe(true);
    expect(colorField.validateColor('#F00')).toBe(true);
    expect(colorField.validateColor('#FF5733FF')).toBe(true); // アルファ付き

    // 無効な色コード
    expect(colorField.validateColor('')).toBe(false);
    expect(colorField.validateColor(null)).toBe(false);
    expect(colorField.validateColor('invalid')).toBe(false);
    expect(colorField.validateColor('#GG0000')).toBe(false);
    expect(colorField.validateColor('#F0F0F0F')).toBe(false); // 7桁は無効
  });
});

// メインJSのテスト
describe('Main Module Integration', () => {

  // テスト用DOMのセットアップ
  beforeEach(() => {
    document.body.innerHTML = `
      <div class="cfs-loop">
        <div class="cfs-loop-rows">
          <div class="cfs-row">Field 1</div>
        </div>
        <a href="#" class="cfs-add-row">Add Row</a>
      </div>

      <div class="cfs-tabs">
        <div class="cfs-tab" rel="tab1">Tab 1</div>
        <div class="cfs-tab" rel="tab2">Tab 2</div>
      </div>
      <div class="cfs-tab-content cfs-tab-content-tab1">Content 1</div>
      <div class="cfs-tab-content cfs-tab-content-tab2">Content 2</div>

      <div class="cfs-conditional-logic" data-target="field_1" data-operator="and">
        <a href="#" class="toggle">Toggle</a>
        <div class="content hidden">
          <div class="rules">
            <div class="rule">
              <select class="rule-field">
                <option value="field_2">Field 2</option>
              </select>
              <select class="rule-type">
                <option value="==">==</option>
              </select>
              <input type="text" class="rule-value" value="test">
              <a href="#" class="remove-rule">Remove</a>
            </div>
          </div>
          <a href="#" class="add-rule">Add Rule</a>
        </div>
      </div>

      <div id="field_1" class="field">Target Field</div>
      <input type="text" name="field_2" value="test">
    `;

    // グローバルCFSオブジェクトのモック
    global.CFS = {
      ajax_url: '/wp-admin/admin-ajax.php',
      nonce: 'test_nonce',
      text: {
        loading: 'Loading...',
        confirm_remove: 'Are you sure?'
      }
    };

    // AJAX通信のモック
    global.fetch = jest.fn().mockImplementation(() =>
      Promise.resolve({
        ok: true,
        json: () => Promise.resolve({
          success: true,
          html: '<div class="rule">New Rule</div>'
        })
      })
    );
  });

  // テスト後のクリーンアップ
  afterEach(() => {
    document.body.innerHTML = '';
    jest.clearAllMocks();
    delete global.CFS;
    delete global.fetch;
  });

  // メインJSモジュールのインポートとテスト
  test('should initialize and integrate all modules', async () => {
    // メインモジュールをインポート
    const mainModule = await import('../main.js');

    // 初期化イベントの発火
    const initEvent = new Event('DOMContentLoaded');
    document.dispatchEvent(initEvent);

    // タブの初期化確認
    const firstTab = qs('.cfs-tab');
    expect(firstTab.classList.contains('active')).toBe(true);

    // 条件付きロジックの確認
    const toggleButton = qs('.toggle');
    toggleButton.click();

    const content = qs('.content');
    expect(content.classList.contains('hidden')).toBe(false);

    // ルール追加ボタンのテスト
    const addRuleButton = qs('.add-rule');
    addRuleButton.click();

    // Ajaxリクエストが行われたことを確認
    expect(global.fetch).toHaveBeenCalled();

    // ループ行追加のテスト（トリガーのみ）
    const addRowButton = qs('.cfs-add-row');
    expect(addRowButton).not.toBeNull();

    // グローバルAPI関数の公開確認
    expect(typeof window.CFS.fieldsFunctions).toBe('object');
    expect(typeof window.CFS.fieldsFunctions.checkConditionalLogic).toBe('function');
    expect(typeof window.CFS.fieldsFunctions.addRow).toBe('function');
    expect(typeof window.CFS.fieldsFunctions.removeRow).toBe('function');
  });
});
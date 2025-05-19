/**
 * ユーティリティモジュールユニットテスト
 *
 * @package CFS
 * @since 3.0.0
 */

import utils, {
  qs, qsa, addClass, removeClass, toggleClass,
  delegate, on, once, trigger,
  ajax, ajaxCFS,
  fadeIn, fadeOut, slideDown, slideUp,
  escapeHTML, generateId, deepMerge, $
} from '../utils';

// DOM操作ユーティリティのテスト
describe('DOM Utilities', () => {

  // テスト前にDOMを設定
  beforeEach(() => {
    document.body.innerHTML = `
      <div id="test-container">
        <div class="item item-1">Item 1</div>
        <div class="item item-2">Item 2</div>
        <div class="item item-3">Item 3</div>
      </div>
    `;
  });

  // テスト後にDOMをクリア
  afterEach(() => {
    document.body.innerHTML = '';
  });

  test('qs should return the first matching element', () => {
    const container = qs('#test-container');
    const firstItem = qs('.item');
    const specificItem = qs('.item-2');

    expect(container).not.toBeNull();
    expect(container.id).toBe('test-container');

    expect(firstItem).not.toBeNull();
    expect(firstItem.textContent).toBe('Item 1');

    expect(specificItem).not.toBeNull();
    expect(specificItem.textContent).toBe('Item 2');

    // 存在しない要素の場合はnullを返す
    const nonExistent = qs('.non-existent');
    expect(nonExistent).toBeNull();
  });

  test('qsa should return all matching elements as an array', () => {
    const items = qsa('.item');

    expect(Array.isArray(items)).toBe(true);
    expect(items.length).toBe(3);
    expect(items[0].textContent).toBe('Item 1');
    expect(items[1].textContent).toBe('Item 2');

    // コンテキスト内での検索
    const container = qs('#test-container');
    const itemsInContainer = qsa('.item', container);

    expect(itemsInContainer.length).toBe(3);

    // 存在しない要素の場合は空の配列を返す
    const nonExistent = qsa('.non-existent');
    expect(Array.isArray(nonExistent)).toBe(true);
    expect(nonExistent.length).toBe(0);
  });

  test('addClass should add class to an element', () => {
    const item = qs('.item-1');

    addClass(item, 'new-class');
    expect(item.classList.contains('new-class')).toBe(true);

    // すでに存在するクラスを追加してもエラーにならない
    addClass(item, 'new-class');
    expect(item.classList.contains('new-class')).toBe(true);

    // nullを渡してもエラーにならない
    expect(() => {
      addClass(null, 'some-class');
    }).not.toThrow();
  });

  test('removeClass should remove class from an element', () => {
    const item = qs('.item-1');

    // 初期状態で存在するクラスを削除
    removeClass(item, 'item-1');
    expect(item.classList.contains('item-1')).toBe(false);

    // 存在しないクラスを削除してもエラーにならない
    removeClass(item, 'non-existent-class');

    // nullを渡してもエラーにならない
    expect(() => {
      removeClass(null, 'some-class');
    }).not.toThrow();
  });

  test('toggleClass should toggle class on an element', () => {
    const item = qs('.item-1');

    // クラスをトグル（追加）
    toggleClass(item, 'toggled');
    expect(item.classList.contains('toggled')).toBe(true);

    // 同じクラスを再度トグル（削除）
    toggleClass(item, 'toggled');
    expect(item.classList.contains('toggled')).toBe(false);

    // forceパラメータでの制御
    toggleClass(item, 'forced', true);
    expect(item.classList.contains('forced')).toBe(true);

    toggleClass(item, 'forced', true); // 変化なし
    expect(item.classList.contains('forced')).toBe(true);

    toggleClass(item, 'forced', false);
    expect(item.classList.contains('forced')).toBe(false);

    // nullを渡してもエラーにならない
    expect(() => {
      toggleClass(null, 'some-class');
    }).not.toThrow();
  });
});

// イベントユーティリティのテスト
describe('Event Utilities', () => {

  // テスト前にDOMを設定
  beforeEach(() => {
    document.body.innerHTML = `
      <div id="delegate-container">
        <button class="delegate-button">Button 1</button>
        <button class="delegate-button">Button 2</button>
        <button class="other-button">Other Button</button>
      </div>
    `;
  });

  // テスト後にDOMをクリア
  afterEach(() => {
    document.body.innerHTML = '';
    jest.clearAllMocks();
  });

  test('delegate should handle events with delegation', () => {
    const container = qs('#delegate-container');
    const mockHandler = jest.fn();

    // イベント委譲を設定
    const removeListener = delegate(container, 'click', '.delegate-button', mockHandler);

    // 対象の要素をクリック
    const buttons = qsa('.delegate-button');
    buttons[0].click();

    // ハンドラが1回呼ばれたことを確認
    expect(mockHandler).toHaveBeenCalledTimes(1);

    // 異なるセレクタの要素をクリック
    const otherButton = qs('.other-button');
    otherButton.click();

    // ハンドラが呼ばれないことを確認
    expect(mockHandler).toHaveBeenCalledTimes(1);

    // リスナーを削除
    removeListener();

    // 再度クリックしてもハンドラが呼ばれないことを確認
    buttons[0].click();
    expect(mockHandler).toHaveBeenCalledTimes(1);
  });

  test('on should add multiple event handlers', () => {
    const button = qs('.delegate-button');
    const mockHandlers = {
      click: jest.fn(),
      mouseenter: jest.fn(),
      mouseleave: jest.fn()
    };

    // 複数のイベントハンドラを設定
    const removeListeners = on(button, mockHandlers);

    // 各イベントを発火
    button.click();
    expect(mockHandlers.click).toHaveBeenCalledTimes(1);

    button.dispatchEvent(new MouseEvent('mouseenter'));
    expect(mockHandlers.mouseenter).toHaveBeenCalledTimes(1);

    button.dispatchEvent(new MouseEvent('mouseleave'));
    expect(mockHandlers.mouseleave).toHaveBeenCalledTimes(1);

    // リスナーを削除
    removeListeners();

    // 再度イベントを発火してもハンドラが呼ばれないことを確認
    button.click();
    expect(mockHandlers.click).toHaveBeenCalledTimes(1);
  });

  test('once should execute handler only once', () => {
    const button = qs('.delegate-button');
    const mockHandler = jest.fn();

    // 一度だけ実行されるイベントリスナーを設定
    once(button, 'click', mockHandler);

    // 1回目のクリック
    button.click();
    expect(mockHandler).toHaveBeenCalledTimes(1);

    // 2回目のクリック（ハンドラは呼ばれない）
    button.click();
    expect(mockHandler).toHaveBeenCalledTimes(1);
  });

  test('trigger should fire custom events', () => {
    const button = qs('.delegate-button');
    const mockHandler = jest.fn();

    // カスタムイベントのリスナーを設定
    button.addEventListener('custom-event', mockHandler);

    // カスタムイベントを発火
    trigger(button, 'custom-event', { testData: 'value' });

    // ハンドラが呼ばれたことを確認
    expect(mockHandler).toHaveBeenCalledTimes(1);

    // カスタムデータが含まれていることを確認
    const event = mockHandler.mock.calls[0][0];
    expect(event.detail).toEqual({ testData: 'value' });
  });
});

// Ajaxユーティリティのテスト
describe('Ajax Utilities', () => {

  beforeEach(() => {
    // グローバルオブジェクトの設定
    global.CFS = {
      ajax_url: '/wp-admin/admin-ajax.php',
      nonce: 'test_nonce',
      text: {
        loading: 'Loading...'
      }
    };

    // fetchのモック
    global.fetch = jest.fn().mockImplementation(() =>
      Promise.resolve({
        ok: true,
        json: () => Promise.resolve({ success: true, data: 'test' })
      })
    );

    // コンソールエラーのモック
    console.error = jest.fn();
  });

  afterEach(() => {
    // グローバルオブジェクトのクリア
    delete global.CFS;
    global.fetch.mockClear();
    console.error.mockClear();
  });

  test('ajax should make a fetch request with FormData by default', async () => {
    const mockSuccessCallback = jest.fn();
    const mockErrorCallback = jest.fn();

    // ajaxリクエスト実行
    await ajax(
      { action: 'test_action', test_param: 'test_value' },
      mockSuccessCallback,
      mockErrorCallback
    );

    // fetchが呼ばれたことを確認
    expect(global.fetch).toHaveBeenCalledTimes(1);

    // 正しいURLで呼ばれたことを確認
    expect(global.fetch.mock.calls[0][0]).toBe('/wp-admin/admin-ajax.php');

    // オプションが正しいことを確認
    const options = global.fetch.mock.calls[0][1];
    expect(options.method).toBe('POST');
    expect(options.credentials).toBe('same-origin');

    // FormDataが使用されていることを確認
    expect(options.body instanceof FormData).toBe(true);

    // コールバックが呼ばれたことを確認
    expect(mockSuccessCallback).toHaveBeenCalledTimes(1);
    expect(mockSuccessCallback).toHaveBeenCalledWith({ success: true, data: 'test' });

    // エラーコールバックは呼ばれていないことを確認
    expect(mockErrorCallback).not.toHaveBeenCalled();
  });

  test('ajax should handle JSON requests', async () => {
    await ajax(
      { action: 'test_action', test_param: 'test_value' },
      null,
      null,
      { json: true, formData: false }
    );

    // JSONリクエストになっていることを確認
    const options = global.fetch.mock.calls[0][1];
    expect(options.headers['Content-Type']).toBe('application/json');
    expect(typeof options.body).toBe('string');

    // JSONデータが正しいことを確認
    const bodyData = JSON.parse(options.body);
    expect(bodyData.action).toBe('test_action');
    expect(bodyData.test_param).toBe('test_value');
    expect(bodyData._ajax_nonce).toBe('test_nonce');
  });

  test('ajax should handle errors', async () => {
    // fetchのモックをエラーにオーバーライド
    global.fetch.mockImplementationOnce(() =>
      Promise.reject(new Error('Network error'))
    );

    const mockSuccessCallback = jest.fn();
    const mockErrorCallback = jest.fn();

    // Promiseをキャッチするためにtry-catchを使用
    try {
      await ajax(
        { action: 'test_action' },
        mockSuccessCallback,
        mockErrorCallback
      );
    } catch (error) {
      // エラーは期待通り
    }

    // 成功コールバックは呼ばれていないことを確認
    expect(mockSuccessCallback).not.toHaveBeenCalled();

    // エラーコールバックが呼ばれたことを確認
    expect(mockErrorCallback).toHaveBeenCalledTimes(1);
    expect(mockErrorCallback.mock.calls[0][0]).toBeInstanceOf(Error);
    expect(mockErrorCallback.mock.calls[0][0].message).toBe('Network error');

    // コンソールエラーが出力されたことを確認
    expect(console.error).toHaveBeenCalledTimes(1);
  });

  test('ajaxCFS should use the CFS ajax handler', async () => {
    await ajaxCFS(
      'test_callback',
      { field_id: 123 }
    );

    // fetchが呼ばれたことを確認
    expect(global.fetch).toHaveBeenCalledTimes(1);

    // ボディデータを確認
    const options = global.fetch.mock.calls[0][1];
    const formData = options.body;

    // FormDataからエントリを抽出（ダーティだが効果的な方法）
    let formDataEntries = {};
    for (let [key, value] of formData.entries()) {
      formDataEntries[key] = value;
    }

    // 正しいデータが含まれていることを確認
    expect(formDataEntries.action).toBe('cfs_ajax_handler');
    expect(formDataEntries.cfs_ajax_callback).toBe('test_callback');
    expect(formDataEntries.field_id).toBe('123');
    expect(formDataEntries._ajax_nonce).toBe('test_nonce');
  });
});

// アニメーションユーティリティのテスト
describe('Animation Utilities', () => {

  beforeEach(() => {
    document.body.innerHTML = `
      <div id="animation-test" style="display:none;">Test Element</div>
    `;

    // requestAnimationFrameのモック
    window.requestAnimationFrame = jest.fn(callback => {
      setTimeout(() => callback(Date.now()), 10);
      return Math.floor(Math.random() * 1000);
    });
  });

  afterEach(() => {
    document.body.innerHTML = '';
    jest.clearAllMocks();
  });

  test('fadeIn should make an element visible with opacity transition', done => {
    const element = qs('#animation-test');

    // コールバック関数
    const mockCallback = jest.fn(() => {
      // アニメーション完了後にチェック
      expect(element.style.display).not.toBe('none');
      expect(element.style.opacity).toBe('');
      done();
    });

    // fadeIn実行
    fadeIn(element, 50, mockCallback);

    // 直後のチェック
    expect(element.style.display).not.toBe('none');
    expect(element.style.opacity).toBe('0');

    // requestAnimationFrameが呼ばれたことを確認
    expect(window.requestAnimationFrame).toHaveBeenCalled();
  });

  test('fadeOut should hide an element with opacity transition', done => {
    const element = qs('#animation-test');
    element.style.display = 'block';

    // コールバック関数
    const mockCallback = jest.fn(() => {
      // アニメーション完了後にチェック
      expect(element.style.display).toBe('none');
      done();
    });

    // fadeOut実行
    fadeOut(element, 50, mockCallback);

    // requestAnimationFrameが呼ばれたことを確認
    expect(window.requestAnimationFrame).toHaveBeenCalled();
  });

  test('slideDown should expand an element vertically', done => {
    const element = qs('#animation-test');

    // コールバック関数
    const mockCallback = jest.fn(() => {
      // アニメーション完了後にチェック
      expect(element.style.display).not.toBe('none');
      expect(element.style.overflow).toBe('');
      expect(element.style.height).toBe('');
      done();
    });

    // slideDown実行
    slideDown(element, 50, mockCallback);

    // requestAnimationFrameが呼ばれたことを確認
    expect(window.requestAnimationFrame).toHaveBeenCalled();
  });

  test('slideUp should collapse an element vertically', done => {
    const element = qs('#animation-test');
    element.style.display = 'block';

    // コールバック関数
    const mockCallback = jest.fn(() => {
      // アニメーション完了後にチェック
      expect(element.style.display).toBe('none');
      expect(element.style.overflow).toBe('');
      expect(element.style.height).toBe('');
      done();
    });

    // slideUp実行
    slideUp(element, 50, mockCallback);

    // requestAnimationFrameが呼ばれたことを確認
    expect(window.requestAnimationFrame).toHaveBeenCalled();
  });
});

// その他のユーティリティのテスト
describe('Other Utilities', () => {

  test('escapeHTML should escape HTML special characters', () => {
    const input = '<script>alert("XSS & more");</script>';
    const expected = '&lt;script&gt;alert(&quot;XSS &amp; more&quot;);&lt;/script&gt;';

    expect(escapeHTML(input)).toBe(expected);

    // エッジケース
    expect(escapeHTML('')).toBe('');
    expect(escapeHTML(null)).toBe('');
    expect(escapeHTML(undefined)).toBe('');
  });

  test('generateId should create a unique ID with prefix', () => {
    const id1 = generateId();
    const id2 = generateId();

    // 生成されたIDが文字列であることを確認
    expect(typeof id1).toBe('string');

    // デフォルトプレフィックスが使用されていることを確認
    expect(id1.startsWith('cfs-')).toBe(true);

    // 異なるIDが生成されることを確認
    expect(id1).not.toBe(id2);

    // カスタムプレフィックスが使用できることを確認
    const customId = generateId('custom-');
    expect(customId.startsWith('custom-')).toBe(true);
  });

  test('deepMerge should merge objects deeply', () => {
    const obj1 = {
      a: 1,
      b: { c: 2, d: 3 },
      e: [1, 2, 3]
    };

    const obj2 = {
      b: { d: 4, f: 5 },
      g: 6
    };

    const expected = {
      a: 1,
      b: { c: 2, d: 4, f: 5 },
      e: [1, 2, 3],
      g: 6
    };

    const result = deepMerge({}, obj1, obj2);
    expect(result).toEqual(expected);

    // 元のオブジェクトは変更されていないことを確認
    expect(obj1.b.d).toBe(3);

    // エッジケース
    expect(deepMerge({}, obj1)).toEqual(obj1);
    expect(deepMerge({}, undefined, obj2)).toEqual(obj2);
  });

  test('$ should provide jQuery-like functionality', () => {
    document.body.innerHTML = `
      <div id="jquery-test">
        <div class="item">Item 1</div>
        <div class="item">Item 2</div>
      </div>
    `;

    // セレクタでの要素選択
    const $items = $('.item');
    expect($items.elements.length).toBe(2);

    // 単一要素の取得
    const item = $items.get(0);
    expect(item.textContent).toBe('Item 1');

    // クラス操作のテスト
    $items.addClass('new-class');
    expect(item.classList.contains('new-class')).toBe(true);

    $items.removeClass('new-class');
    expect(item.classList.contains('new-class')).toBe(false);

    // クラス切り替えのテスト
    $items.toggleClass('toggled');
    expect(item.classList.contains('toggled')).toBe(true);

    // HTML設定のテスト
    $items.html('<span>Updated</span>');
    expect(item.innerHTML).toBe('<span>Updated</span>');

    // HTML取得のテスト
    expect($('#jquery-test').html()).toContain('<span>Updated</span>');

    // テキスト設定のテスト
    $items.text('New Text');
    expect(item.textContent).toBe('New Text');

    // テキスト取得のテスト
    expect($items.text()).toBe('New Text');

    // 各要素に対する処理
    const mockFn = jest.fn();
    $items.each(mockFn);
    expect(mockFn).toHaveBeenCalledTimes(2);

    // メソッドチェーンのテスト
    $('.item')
    .addClass('chained')
    .html('Chained')
    .attr('data-test', 'value');

    expect(item.classList.contains('chained')).toBe(true);
    expect(item.innerHTML).toBe('Chained');
    expect(item.getAttribute('data-test')).toBe('value');
  });
});
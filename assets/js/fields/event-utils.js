/**
 * Event Utilities
 * イベント処理に関するユーティリティ関数
 *
 * @package CFS
 * @since 3.0.0
 */

/**
 * イベント委譲 - 親要素にイベントリスナーを設定し、特定の子要素のイベントを処理する
 *
 * @param {Element|Document} el - イベントリスナーを設定する親要素
 * @param {string} eventType - イベントタイプ（例：'click', 'change'）
 * @param {string} selector - 対象となる子要素を特定するCSSセレクタ
 * @param {Function} handler - イベントハンドラ関数。(event, matchedElement) が引数として渡される
 * @return {Function} リスナー解除用の関数
 */
export const delegate = (el, eventType, selector, handler) => {
  if (!el) return () => {};

  const listener = (event) => {
    // イベント対象の要素からセレクタに一致する最も近い祖先要素を検索
    const target = event.target.closest(selector);

    // セレクタに一致する要素が見つかり、かつそれが親要素の子孫である場合
    if (target && el.contains(target)) {
      // ハンドラを実行（context を target に設定）
      handler.call(target, event, target);
    }
  };

  // イベントリスナーを登録
  el.addEventListener(eventType, listener);

  // リスナー解除用の関数を返す
  return () => {
    el.removeEventListener(eventType, listener);
  };
};

/**
 * 要素に複数のイベントリスナーを一度に登録する
 *
 * @param {Element} el - イベントリスナーを設定する要素
 * @param {Object} events - イベントとハンドラのマップ。例: { 'click': () => {}, 'change': () => {} }
 * @return {Function} 全リスナーを解除する関数
 */
export const on = (el, events) => {
  if (!el || !events) return () => {};

  const handlers = [];

  Object.entries(events).forEach(([eventType, handler]) => {
    el.addEventListener(eventType, handler);
    handlers.push([eventType, handler]);
  });

  return () => {
    handlers.forEach(([eventType, handler]) => {
      el.removeEventListener(eventType, handler);
    });
  };
};

/**
 * 一度だけ実行されるイベントリスナーを登録する
 *
 * @param {Element} el - イベントリスナーを設定する要素
 * @param {string} eventType - イベントタイプ
 * @param {Function} handler - イベントハンドラ
 */
export const once = (el, eventType, handler) => {
  if (!el) return;

  const onceHandler = (event) => {
    // リスナーを削除
    el.removeEventListener(eventType, onceHandler);
    // 元のハンドラを実行
    handler.call(el, event);
  };

  el.addEventListener(eventType, onceHandler);
};

/**
 * イベントを発火する
 *
 * @param {Element} el - イベントを発火する要素
 * @param {string} eventType - イベントタイプ
 * @param {Object} [eventData={}] - カスタムイベントデータ
 * @param {Object} [options={}] - CustomEventのオプション
 */
export const trigger = (el, eventType, eventData = {}, options = {}) => {
  if (!el) return;

  const event = new CustomEvent(eventType, {
    bubbles: true,
    cancelable: true,
    detail: eventData,
    ...options
  });

  el.dispatchEvent(event);
};

/**
 * DOMContentLoadedイベントの一般的なラッパー
 *
 * @param {Function} callback - DOMが読み込まれた後に実行するコールバック
 */
export const ready = (callback) => {
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', callback);
  } else {
    callback();
  }
};

/**
 * クリックイベントの拡張ハンドラ - 右クリックと修飾キーを区別
 *
 * @param {Function} handler - クリックハンドラ
 * @param {Object} [options={}] - オプション
 * @param {boolean} [options.preventContext=true] - 右クリックメニューを抑制するか
 * @return {Function} 拡張されたイベントハンドラ
 */
export const onClick = (handler, options = { preventContext: true }) => {
  return (event) => {
    // 右クリック
    if (event.button === 2) {
      if (options.preventContext) {
        event.preventDefault();
      }
      return;
    }

    // 修飾キーが押されている場合のハンドリング
    if (event.ctrlKey || event.metaKey || event.shiftKey) {
      // デフォルトでは何もしない（例：Ctrlキーを押しながらのクリックは新しいタブで開く動作を妨げない）
      return;
    }

    // 通常のクリックイベント
    handler(event);
  };
};

/**
 * 実行をスロットルする（連続呼び出しを制限する）
 *
 * @param {Function} fn - 実行する関数
 * @param {number} delay - 実行間隔（ミリ秒）
 * @return {Function} スロットルされた関数
 */
export const throttle = (fn, delay) => {
  let lastCall = 0;
  return (...args) => {
    const now = Date.now();
    if (now - lastCall >= delay) {
      lastCall = now;
      return fn(...args);
    }
  };
};

/**
 * 実行をデバウンスする（連続呼び出しを一つにまとめる）
 *
 * @param {Function} fn - 実行する関数
 * @param {number} delay - 待機時間（ミリ秒）
 * @param {boolean} [immediate=false] - 最初の呼び出しで即時実行するか
 * @return {Function} デバウンスされた関数
 */
export const debounce = (fn, delay, immediate = false) => {
  let timeout;
  return (...args) => {
    const callNow = immediate && !timeout;

    clearTimeout(timeout);

    timeout = setTimeout(() => {
      timeout = null;
      if (!immediate) fn(...args);
    }, delay);

    if (callNow) fn(...args);
  };
};

export default {
  delegate,
  on,
  once,
  trigger,
  ready,
  onClick,
  throttle,
  debounce
};
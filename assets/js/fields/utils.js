/**
 * Custom Field Suite - Utils
 * メインユーティリティモジュール
 *
 * 機能別のユーティリティモジュールをまとめて提供します
 *
 * @package CFS
 * @since 3.0.0
 */

// 各ユーティリティモジュールをインポート
import domUtils from './dom-utils.js';
import eventUtils from './event-utils.js';
import ajaxUtils from './ajax-utils.js';
import animationUtils from './animation-utils.js';

/**
 * ユーティリティモジュールを集約したオブジェクト
 */
const utils = {
  // DOM操作ユーティリティ
  ...domUtils,

  // イベント処理ユーティリティ
  ...eventUtils,

  // AJAX通信ユーティリティ
  ...ajaxUtils,

  // アニメーションユーティリティ
  ...animationUtils,

  /**
   * 文字列をHTMLエスケープする
   *
   * @param {string} str - エスケープする文字列
   * @return {string} エスケープされた文字列
   */
  escapeHTML: (str) => {
    if (!str || typeof str !== 'string') return '';

    return str
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
  },

  /**
   * ランダムなIDを生成する
   *
   * @param {string} [prefix='cfs-'] - IDのプレフィックス
   * @return {string} 生成されたID
   */
  generateId: (prefix = 'cfs-') => {
    return prefix + Math.random().toString(36).substring(2, 9);
  },

  /**
   * オブジェクトを深くマージする
   *
   * @param {Object} target - ターゲットオブジェクト
   * @param {...Object} sources - マージするソースオブジェクト
   * @return {Object} マージされたオブジェクト
   */
  deepMerge: (target, ...sources) => {
    if (!sources.length) return target;

    const source = sources.shift();

    if (source === undefined) return target;

    if (isObject(target) && isObject(source)) {
      Object.keys(source).forEach(key => {
        if (isObject(source[key])) {
          if (!target[key]) Object.assign(target, { [key]: {} });
          utils.deepMerge(target[key], source[key]);
        } else {
          Object.assign(target, { [key]: source[key] });
        }
      });
    }

    return utils.deepMerge(target, ...sources);

    // オブジェクト判定ヘルパー関数
    function isObject(item) {
      return (item && typeof item === 'object' && !Array.isArray(item));
    }
  },

  /**
   * 指定された時間だけ待機する
   *
   * @param {number} ms - 待機時間（ミリ秒）
   * @return {Promise} 待機後に解決するPromise
   */
  sleep: (ms) => {
    return new Promise(resolve => setTimeout(resolve, ms));
  },

  /**
   * メソッドチェーンを提供するDOM操作ラッパー
   *
   * @param {string|Element} selector - セレクタ文字列またはDOM要素
   * @param {Element|Document} [context=document] - コンテキスト要素
   * @return {Object} メソッドチェーン可能なオブジェクト
   */
  $: (selector, context = document) => {
    let elements = [];

    if (typeof selector === 'string') {
      elements = Array.from(context.querySelectorAll(selector));
    } else if (selector instanceof Element) {
      elements = [selector];
    } else if (selector instanceof NodeList || selector instanceof HTMLCollection) {
      elements = Array.from(selector);
    } else if (Array.isArray(selector)) {
      elements = selector.filter(el => el instanceof Element);
    }

    // メソッドチェーンオブジェクト
    const chainObj = {
      // 要素コレクション
      elements,

      // 指定インデックスの要素を取得
      get: (index = 0) => elements[index] || null,

      // 各要素に対して関数を実行
      each: (fn) => {
        elements.forEach((el, i) => fn.call(el, el, i));
        return chainObj;
      },

      // クラスを追加
      addClass: (className) => {
        elements.forEach(el => domUtils.addClass(el, className));
        return chainObj;
      },

      // クラスを削除
      removeClass: (className) => {
        elements.forEach(el => domUtils.removeClass(el, className));
        return chainObj;
      },

      // クラスをトグル
      toggleClass: (className, force) => {
        elements.forEach(el => domUtils.toggleClass(el, className, force));
        return chainObj;
      },

      // 属性を設定
      attr: (name, value) => {
        if (value === undefined) {
          return elements.length ? domUtils.getAttr(elements[0], name) : null;
        }
        elements.forEach(el => domUtils.setAttr(el, name, value));
        return chainObj;
      },

      // 非表示
      hide: () => {
        elements.forEach(el => domUtils.hide(el));
        return chainObj;
      },

      // 表示
      show: (display) => {
        elements.forEach(el => domUtils.show(el, display));
        return chainObj;
      },

      // フェードイン
      fadeIn: (duration, callback, display) => {
        const promises = elements.map(el =>
          new Promise(resolve => animationUtils.fadeIn(el, duration, resolve, display))
        );

        Promise.all(promises).then(() => {
          if (callback && typeof callback === 'function') {
            callback();
          }
        });

        return chainObj;
      },

      // フェードアウト
      fadeOut: (duration, callback) => {
        const promises = elements.map(el =>
          new Promise(resolve => animationUtils.fadeOut(el, duration, resolve))
        );

        Promise.all(promises).then(() => {
          if (callback && typeof callback === 'function') {
            callback();
          }
        });

        return chainObj;
      },

      // イベントリスナーを追加
      on: (eventType, handler) => {
        elements.forEach(el => el.addEventListener(eventType, handler));
        return chainObj;
      },

      // イベント委譲
      delegate: (eventType, selector, handler) => {
        elements.forEach(el => eventUtils.delegate(el, eventType, selector, handler));
        return chainObj;
      },

      // 内部HTMLを設定/取得
      html: (content) => {
        if (content === undefined) {
          return elements.length ? elements[0].innerHTML : '';
        }
        elements.forEach(el => domUtils.setHTML(el, content));
        return chainObj;
      },

      // テキストコンテンツを設定/取得
      text: (content) => {
        if (content === undefined) {
          return elements.length ? elements[0].textContent : '';
        }
        elements.forEach(el => { el.textContent = content; });
        return chainObj;
      },

      // フォーム値を設定/取得
      val: (value) => {
        if (value === undefined) {
          return elements.length ? elements[0].value : '';
        }
        elements.forEach(el => { if ('value' in el) el.value = value; });
        return chainObj;
      }
    };

    return chainObj;
  }
};

// 名前付きエクスポート
export const {
  // DOM Utils
  qs, qsa, createEl, addClass, removeClass, toggleClass,
  setAttr, getAttr, removeAttr, hide, show, isVisible,
  append, insertBefore, insertAfter, empty, remove, createFromHTML, setHTML,

  // Event Utils
  delegate, on, once, trigger, ready, onClick, throttle, debounce,

  // Ajax Utils
  ajax, ajaxAction, ajaxCFS, ajaxWithLoading, wpAPI,

  // Animation Utils
  fadeIn, fadeOut, slideDown, slideUp, slideToggle, fadeToggle, animate,

  // Other Utils
  escapeHTML, generateId, deepMerge, sleep, $
} = utils;

// デフォルトエクスポート
export default utils;
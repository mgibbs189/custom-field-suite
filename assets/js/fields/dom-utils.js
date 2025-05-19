/**
 * DOM Utilities
 * DOM操作に関するユーティリティ関数
 *
 * @package CFS
 * @since 3.0.0
 */

/**
 * 要素を選択するためのクエリセレクタ（単一要素）
 * @param {string} selector - CSS セレクタ
 * @param {Element|Document} [context=document] - 検索対象の親要素
 * @return {Element|null} 見つかった要素、または null
 */
export const qs = (selector, context = document) => context.querySelector(selector);

/**
 * 要素を選択するためのクエリセレクタ（複数要素）
 * @param {string} selector - CSS セレクタ
 * @param {Element|Document} [context=document] - 検索対象の親要素
 * @return {Element[]} 見つかった要素の配列
 */
export const qsa = (selector, context = document) => Array.from(context.querySelectorAll(selector));

/**
 * 要素を作成する
 * @param {string} tagName - 作成する要素のタグ名
 * @param {Object} [attributes={}] - 設定する属性
 * @param {string} [innerHTML=''] - 要素内部のHTML
 * @return {Element} 作成された要素
 */
export const createEl = (tagName, attributes = {}, innerHTML = '') => {
  const el = document.createElement(tagName);

  // 属性を設定
  Object.entries(attributes).forEach(([key, value]) => {
    if (key === 'class' || key === 'className') {
      el.className = value;
    } else if (key === 'style' && typeof value === 'object') {
      Object.entries(value).forEach(([prop, val]) => {
        el.style[prop] = val;
      });
    } else {
      el.setAttribute(key, value);
    }
  });

  // 中身を設定
  if (innerHTML) {
    el.innerHTML = innerHTML;
  }

  return el;
};

/**
 * クラスを追加する
 * @param {Element} el - 対象要素
 * @param {string} className - 追加するクラス名
 */
export const addClass = (el, className) => {
  if (!el) return;
  el.classList.add(className);
};

/**
 * クラスを削除する
 * @param {Element} el - 対象要素
 * @param {string} className - 削除するクラス名
 */
export const removeClass = (el, className) => {
  if (!el) return;
  el.classList.remove(className);
};

/**
 * クラスを切り替える
 * @param {Element} el - 対象要素
 * @param {string} className - 切り替えるクラス名
 * @param {boolean} [force] - 強制的に追加/削除する場合の真偽値
 */
export const toggleClass = (el, className, force) => {
  if (!el) return;
  if (typeof force !== 'undefined') {
    el.classList.toggle(className, force);
  } else {
    el.classList.toggle(className);
  }
};

/**
 * 要素に属性を設定する
 * @param {Element} el - 対象要素
 * @param {string} name - 属性名
 * @param {string} value - 属性値
 */
export const setAttr = (el, name, value) => {
  if (!el) return;
  el.setAttribute(name, value);
};

/**
 * 要素から属性を取得する
 * @param {Element} el - 対象要素
 * @param {string} name - 属性名
 * @return {string|null} 属性値、または null
 */
export const getAttr = (el, name) => {
  if (!el) return null;
  return el.getAttribute(name);
};

/**
 * 要素から属性を削除する
 * @param {Element} el - 対象要素
 * @param {string} name - 属性名
 */
export const removeAttr = (el, name) => {
  if (!el) return;
  el.removeAttribute(name);
};

/**
 * 要素を非表示にする
 * @param {Element} el - 対象要素
 */
export const hide = (el) => {
  if (!el) return;
  el.style.display = 'none';
};

/**
 * 要素を表示する
 * @param {Element} el - 対象要素
 * @param {string} [display=''] - 表示形式
 */
export const show = (el, display = '') => {
  if (!el) return;
  el.style.display = display;
};

/**
 * 要素が表示されているかチェックする
 * @param {Element} el - 対象要素
 * @return {boolean} 表示されていれば true
 */
export const isVisible = (el) => {
  if (!el) return false;
  return el.style.display !== 'none';
};

/**
 * 親要素内で要素を追加する
 * @param {Element} parent - 親要素
 * @param {Element} child - 追加する子要素
 */
export const append = (parent, child) => {
  if (!parent || !child) return;
  parent.appendChild(child);
};

/**
 * 要素の前に別の要素を挿入する
 * @param {Element} el - 基準要素
 * @param {Element} newEl - 新しい要素
 */
export const insertBefore = (el, newEl) => {
  if (!el || !newEl || !el.parentNode) return;
  el.parentNode.insertBefore(newEl, el);
};

/**
 * 要素の後に別の要素を挿入する
 * @param {Element} el - 基準要素
 * @param {Element} newEl - 新しい要素
 */
export const insertAfter = (el, newEl) => {
  if (!el || !newEl || !el.parentNode) return;
  if (el.nextSibling) {
    el.parentNode.insertBefore(newEl, el.nextSibling);
  } else {
    el.parentNode.appendChild(newEl);
  }
};

/**
 * 要素を空にする
 * @param {Element} el - 対象要素
 */
export const empty = (el) => {
  if (!el) return;
  while (el.firstChild) {
    el.removeChild(el.firstChild);
  }
};

/**
 * 要素を削除する
 * @param {Element} el - 削除する要素
 */
export const remove = (el) => {
  if (!el || !el.parentNode) return;
  el.parentNode.removeChild(el);
};

/**
 * HTML文字列を要素に変換する
 * @param {string} html - HTML文字列
 * @return {DocumentFragment} 作成された要素を含むフラグメント
 */
export const createFromHTML = (html) => {
  const template = document.createElement('template');
  template.innerHTML = html.trim();
  return template.content;
};

/**
 * 要素の中身をHTML文字列で設定する
 * @param {Element} el - 対象要素
 * @param {string} html - HTML文字列
 */
export const setHTML = (el, html) => {
  if (!el) return;
  el.innerHTML = html;
};

export default {
  qs,
  qsa,
  createEl,
  addClass,
  removeClass,
  toggleClass,
  setAttr,
  getAttr,
  removeAttr,
  hide,
  show,
  isVisible,
  append,
  insertBefore,
  insertAfter,
  empty,
  remove,
  createFromHTML,
  setHTML
};
/**
 * Animation Utilities
 * アニメーション処理に関するユーティリティ関数
 *
 * @package CFS
 * @since 3.0.0
 */

/**
 * 要素をフェードイン表示する
 *
 * @param {Element} el - フェードインする要素
 * @param {number} [duration=400] - アニメーション時間（ミリ秒）
 * @param {Function} [callback] - アニメーション完了後のコールバック
 * @param {string} [display=''] - 表示形式
 */
export const fadeIn = (el, duration = 400, callback, display = '') => {
  if (!el) return;

  // すでに表示中なら何もしない
  if (el.style.display !== 'none' &&
    el.style.opacity !== '0' &&
    getComputedStyle(el).display !== 'none') {
    if (callback && typeof callback === 'function') {
      callback();
    }
    return;
  }

  // 表示スタイルをセット
  el.style.opacity = '0';
  el.style.display = display || '';

  let start = null;

  // アニメーションステップ
  const step = (timestamp) => {
    if (!start) start = timestamp;
    const progress = timestamp - start;

    // 進行度に応じて不透明度を設定
    const opacity = Math.min(progress / duration, 1);
    el.style.opacity = opacity.toString();

    // アニメーション継続判定
    if (progress < duration) {
      window.requestAnimationFrame(step);
    } else {
      // アニメーション完了
      el.style.opacity = '';
      if (callback && typeof callback === 'function') {
        callback();
      }
    }
  };

  // アニメーション開始
  window.requestAnimationFrame(step);
};

/**
 * 要素をフェードアウト非表示にする
 *
 * @param {Element} el - フェードアウトする要素
 * @param {number} [duration=400] - アニメーション時間（ミリ秒）
 * @param {Function} [callback] - アニメーション完了後のコールバック
 */
export const fadeOut = (el, duration = 400, callback) => {
  if (!el) return;

  // すでに非表示なら何もしない
  if (el.style.display === 'none' || getComputedStyle(el).display === 'none') {
    if (callback && typeof callback === 'function') {
      callback();
    }
    return;
  }

  let start = null;
  const initialOpacity = parseFloat(getComputedStyle(el).opacity) || 1;

  // アニメーションステップ
  const step = (timestamp) => {
    if (!start) start = timestamp;
    const progress = timestamp - start;

    // 進行度に応じて不透明度を設定
    const remainingOpacity = initialOpacity * (1 - Math.min(progress / duration, 1));
    el.style.opacity = remainingOpacity.toString();

    // アニメーション継続判定
    if (progress < duration) {
      window.requestAnimationFrame(step);
    } else {
      // アニメーション完了
      el.style.opacity = '';
      el.style.display = 'none';
      if (callback && typeof callback === 'function') {
        callback();
      }
    }
  };

  // アニメーション開始
  window.requestAnimationFrame(step);
};

/**
 * 要素をスライドダウン表示する
 *
 * @param {Element} el - スライドダウンする要素
 * @param {number} [duration=400] - アニメーション時間（ミリ秒）
 * @param {Function} [callback] - アニメーション完了後のコールバック
 * @param {string} [display='block'] - 表示形式
 */
export const slideDown = (el, duration = 400, callback, display = 'block') => {
  if (!el) return;

  // すでに表示中なら何もしない
  if (el.style.display !== 'none' && getComputedStyle(el).display !== 'none') {
    if (callback && typeof callback === 'function') {
      callback();
    }
    return;
  }

  // 現在のスタイルを保存
  const paddingTop = getComputedStyle(el).paddingTop;
  const paddingBottom = getComputedStyle(el).paddingBottom;
  const borderTopWidth = getComputedStyle(el).borderTopWidth;
  const borderBottomWidth = getComputedStyle(el).borderBottomWidth;

  // 一時的に表示してサイズを計測
  el.style.display = display;
  el.style.visibility = 'hidden';
  el.style.overflow = 'hidden';
  el.style.height = 'auto';
  el.style.paddingTop = '0';
  el.style.paddingBottom = '0';
  el.style.borderTopWidth = '0';
  el.style.borderBottomWidth = '0';

  const targetHeight = el.offsetHeight;

  // 初期状態に戻す
  el.style.height = '0';
  el.style.visibility = '';

  let start = null;

  // アニメーションステップ
  const step = (timestamp) => {
    if (!start) start = timestamp;
    const progress = timestamp - start;
    const percentage = Math.min(progress / duration, 1);

    // 進行度に応じてサイズを設定
    el.style.height = (targetHeight * percentage) + 'px';

    if (percentage >= 0.5) {
      el.style.paddingTop = paddingTop;
      el.style.paddingBottom = paddingBottom;
      el.style.borderTopWidth = borderTopWidth;
      el.style.borderBottomWidth = borderBottomWidth;
    }

    // アニメーション継続判定
    if (progress < duration) {
      window.requestAnimationFrame(step);
    } else {
      // アニメーション完了
      el.style.height = '';
      el.style.overflow = '';

      if (callback && typeof callback === 'function') {
        callback();
      }
    }
  };

  // アニメーション開始
  window.requestAnimationFrame(step);
};

/**
 * 要素をスライドアップして非表示にする
 *
 * @param {Element} el - スライドアップする要素
 * @param {number} [duration=400] - アニメーション時間（ミリ秒）
 * @param {Function} [callback] - アニメーション完了後のコールバック
 */
export const slideUp = (el, duration = 400, callback) => {
  if (!el) return;

  // すでに非表示なら何もしない
  if (el.style.display === 'none' || getComputedStyle(el).display === 'none') {
    if (callback && typeof callback === 'function') {
      callback();
    }
    return;
  }

  // 現在のスタイルを保存
  const paddingTop = getComputedStyle(el).paddingTop;
  const paddingBottom = getComputedStyle(el).paddingBottom;
  const borderTopWidth = getComputedStyle(el).borderTopWidth;
  const borderBottomWidth = getComputedStyle(el).borderBottomWidth;
  const height = el.offsetHeight;

  // アニメーション準備
  el.style.overflow = 'hidden';
  el.style.height = height + 'px';

  let start = null;

  // アニメーションステップ
  const step = (timestamp) => {
    if (!start) start = timestamp;
    const progress = timestamp - start;
    const percentage = 1 - Math.min(progress / duration, 1);

    // 進行度に応じてサイズを設定
    el.style.height = (height * percentage) + 'px';

    if (percentage <= 0.5) {
      el.style.paddingTop = '0';
      el.style.paddingBottom = '0';
      el.style.borderTopWidth = '0';
      el.style.borderBottomWidth = '0';
    }

    // アニメーション継続判定
    if (progress < duration) {
      window.requestAnimationFrame(step);
    } else {
      // アニメーション完了
      el.style.display = 'none';
      el.style.height = '';
      el.style.overflow = '';
      el.style.paddingTop = paddingTop;
      el.style.paddingBottom = paddingBottom;
      el.style.borderTopWidth = borderTopWidth;
      el.style.borderBottomWidth = borderBottomWidth;

      if (callback && typeof callback === 'function') {
        callback();
      }
    }
  };

  // アニメーション開始
  window.requestAnimationFrame(step);
};

/**
 * 要素をスライドトグルする（表示・非表示を切り替える）
 *
 * @param {Element} el - トグルする要素
 * @param {number} [duration=400] - アニメーション時間（ミリ秒）
 * @param {Function} [callback] - アニメーション完了後のコールバック
 * @param {string} [display='block'] - 表示形式
 */
export const slideToggle = (el, duration = 400, callback, display = 'block') => {
  if (!el) return;

  if (el.style.display === 'none' || getComputedStyle(el).display === 'none') {
    slideDown(el, duration, callback, display);
  } else {
    slideUp(el, duration, callback);
  }
};

/**
 * 要素をフェードトグルする（表示・非表示を切り替える）
 *
 * @param {Element} el - トグルする要素
 * @param {number} [duration=400] - アニメーション時間（ミリ秒）
 * @param {Function} [callback] - アニメーション完了後のコールバック
 * @param {string} [display=''] - 表示形式
 */
export const fadeToggle = (el, duration = 400, callback, display = '') => {
  if (!el) return;

  if (el.style.display === 'none' || getComputedStyle(el).display === 'none') {
    fadeIn(el, duration, callback, display);
  } else {
    fadeOut(el, duration, callback);
  }
};

/**
 * 要素をアニメーションで移動する
 *
 * @param {Element} el - アニメーションする要素
 * @param {Object} properties - アニメーションするプロパティと最終値
 * @param {number} [duration=400] - アニメーション時間（ミリ秒）
 * @param {Function} [callback] - アニメーション完了後のコールバック
 * @param {string} [easing='linear'] - イージング関数
 */
export const animate = (el, properties, duration = 400, callback, easing = 'linear') => {
  if (!el) return;

  // 初期値を取得
  const initialValues = {};
  const units = {};

  // プロパティごとに初期値と単位を設定
  Object.keys(properties).forEach(prop => {
    const current = getComputedStyle(el)[prop];
    initialValues[prop] = parseFloat(current) || 0;

    // 単位を特定（px、%など）
    const match = current.match(/[a-z%]+$/i);
    units[prop] = match ? match[0] : 'px';
  });

  let start = null;

  // イージング関数
  const easingFunctions = {
    linear: t => t,
    easeInQuad: t => t * t,
    easeOutQuad: t => t * (2 - t),
    easeInOutQuad: t => t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t
  };

  const easingFn = easingFunctions[easing] || easingFunctions.linear;

  // アニメーションステップ
  const step = (timestamp) => {
    if (!start) start = timestamp;
    const progress = Math.min((timestamp - start) / duration, 1);
    const easedProgress = easingFn(progress);

    // 各プロパティをアニメーション
    Object.keys(properties).forEach(prop => {
      const initial = initialValues[prop];
      const target = parseFloat(properties[prop]);
      const current = initial + (target - initial) * easedProgress;

      el.style[prop] = current + units[prop];
    });

    // アニメーション継続判定
    if (progress < 1) {
      window.requestAnimationFrame(step);
    } else {
      // アニメーション完了
      if (callback && typeof callback === 'function') {
        callback();
      }
    }
  };

  // アニメーション開始
  window.requestAnimationFrame(step);
};

export default {
  fadeIn,
  fadeOut,
  slideDown,
  slideUp,
  slideToggle,
  fadeToggle,
  animate
};
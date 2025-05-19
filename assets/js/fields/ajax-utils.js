/**
 * AJAX Utilities
 * AJAX通信に関するユーティリティ関数
 *
 * @package CFS
 * @since 3.0.0
 */

/**
 * WordPressのajax_urlにデータを送信する
 *
 * @param {Object} data - 送信するデータ
 * @param {Function} [successCallback] - 成功時のコールバック関数
 * @param {Function} [errorCallback] - エラー時のコールバック関数
 * @param {Object} [options={}] - オプション設定
 * @param {boolean} [options.json=false] - JSONとして送信するか
 * @param {boolean} [options.formData=true] - FormDataとして送信するか
 * @return {Promise} 通信結果を返すPromise
 */
export const ajax = (data, successCallback, errorCallback, options = {}) => {
  // デフォルトオプション
  const defaultOptions = {
    json: false,
    formData: true
  };

  // オプションのマージ
  const opts = { ...defaultOptions, ...options };

  // nonce（CSRFトークン）を追加
  if (typeof CFS !== 'undefined' && CFS.nonce) {
    data._ajax_nonce = CFS.nonce;
  }

  // リクエストオプション
  const fetchOptions = {
    method: 'POST',
    credentials: 'same-origin',
    headers: {}
  };

  // リクエストボディの準備
  if (opts.json) {
    // JSONとしてエンコード
    fetchOptions.body = JSON.stringify(data);
    fetchOptions.headers['Content-Type'] = 'application/json';
  } else if (opts.formData) {
    // FormDataとして送信
    const formData = new FormData();
    Object.entries(data).forEach(([key, value]) => {
      formData.append(key, value);
    });
    fetchOptions.body = formData;
  } else {
    // application/x-www-form-urlencoded として送信
    const urlEncodedData = Object.entries(data)
    .map(([key, value]) => {
      return `${encodeURIComponent(key)}=${encodeURIComponent(value)}`;
    })
    .join('&');

    fetchOptions.body = urlEncodedData;
    fetchOptions.headers['Content-Type'] = 'application/x-www-form-urlencoded';
  }

  // 通信実行
  const request = fetch(CFS.ajax_url, fetchOptions)
  .then(response => {
    if (!response.ok) {
      throw new Error(`HTTP error ${response.status}: ${response.statusText}`);
    }
    return response.json();
  })
  .then(response => {
    if (successCallback && typeof successCallback === 'function') {
      successCallback(response);
    }
    return response;
  })
  .catch(error => {
    console.error('CFS Ajax Error:', error);
    if (errorCallback && typeof errorCallback === 'function') {
      errorCallback(error);
    }
    return Promise.reject(error);
  });

  return request;
};

/**
 * WordPressのAjaxアクションを実行する
 *
 * @param {string} action - WordPressのアクション名
 * @param {Object} [data={}] - 送信するデータ
 * @param {Function} [successCallback] - 成功時のコールバック関数
 * @param {Function} [errorCallback] - エラー時のコールバック関数
 * @param {Object} [options={}] - ajaxのオプション
 * @return {Promise} 通信結果を返すPromise
 */
export const ajaxAction = (
  action,
  data = {},
  successCallback,
  errorCallback,
  options = {}
) => {
  return ajax(
    { action, ...data },
    successCallback,
    errorCallback,
    options
  );
};

/**
 * CFSの専用Ajaxハンドラーを実行する
 *
 * @param {string} callback - CFS内部のコールバック名
 * @param {Object} [data={}] - 送信するデータ
 * @param {Function} [successCallback] - 成功時のコールバック関数
 * @param {Function} [errorCallback] - エラー時のコールバック関数
 * @return {Promise} 通信結果を返すPromise
 */
export const ajaxCFS = (
  callback,
  data = {},
  successCallback,
  errorCallback
) => {
  return ajaxAction(
    'cfs_ajax_handler',
    { ...data, cfs_ajax_callback: callback },
    successCallback,
    errorCallback
  );
};

/**
 * ロード中の表示を制御するAjaxラッパー
 *
 * @param {HTMLElement} container - ローディング表示を行うコンテナ要素
 * @param {Function} ajaxCall - ajax関数を実行する関数
 * @param {Object} [loadingOptions={}] - ロード表示のオプション
 * @param {string} [loadingOptions.loadingText='Loading...'] - ロード中のテキスト
 * @param {string} [loadingOptions.loadingClass='cfs-loading'] - 追加するクラス名
 * @return {Promise} 通信結果を返すPromise
 */
export const ajaxWithLoading = (
  container,
  ajaxCall,
  loadingOptions = {}
) => {
  if (!container) {
    return ajaxCall();
  }

  // オプションの設定
  const options = {
    loadingText: CFS.text?.loading || 'Loading...',
    loadingClass: 'cfs-loading',
    ...loadingOptions
  };

  // ローディング表示の追加
  const loadingElement = document.createElement('div');
  loadingElement.className = options.loadingClass;
  loadingElement.textContent = options.loadingText;
  container.appendChild(loadingElement);

  // Ajaxの実行
  return ajaxCall()
  .finally(() => {
    // 完了時にローディング表示を削除
    if (loadingElement && loadingElement.parentNode) {
      loadingElement.parentNode.removeChild(loadingElement);
    }
  });
};

/**
 * WordPressのREST APIを使用する
 *
 * @param {string} endpoint - エンドポイントパス
 * @param {Object} [options={}] - fetchオプション
 * @return {Promise} 通信結果を返すPromise
 */
export const wpAPI = (endpoint, options = {}) => {
  const defaultOptions = {
    credentials: 'same-origin',
    headers: {
      'Content-Type': 'application/json',
      'X-WP-Nonce': CFS.rest_nonce || ''
    }
  };

  const fetchOptions = { ...defaultOptions, ...options };

  // JSON形式のボディをセット
  if (options.body && typeof options.body === 'object' && !(options.body instanceof FormData)) {
    fetchOptions.body = JSON.stringify(options.body);
  }

  const restBase = CFS.rest_url || '/wp-json';
  const url = `${restBase}${endpoint.startsWith('/') ? '' : '/'}${endpoint}`;

  return fetch(url, fetchOptions)
  .then(response => {
    if (!response.ok) {
      return response.json().then(data => {
        throw new Error(data.message || `HTTP error ${response.status}`);
      });
    }
    return response.json();
  });
};

export default {
  ajax,
  ajaxAction,
  ajaxCFS,
  ajaxWithLoading,
  wpAPI
};
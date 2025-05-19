/**
 * Custom Field Suite - Sortable Module
 * ES Module implementation for drag & drop functionality
 *
 * @package CF
 * @version 3.0.0
 */

import { qs, qsa, addClass, removeClass, on, delegate } from './utils.js';

/**
 * Sortable要素の管理クラス
 */
class KDCFSortable {
  /**
   * コンストラクタ
   */
  constructor() {
    // 内部状態
    this.dragElement = null;
    this.placeholderElement = null;
    this.dragStartY = 0;
    this.containers = [];
    this.options = {
      itemSelector: '.cfs-sortable-item',
      handleSelector: '.cfs-sortable-handle',
      placeholderClass: 'cfs-sortable-placeholder',
      dragClass: 'cfs-sortable-dragging',
      containerClass: 'cfs-sortable-container'
    };

    // メソッドバインド
    this.init = this.init.bind(this);
    this.onDragStart = this.onDragStart.bind(this);
    this.onDragOver = this.onDragOver.bind(this);
    this.onDragEnd = this.onDragEnd.bind(this);
    this.onTouchStart = this.onTouchStart.bind(this);
    this.onTouchMove = this.onTouchMove.bind(this);
    this.onTouchEnd = this.onTouchEnd.bind(this);
  }

  /**
   * 指定要素をソート可能にする
   * @param {string|Element} container - コンテナ要素またはセレクタ
   * @param {Object} options - オプション
   */
  init(container, options = {}) {
    // オプションのマージ
    this.options = { ...this.options, ...options };

    // コンテナの特定
    const containers = typeof container === 'string'
      ? qsa(container)
      : (container instanceof Element ? [container] : container);

    // 各コンテナの初期化
    containers.forEach(el => {
      // コンテナにクラス追加
      addClass(el, this.options.containerClass);

      // すでに初期化済みなら追加しない
      if (this.containers.includes(el)) {
        return;
      }

      this.containers.push(el);

      // ドラッグハンドルのイベント設定
      const handles = qsa(this.options.handleSelector, el);
      handles.forEach(handle => {
        // ドラッグ開始イベント
        handle.setAttribute('draggable', 'true');
        handle.addEventListener('dragstart', this.onDragStart);
        handle.addEventListener('touchstart', this.onTouchStart, { passive: false });
      });

      // コンテナにドラッグオーバーイベント
      el.addEventListener('dragover', this.onDragOver);
      el.addEventListener('dragend', this.onDragEnd);
    });

    // グローバルタッチイベント
    document.addEventListener('touchmove', this.onTouchMove, { passive: false });
    document.addEventListener('touchend', this.onTouchEnd);

    // イベント発火 - 初期化完了
    const event = new CustomEvent('cfs:sortable-init', {
      detail: { containers }
    });
    document.dispatchEvent(event);

    return this;
  }

  /**
   * ドラッグ開始イベントハンドラ
   * @param {DragEvent} e - ドラッグイベント
   */
  onDragStart(e) {
    // 親アイテム要素を取得
    const item = e.target.closest(this.options.itemSelector);
    if (!item) return;

    // データトランスファーの設定
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', ''); // Firefox用

    // ドラッグ要素の保存とスタイル適用
    this.dragElement = item;
    addClass(item, this.options.dragClass);

    // プレースホルダーの作成
    this.createPlaceholder(item);

    // 遅延実行で視覚効果を適用（CSSトランジション用）
    setTimeout(() => {
      if (this.dragElement) {
        this.dragElement.style.opacity = '0.4';
      }
    }, 0);

    // イベント発火 - ドラッグ開始
    const event = new CustomEvent('cfs:sortable-dragstart', {
      detail: { item: this.dragElement }
    });
    document.dispatchEvent(event);
  }

  /**
   * ドラッグ中のイベントハンドラ
   * @param {DragEvent} e - ドラッグイベント
   */
  onDragOver(e) {
    // デフォルト動作のキャンセル
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';

    // ドラッグ中でなければ何もしない
    if (!this.dragElement || !this.placeholderElement) return;

    // カーソル下の要素を取得
    const target = e.target.closest(this.options.itemSelector);

    // 有効なターゲットでなければ何もしない
    if (!target || target === this.dragElement || target === this.placeholderElement) {
      return;
    }

    // ターゲットの親コンテナを取得
    const container = target.parentNode;

    // 同じコンテナ内での並べ替え
    const targetRect = target.getBoundingClientRect();
    const middle = targetRect.top + targetRect.height / 2;

    if (e.clientY < middle) {
      // ターゲットの前に配置
      container.insertBefore(this.placeholderElement, target);
    } else {
      // ターゲットの後に配置
      container.insertBefore(this.placeholderElement, target.nextSibling);
    }
  }

  /**
   * ドラッグ終了イベントハンドラ
   * @param {DragEvent} e - ドラッグイベント
   */
  onDragEnd(e) {
    // ドラッグ中でなければ何もしない
    if (!this.dragElement) return;

    // スタイルを元に戻す
    removeClass(this.dragElement, this.options.dragClass);
    this.dragElement.style.opacity = '';

    // プレースホルダーが存在し、親要素が同じならプレースホルダーと入れ替え
    if (this.placeholderElement && this.placeholderElement.parentNode) {
      this.placeholderElement.parentNode.replaceChild(this.dragElement, this.placeholderElement);
    }

    // イベント発火 - ソート完了
    const event = new CustomEvent('cfs:sortable-update', {
      detail: { item: this.dragElement }
    });
    document.dispatchEvent(event);

    // 状態リセット
    this.dragElement = null;
    this.placeholderElement = null;
  }

  /**
   * タッチ開始イベントハンドラ (モバイル対応)
   * @param {TouchEvent} e - タッチイベント
   */
  onTouchStart(e) {
    if (e.touches.length !== 1) return;

    // デフォルト動作をキャンセル
    e.preventDefault();

    // 親アイテム要素を取得
    const handle = e.target.closest(this.options.handleSelector);
    if (!handle) return;

    const item = handle.closest(this.options.itemSelector);
    if (!item) return;

    // 初期位置を記録
    const touch = e.touches[0];
    this.dragStartY = touch.clientY;

    // ドラッグ要素の保存とスタイル適用
    this.dragElement = item;
    addClass(item, this.options.dragClass);

    // プレースホルダーの作成
    this.createPlaceholder(item);
  }

  /**
   * タッチ移動イベントハンドラ (モバイル対応)
   * @param {TouchEvent} e - タッチイベント
   */
  onTouchMove(e) {
    if (!this.dragElement || !this.placeholderElement || e.touches.length !== 1) return;

    // デフォルト動作をキャンセル
    e.preventDefault();

    // タッチポイントの取得
    const touch = e.touches[0];
    const clientY = touch.clientY;

    // ドラッグ要素をタッチポイントに合わせて移動
    this.dragElement.style.position = 'absolute';
    this.dragElement.style.top = clientY + 'px';
    this.dragElement.style.zIndex = '1000';

    // タッチポイント下の要素を取得
    const target = document.elementFromPoint(touch.clientX, clientY);
    if (!target) return;

    const item = target.closest(this.options.itemSelector);

    // 有効なターゲットでなければ何もしない
    if (!item || item === this.dragElement || item === this.placeholderElement) {
      return;
    }

    // ターゲットの親コンテナを取得
    const container = item.parentNode;

    // 同じコンテナ内での並べ替え
    const targetRect = item.getBoundingClientRect();
    const middle = targetRect.top + targetRect.height / 2;

    if (clientY < middle) {
      // ターゲットの前に配置
      container.insertBefore(this.placeholderElement, item);
    } else {
      // ターゲットの後に配置
      container.insertBefore(this.placeholderElement, item.nextSibling);
    }
  }

  /**
   * タッチ終了イベントハンドラ (モバイル対応)
   * @param {TouchEvent} e - タッチイベント
   */
  onTouchEnd(e) {
    // ドラッグ中でなければ何もしない
    if (!this.dragElement) return;

    // スタイルを元に戻す
    removeClass(this.dragElement, this.options.dragClass);
    this.dragElement.style.opacity = '';
    this.dragElement.style.position = '';
    this.dragElement.style.top = '';
    this.dragElement.style.zIndex = '';

    // プレースホルダーが存在し、親要素が同じならプレースホルダーと入れ替え
    if (this.placeholderElement && this.placeholderElement.parentNode) {
      this.placeholderElement.parentNode.replaceChild(this.dragElement, this.placeholderElement);
    }

    // イベント発火 - ソート完了
    const event = new CustomEvent('cfs:sortable-update', {
      detail: { item: this.dragElement }
    });
    document.dispatchEvent(event);

    // 状態リセット
    this.dragElement = null;
    this.placeholderElement = null;
  }

  /**
   * プレースホルダー要素の作成
   * @param {Element} item - 元の要素
   */
  createPlaceholder(item) {
    // すでに存在していれば削除
    if (this.placeholderElement && this.placeholderElement.parentNode) {
      this.placeholderElement.parentNode.removeChild(this.placeholderElement);
    }

    // プレースホルダーの作成
    this.placeholderElement = document.createElement('div');
    this.placeholderElement.className = this.options.placeholderClass;

    // サイズを元の要素に合わせる
    const rect = item.getBoundingClientRect();
    this.placeholderElement.style.width = rect.width + 'px';
    this.placeholderElement.style.height = rect.height + 'px';

    // 元の要素の代わりに挿入
    item.parentNode.insertBefore(this.placeholderElement, item);
  }

  /**
   * 要素の破棄
   */
  destroy() {
    // 各コンテナのイベントリスナーを削除
    this.containers.forEach(container => {
      // ハンドルのイベント解除
      const handles = qsa(this.options.handleSelector, container);
      handles.forEach(handle => {
        handle.removeEventListener('dragstart', this.onDragStart);
        handle.removeEventListener('touchstart', this.onTouchStart);
        handle.removeAttribute('draggable');
      });

      // コンテナのイベント解除
      container.removeEventListener('dragover', this.onDragOver);
      container.removeEventListener('dragend', this.onDragEnd);

      // クラスの削除
      removeClass(container, this.options.containerClass);
    });

    // グローバルイベントリスナーを削除
    document.removeEventListener('touchmove', this.onTouchMove);
    document.removeEventListener('touchend', this.onTouchEnd);

    // 状態リセット
    this.containers = [];
    this.dragElement = null;
    this.placeholderElement = null;
  }
}

// インスタンス作成
const sortable = new KDCFSortable();

// DOMコンテンツロード完了時に初期化
document.addEventListener('DOMContentLoaded', () => {
  // デフォルトの初期化
  const defaultContainers = qsa('.cfs-loop-rows, .cfs-relationship');
  if (defaultContainers.length > 0) {
    sortable.init(defaultContainers);
  }

  // 動的に追加された要素を監視
  document.addEventListener('cfs:row_added', (e) => {
    // 新しいループ行内のソート可能なコンテナを初期化
    if (e.detail && e.detail.row) {
      const newContainers = qsa('.cfs-loop-rows, .cfs-relationship', e.detail.row);
      if (newContainers.length > 0) {
        sortable.init(newContainers);
      }
    }
  });
});

// エクスポート
export default sortable;
/**
 * Custom Field Suite - Tab Module
 * ES Module implementation for tab fields
 *
 * @package CFS
 * @version 3.0.0
 */

import { qs, qsa, addClass, removeClass, delegate } from './utils.js';

/**
 * タブフィールドの管理クラス
 */
class CFSTab {
  /**
   * コンストラクタ
   */
  constructor() {
    // 内部状態
    this.initialized = false;

    // メソッドバインド
    this.init = this.init.bind(this);
    this.activateTab = this.activateTab.bind(this);
    this.handleTabClick = this.handleTabClick.bind(this);
  }

  /**
   * 初期化処理
   */
  init() {
    if (this.initialized) return;

    // タブコンテナの初期化
    const tabContainers = qsa('.cfs-tabs');

    tabContainers.forEach(container => {
      // すべてのタブを取得
      const tabs = qsa('.cfs-tab', container);

      // タブが存在する場合
      if (tabs.length > 0) {
        // 最初のタブをアクティブにする（すでにアクティブなタブがなければ）
        const activeTab = qs('.cfs-tab.active', container);
        if (!activeTab && tabs.length > 0) {
          this.activateTab(tabs[0]);
        }

        // タブクリックイベントのセットアップ（イベント委譲）
        delegate(container, 'click', '.cfs-tab', this.handleTabClick);
      }
    });

    // 条件付きフィールドの表示変更時に、タブコンテンツが正しく表示されるようにする
    document.addEventListener('cfs:conditional_logic_updated', this.handleConditionalLogicUpdate.bind(this));

    // イベント発火 - 初期化完了
    const event = new CustomEvent('cfs:tab-init');
    document.dispatchEvent(event);

    this.initialized = true;
  }

  /**
   * タブクリックイベントハンドラ
   * @param {Event} e - クリックイベント
   * @param {Element} tab - クリックされたタブ要素
   */
  handleTabClick(e, tab) {
    e.preventDefault();
    this.activateTab(tab);
  }

  /**
   * 指定されたタブをアクティブにする
   * @param {Element} tab - アクティブにするタブ要素
   */
  activateTab(tab) {
    if (!tab) return;

    // タブコンテナを取得
    const tabContainer = tab.closest('.cfs-tabs');
    if (!tabContainer) return;

    // タブ名を取得
    const tabName = tab.getAttribute('rel') || tab.dataset.target;
    if (!tabName) return;

    // 同じタブコンテナ内のすべてのタブを非アクティブにする
    const allTabs = qsa('.cfs-tab', tabContainer);
    allTabs.forEach(t => removeClass(t, 'active'));

    // すべてのタブコンテンツを非表示にする
    const allContents = qsa('.cfs-tab-content');
    allContents.forEach(content => removeClass(content, 'active'));

    // 指定されたタブをアクティブにする
    addClass(tab, 'active');

    // 対応するコンテンツを表示する
    const content = qs(`.cfs-tab-content-${tabName}`);
    if (content) {
      addClass(content, 'active');
    }

    // イベント発火 - タブ切り替え
    const event = new CustomEvent('cfs:tab-changed', {
      detail: { tab, tabName, content }
    });
    document.dispatchEvent(event);
  }

  /**
   * タブをセレクタまたはインデックスで選択する
   * @param {string|number} selector - タブセレクタまたはインデックス
   */
  selectTab(selector) {
    let tab = null;

    // セレクタが文字列の場合
    if (typeof selector === 'string') {
      tab = qs(selector);
    }
    // セレクタが数値の場合（インデックス）
    else if (typeof selector === 'number') {
      const allTabs = qsa('.cfs-tab');
      if (selector >= 0 && selector < allTabs.length) {
        tab = allTabs[selector];
      }
    }

    if (tab) {
      this.activateTab(tab);
    }
  }

  /**
   * 条件付きロジック更新後のハンドラ
   */
  handleConditionalLogicUpdate() {
    // アクティブタブのコンテンツ内に表示されているフィールドがあるか確認
    const activeContents = qsa('.cfs-tab-content.active');

    activeContents.forEach(content => {
      // 表示されているフィールドを探す
      const visibleFields = qsa('.field', content);
      const hasVisibleFields = visibleFields.some(field =>
        field.style.display !== 'none' && !field.classList.contains('hidden')
      );

      // 表示フィールドがない場合、別のタブを選択
      if (!hasVisibleFields) {
        const tabContainers = qsa('.cfs-tabs');

        // 各タブコンテナについて処理
        tabContainers.forEach(container => {
          // この非表示コンテンツに関連するタブを探す
          const tabs = qsa('.cfs-tab', container);
          const activeTab = qs('.cfs-tab.active', container);

          // アクティブタブを探す
          let foundTab = false;
          for (let i = 0; i < tabs.length; i++) {
            const tab = tabs[i];
            const tabName = tab.getAttribute('rel') || tab.dataset.target;

            // このタブが現在のコンテンツを参照している場合
            if (content.classList.contains(`cfs-tab-content-${tabName}`)) {
              // アクティブタブである場合は次のタブを選択
              if (tab === activeTab) {
                // 次のタブまたは最初のタブを選択
                const nextTab = tabs[i + 1] || tabs[0];
                if (nextTab !== tab) {
                  this.activateTab(nextTab);
                  foundTab = true;
                  break;
                }
              }
            }
          }

          // タブが見つからず、アクティブタブがない場合は最初のタブを選択
          if (!foundTab && !activeTab && tabs.length > 0) {
            this.activateTab(tabs[0]);
          }
        });
      }
    });
  }
}

// インスタンス作成
const tab = new CFSTab();

// DOMコンテンツロード完了時に初期化
document.addEventListener('DOMContentLoaded', tab.init);

// グローバルAPIとしても公開
if (typeof window.CFS === 'object') {
  window.CFS.tab = tab;
}

// エクスポート
export default tab;
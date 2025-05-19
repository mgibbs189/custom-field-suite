/**
 * Custom Field Suite - メインモジュール
 * ES Module実装によるフィールド管理JSの入口点
 *
 * @package CFS
 * @version 3.0.0
 */

// ユーティリティモジュールをインポート
import utils, { ready, qs, qsa, delegate, ajax, ajaxCFS, fadeIn, fadeOut } from './utils.js';

// CFS グローバルオブジェクトの初期化
if (typeof window.CFS === 'undefined') {
  window.CFS = {};
}

// バージョン情報
window.CFS.version = '3.0.0';

/**
 * カスタムフィールドの初期化と管理
 */
class CFSFields {
  constructor() {
    // 内部状態管理変数
    this.initialized = false;
    this.sortTimer = null;

    // 初期化メソッドのバインド
    this.init = this.init.bind(this);
    this.initConditionalLogic = this.initConditionalLogic.bind(this);
    this.checkConditionalLogic = this.checkConditionalLogic.bind(this);
    this.initLoopFields = this.initLoopFields.bind(this);
    this.addRow = this.addRow.bind(this);
    this.removeRow = this.removeRow.bind(this);

    // 初期化（DOM読み込み完了後）
    ready(this.init);
  }

  /**
   * 初期化処理
   */
  init() {
    if (this.initialized) return;

    // 条件付きロジックの初期化
    this.initConditionalLogic();

    // ループフィールドの初期化
    this.initLoopFields();

    // Tabフィールドの初期化（将来的には別モジュール）
    this.initTabs();

    // 初期化完了
    this.initialized = true;
    console.log('CFS Fields initialized');

    // カスタムイベント発火
    const event = new CustomEvent('cfs:initialized');
    document.dispatchEvent(event);
  }

  /**
   * 条件付きロジックの初期化
   */
  initConditionalLogic() {
    // 条件グループの初期化
    qsa('.cfs-conditional-logic').forEach(group => {
      // トグルボタンのクリックイベント
      const toggleBtn = qs('.toggle', group);
      if (toggleBtn) {
        toggleBtn.addEventListener('click', e => {
          e.preventDefault();
          const content = qs('.content', group);
          content.classList.toggle('hidden');
        });
      }

      // ルール追加ボタン
      const addRuleBtn = qs('.add-rule', group);
      if (addRuleBtn) {
        addRuleBtn.addEventListener('click', e => {
          e.preventDefault();

          // フィールド名とルールインデックスを取得
          const fieldName = group.dataset.fieldName;
          const ruleIndex = qsa('.rule', group).length;

          // ロード中表示
          const rulesContainer = qs('.rules', group);
          const loadingRow = document.createElement('div');
          loadingRow.className = 'loading-row';
          loadingRow.textContent = CFS.text?.loading || 'Loading...';
          rulesContainer.appendChild(loadingRow);

          // Ajax経由でルール行を追加
          ajaxCFS(
            'add_rule_row',
            {
              rule_index: ruleIndex,
              field_name: fieldName
            },
            response => {
              // ロード中表示を削除
              if (loadingRow.parentNode) {
                loadingRow.parentNode.removeChild(loadingRow);
              }

              // 新しいルール行を追加
              if (response.html) {
                rulesContainer.insertAdjacentHTML('beforeend', response.html);
              }
            }
          );
        });
      }
    });

    // 既存ルール行の削除ボタン
    delegate(document, 'click', '.remove-rule', e => {
      e.preventDefault();
      const ruleRow = e.target.closest('.rule');
      if (ruleRow && ruleRow.parentNode) {
        ruleRow.parentNode.removeChild(ruleRow);
      }
    });

    // ルールタイプの変更イベント
    delegate(document, 'change', '.rule-type', e => {
      const select = e.target;
      const ruleRow = select.closest('.rule');

      // 値が必要なルールタイプかどうかでクラスを切り替え
      const hasValue = ['==', '!=', '>', '>=', '<', '<=', 'contains', 'not contains', 'begins with', 'ends with'].includes(select.value);

      if (hasValue) {
        ruleRow.classList.add('has-value');
      } else {
        ruleRow.classList.remove('has-value');
      }
    });

    // フィールド値変更時のチェック
    document.addEventListener('change', this.checkConditionalLogic);

    // 初期状態のチェック
    this.checkConditionalLogic();
  }

  /**
   * 条件付きロジックのチェックと適用
   * @param {Event} [event] - 変更イベント（任意）
   */
  checkConditionalLogic(event) {
    // すべての条件グループをチェック
    qsa('.cfs-conditional-logic').forEach(group => {
      // 対象フィールドとオペレータを取得
      const targetId = group.dataset.target;
      const operator = group.dataset.operator || 'and';

      // ルールの評価
      const rules = qsa('.rule', group);
      let showField = operator === 'and'; // ANDの場合は初期値true、ORの場合は初期値false

      // ルールがない場合は表示
      if (rules.length === 0) {
        showField = true;
      } else {
        rules.forEach(rule => {
          const result = this.evaluateRule(rule);

          if (operator === 'and') {
            showField = showField && result;
          } else {
            showField = showField || result;
          }
        });
      }

      // 対象フィールドの表示/非表示
      const targetField = document.getElementById(targetId);
      if (targetField) {
        if (showField) {
          fadeIn(targetField);
        } else {
          fadeOut(targetField);
        }
      }
    });
  }

  /**
   * 個別ルールの評価
   * @param {Element} rule - ルール要素
   * @return {boolean} 評価結果
   */
  evaluateRule(rule) {
    // フィールド、演算子、値を取得
    const fieldSelect = qs('.rule-field', rule);
    const opSelect = qs('.rule-type', rule);
    const valueField = qs('.rule-value', rule);

    if (!fieldSelect || !opSelect) return true;

    const fieldName = fieldSelect.value;
    const operator = opSelect.value;
    const compareValue = valueField ? valueField.value : '';

    // 値が必要ないオペレータの場合
    if (['is empty', 'is not empty'].includes(operator)) {
      return this.compareValues('', operator, fieldName);
    }

    return this.compareValues(compareValue, operator, fieldName);
  }

  /**
   * 値の比較
   * @param {string} compareValue - 比較する値
   * @param {string} operator - 演算子
   * @param {string} fieldName - フィールド名
   * @return {boolean} 比較結果
   */
  compareValues(compareValue, operator, fieldName) {
    // フィールド要素を取得
    const field = document.querySelector(`[name="${fieldName}"]`);
    if (!field) return true;

    // フィールドの値を取得
    let fieldValue = '';

    if (field.type === 'checkbox') {
      // チェックボックスの場合
      fieldValue = field.checked ? field.value : '';
    } else if (field.type === 'radio') {
      // ラジオボタンの場合
      const checkedRadio = document.querySelector(`[name="${fieldName}"]:checked`);
      fieldValue = checkedRadio ? checkedRadio.value : '';
    } else if (field.tagName === 'SELECT' && field.multiple) {
      // 複数選択セレクトの場合
      fieldValue = Array.from(field.selectedOptions).map(opt => opt.value).join(',');
    } else {
      // その他のフィールド
      fieldValue = field.value;
    }

    // 値の比較
    switch (operator) {
      case '==':
        return fieldValue == compareValue;
      case '!=':
        return fieldValue != compareValue;
      case '>':
        return parseFloat(fieldValue) > parseFloat(compareValue);
      case '>=':
        return parseFloat(fieldValue) >= parseFloat(compareValue);
      case '<':
        return parseFloat(fieldValue) < parseFloat(compareValue);
      case '<=':
        return parseFloat(fieldValue) <= parseFloat(compareValue);
      case 'contains':
        return fieldValue.indexOf(compareValue) !== -1;
      case 'not contains':
        return fieldValue.indexOf(compareValue) === -1;
      case 'begins with':
        return fieldValue.indexOf(compareValue) === 0;
      case 'ends with':
        return fieldValue.slice(-compareValue.length) === compareValue;
      case 'is empty':
        return fieldValue === '';
      case 'is not empty':
        return fieldValue !== '';
      default:
        return true;
    }
  }

  /**
   * ループフィールドの初期化
   */
  initLoopFields() {
    // 行の追加ボタン
    delegate(document, 'click', '.cfs-add-row', e => {
      e.preventDefault();
      this.addRow(e.target.closest('.cfs-add-row'));
    });

    // 行の削除ボタン
    delegate(document, 'click', '.cfs-delete-row', e => {
      e.preventDefault();
      this.removeRow(e.target.closest('.cfs-delete-row'));
    });
  }

  /**
   * ループ行の追加
   * @param {Element} button - 追加ボタン
   */
  addRow(button) {
    if (!button) return;

    const loop = button.closest('.cfs-loop');
    const template = qs('.cfs-row-template', loop);
    const rowContainer = qs('.cfs-loop-rows', loop);

    if (!template || !rowContainer) return;

    // 現在の行数
    const rowCount = qsa('.cfs-row', loop).length;

    // テンプレートから新しい行を作成（プレースホルダーを置換）
    let html = template.innerHTML.replace(/{loop_row_id}/g, rowCount);

    // 新しい行を追加
    rowContainer.insertAdjacentHTML('beforeend', html);
    const newRow = rowContainer.lastElementChild;

    // 新しい行内のサブフィールドを初期化
    this.initSubfields(newRow);

    // レイアウト調整のためにイベント発火
    const event = new CustomEvent('cfs:row_added', {
      detail: { row: newRow, loop: loop }
    });
    document.dispatchEvent(event);

    // 条件付きロジックを再チェック
    this.checkConditionalLogic();
  }

  /**
   * ループ行の削除
   * @param {Element} button - 削除ボタン
   */
  removeRow(button) {
    if (!button) return;

    const row = button.closest('.cfs-row');

    // 確認ダイアログ
    if (row && window.confirm(CFS.text?.confirm_remove || 'Are you sure?')) {
      // アニメーションで削除
      fadeOut(row, 400, () => {
        if (row.parentNode) {
          row.parentNode.removeChild(row);

          // レイアウト調整のためにイベント発火
          const event = new CustomEvent('cfs:row_removed');
          document.dispatchEvent(event);
        }
      });
    }
  }

  /**
   * サブフィールドの初期化
   * @param {Element} container - フィールドコンテナ
   *
   * 注: 将来的には各フィールドタイプの個別モジュールから初期化関数をインポートして呼び出す
   */
  initSubfields(container) {
    // ここでは初期段階として基本的な初期化のみ実装
    // 後で各フィールドタイプの専用モジュールに移行

    // 日付フィールド
    qsa('.cfs-datepicker', container).forEach(field => {
      // 将来的にはDateモジュールからインポートした関数を呼び出す
      console.log('Datepicker to be initialized:', field);
    });

    // カラーピッカー
    qsa('.cfs-color', container).forEach(field => {
      // 将来的にはColorモジュールからインポートした関数を呼び出す
      console.log('Color picker to be initialized:', field);
    });

    // リッチテキストエディタ
    qsa('.cfs-wysiwyg', container).forEach(field => {
      // 将来的にはWysiwygモジュールからインポートした関数を呼び出す
      console.log('WYSIWYG editor to be initialized:', field);
    });
  }

  /**
   * タブフィールドの初期化
   */
  initTabs() {
    // タブクリックイベント
    delegate(document, 'click', '.cfs-tab', e => {
      e.preventDefault();

      const tab = e.target.closest('.cfs-tab');
      const tabGroup = tab.closest('.cfs-tabs');

      // 現在のアクティブタブを取得
      const activeTab = qs('.cfs-tab.active', tabGroup);

      // 同じタブがクリックされた場合は何もしない
      if (activeTab === tab) return;

      // タブのアクティブ状態を切り替え
      if (activeTab) {
        activeTab.classList.remove('active');
      }
      tab.classList.add('active');

      // タブコンテンツの切り替え
      const targetId = tab.getAttribute('href') || tab.dataset.target;
      if (targetId) {
        // 現在のアクティブコンテンツを非アクティブ化
        const activeContent = qs('.cfs-tab-content.active');
        if (activeContent) {
          activeContent.classList.remove('active');
        }

        // 対象コンテンツをアクティブ化
        const targetContent = document.querySelector(targetId);
        if (targetContent) {
          targetContent.classList.add('active');
        }
      }
    });
  }
}

// CFSFieldsインスタンスを作成
const cfsFields = new CFSFields();

// グローバル関数APIの公開
window.CFS.fieldsFunctions = {
  /**
   * 条件付きロジックを再評価
   */
  checkConditionalLogic: () => cfsFields.checkConditionalLogic(),

  /**
   * ループ行の追加
   * @param {Element|string} button - 追加ボタンまたはセレクタ
   */
  addRow: (button) => {
    const btn = typeof button === 'string' ? qs(button) : button;
    cfsFields.addRow(btn);
  },

  /**
   * ループ行の削除
   * @param {Element|string} button - 削除ボタンまたはセレクタ
   */
  removeRow: (button) => {
    const btn = typeof button === 'string' ? qs(button) : button;
    cfsFields.removeRow(btn);
  }
};

// モジュールのエクスポート
export default cfsFields;
export { utils };
/**
 * Custom Field Suite - Loop Module
 * ES Module for loop fields
 */
import { qs, qsa, addClass, removeClass, fadeOut } from './utils.js';
import sortable from './sortable.js';

class CFSLoop {
  constructor() {
    this.init = this.init.bind(this);
    this.initFields = this.initFields.bind(this);
    this.addRow = this.addRow.bind(this);
    this.removeRow = this.removeRow.bind(this);
  }

  init() {
    this.initFields();
  }

  initFields() {
    // ループフィールドの初期化
    qsa('.cfs-loop:not(.cfs-loop-initialized)').forEach(field => {
      // ソータブル初期化
      const loopRows = qs('.cfs-loop-rows', field);
      if (loopRows) {
        sortable.init(loopRows);
      }

      addClass(field, 'cfs-loop-initialized');
    });

    // 行追加ボタン
    delegate(document, 'click', '.cfs-add-row', (e, button) => {
      e.preventDefault();
      this.addRow(button);
    });

    // 行削除ボタン
    delegate(document, 'click', '.cfs-delete-row', (e, button) => {
      e.preventDefault();
      this.removeRow(button);
    });
  }

  addRow(button) {
    // 行追加処理
    // ...メインスクリプトの実装部分と統合...
  }

  removeRow(button) {
    // 行削除処理
    // ...メインスクリプトの実装部分と統合...
  }
}

// インスタンス作成して初期化
const loopField = new CFSLoop();
document.addEventListener('DOMContentLoaded', loopField.init);
export default loopField;
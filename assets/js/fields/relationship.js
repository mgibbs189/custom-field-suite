/**
 * Custom Field Suite - Relationship Module
 * ES Module for relationship fields
 */
import { qs, qsa, addClass, removeClass, ajax } from './utils.js';
import sortable from './sortable.js';

class CFSRelationship {
  constructor() {
    this.init = this.init.bind(this);
    this.initFields = this.initFields.bind(this);
    this.search = this.search.bind(this);
  }

  init() {
    this.initFields();
    document.addEventListener('cfs:row_added', this.handleRowAdded.bind(this));
  }

  initFields() {
    qsa('.cfs_relationship:not(.cfs-relationship-initialized)').forEach(field => {
      // 検索ボックス
      const searchInput = qs('.cfs_relationship_search', field);
      if (searchInput) {
        searchInput.addEventListener('input', e => {
          this.search(e.target);
        });
      }

      // ソータブル初期化
      const valueArea = qs('.relationship_right', field);
      if (valueArea) {
        sortable.init(valueArea);
      }

      // 項目選択イベント
      delegate(field, 'click', '.relationship_left .cfs_relationship_item', (e, item) => {
        this.addItem(item);
      });

      // 項目削除イベント
      delegate(field, 'click', '.relationship_right .cfs_relationship_item', (e, item) => {
        this.removeItem(item);
      });

      addClass(field, 'cfs-relationship-initialized');
    });
  }

  search(input) {
    // 検索処理
    const term = input.value.toLowerCase();
    const field = input.closest('.cfs_relationship');
    const items = qsa('.relationship_left .cfs_relationship_item', field);

    items.forEach(item => {
      const text = item.textContent.toLowerCase();
      if (text.includes(term) || term === '') {
        item.style.display = '';
      } else {
        item.style.display = 'none';
      }
    });
  }

  // 他のメソッド...
}

// インスタンス作成して初期化
const relationshipField = new CFSRelationship();
document.addEventListener('DOMContentLoaded', relationshipField.init);
export default relationshipField;
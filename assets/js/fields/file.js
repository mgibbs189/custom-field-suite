/**
 * Custom Field Suite - File Module
 * ES Module for file upload fields
 */
import { qs, qsa, addClass, removeClass } from './utils.js';

class CFSFile {
  constructor() {
    this.init = this.init.bind(this);
    this.initFields = this.initFields.bind(this);
    this.openMediaLibrary = this.openMediaLibrary.bind(this);
  }

  init() {
    this.initFields();
    document.addEventListener('cfs:row_added', this.handleRowAdded.bind(this));
  }

  initFields() {
    // ファイルボタンにイベントハンドラを追加
    qsa('.cfs_file:not(.cfs-file-initialized)').forEach(field => {
      const button = qs('.cfs_upload_button', field);
      if (button) {
        button.addEventListener('click', e => {
          e.preventDefault();
          this.openMediaLibrary(e.target);
        });
      }

      // 削除ボタン
      const removeBtn = qs('.cfs_remove_button', field);
      if (removeBtn) {
        removeBtn.addEventListener('click', e => {
          e.preventDefault();
          this.removeFile(e.target);
        });
      }

      addClass(field, 'cfs-file-initialized');
    });
  }

  openMediaLibrary(button) {
    // wp.mediaを使用
    const mediaFrame = wp.media({
      title: button.dataset.text || 'Select File',
      button: { text: button.dataset.text || 'Select File' },
      multiple: false
    });

    // 選択イベント
    mediaFrame.on('select', () => {
      const attachment = mediaFrame.state().get('selection').first().toJSON();
      this.updateField(button, attachment);
    });

    mediaFrame.open();
  }

  // 他のメソッド...
}

// インスタンス作成して初期化
const fileField = new CFSFile();
document.addEventListener('DOMContentLoaded', fileField.init);
export default fileField;
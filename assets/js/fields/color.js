/**
 * kinokoData Custom Fields - Color Module
 * ES Module implementation for color fields
 *
 * @package CFS
 * @version 3.0.0
 */

import { qs, qsa, addClass, removeClass, fadeIn, fadeOut } from './utils.js';

/**
 * カラーフィールドの管理クラス
 */
class CFSColor {
  /**
   * コンストラクタ
   */
  constructor() {
    // 内部状態
    this.initialized = false;
    this.activeField = null;
    this.colorpicker = null;
    this.container = null;
    this.selectedColor = null;

    // カラーパレット
    this.defaultColors = [
      '#000000', '#FFFFFF', '#F44336', '#E91E63', '#9C27B0', '#673AB7',
      '#3F51B5', '#2196F3', '#03A9F4', '#00BCD4', '#009688', '#4CAF50',
      '#8BC34A', '#CDDC39', '#FFEB3B', '#FFC107', '#FF9800', '#FF5722',
      '#795548', '#9E9E9E', '#607D8B', '#E53935', '#D81B60', '#8E24AA',
      '#5E35B1', '#3949AB', '#1E88E5', '#039BE5', '#00ACC1', '#00897B',
      '#43A047', '#7CB342', '#C0CA33', '#FDD835', '#FFB300', '#FB8C00',
      '#F4511E', '#6D4C41', '#757575', '#546E7A'
    ];

    // メソッドバインド
    this.init = this.init.bind(this);
    this.initFields = this.initFields.bind(this);
    this.initColorpicker = this.initColorpicker.bind(this);
    this.onInputClick = this.onInputClick.bind(this);
    this.onColorSelect = this.onColorSelect.bind(this);
    this.onClickOutside = this.onClickOutside.bind(this);
    this.closeColorpicker = this.closeColorpicker.bind(this);
    this.validateColor = this.validateColor.bind(this);
  }

  /**
   * 初期化処理
   */
  init() {
    if (this.initialized) return;

    // カラーピッカーコンテナの作成
    this.initColorpicker();

    // 既存フィールドの初期化
    this.initFields();

    // 動的に追加されたフィールドを監視
    document.addEventListener('cfs:row_added', this.handleRowAdded.bind(this));

    // 外部クリックのイベントリスナー
    document.addEventListener('click', this.onClickOutside);

    // イベント発火 - 初期化完了
    const event = new CustomEvent('cfs:color-init');
    document.dispatchEvent(event);

    this.initialized = true;
  }

  /**
   * 既存のカラーフィールドの初期化
   */
  initFields() {
    const colorFields = qsa('.cfs_color:not(.cfs-color-initialized)');

    colorFields.forEach(field => {
      // 入力フィールドと色プレビュー要素を取得
      const input = qs('input[type="text"]', field);

      // プレビュー要素がなければ作成
      let preview = qs('.cfs-color-preview', field);
      if (!preview && input) {
        preview = document.createElement('div');
        preview.className = 'cfs-color-preview';
        input.parentNode.insertBefore(preview, input.nextSibling);

        // プレビューの初期色を設定
        if (input.value && this.validateColor(input.value)) {
          preview.style.backgroundColor = input.value;
        }
      }

      if (input) {
        // 入力フィールドの変更イベント
        input.addEventListener('change', e => {
          const color = e.target.value;
          if (color && preview && this.validateColor(color)) {
            preview.style.backgroundColor = color;
          } else if (preview) {
            preview.style.backgroundColor = '';
          }
        });

        // クリックイベントを入力フィールドとプレビューに追加
        input.addEventListener('click', this.onInputClick);
        if (preview) {
          preview.addEventListener('click', () => this.onInputClick({ target: input }));
        }

        // クラスを追加して初期化済みとしてマーク
        addClass(field, 'cfs-color-initialized');
      }
    });
  }

  /**
   * カラーピッカーコンテナの初期化
   */
  initColorpicker() {
    // すでに存在する場合は何もしない
    if (this.container) return;

    // コンテナ作成
    this.container = document.createElement('div');
    this.container.className = 'cfs-colorpicker';
    this.container.style.display = 'none';
    document.body.appendChild(this.container);

    // インスタンス変数としてカラーピッカーを保持
    this.colorpicker = new CFSColorpicker({
      colors: this.defaultColors,
      onChange: this.onColorSelect
    });

    // カラーピッカーをコンテナに追加
    this.container.appendChild(this.colorpicker.element);
  }

  /**
   * 入力フィールドクリック時のハンドラ
   * @param {Event} e - クリックイベント
   */
  onInputClick(e) {
    e.preventDefault();
    e.stopPropagation();

    const input = e.target;
    const fieldWrapper = input.closest('.cfs_color');

    if (!fieldWrapper || !this.colorpicker) return;

    // すでにこのフィールドがアクティブならカラーピッカーを閉じる
    if (this.activeField === input) {
      this.closeColorpicker();
      return;
    }

    // 異なるフィールドがアクティブならいったん閉じる
    if (this.activeField) {
      this.closeColorpicker();
    }

    // 現在の入力値を取得
    const colorValue = input.value.trim();

    // 入力値があれば色として設定
    if (colorValue && this.validateColor(colorValue)) {
      this.selectedColor = colorValue;
      this.colorpicker.setColor(this.selectedColor);
    } else {
      this.selectedColor = null;
      this.colorpicker.setColor(null);
    }

    // アクティブフィールドを設定
    this.activeField = input;

    // 入力フィールドの位置に合わせてカラーピッカーを配置
    const rect = input.getBoundingClientRect();
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;

    this.container.style.top = (rect.bottom + scrollTop) + 'px';
    this.container.style.left = (rect.left + scrollLeft) + 'px';

    // カラーピッカーを表示
    this.container.style.display = 'block';
    fadeIn(this.container, 200);
  }

  /**
   * 色選択時のコールバック
   * @param {string} color - 選択された色（HEX形式）
   */
  onColorSelect(color) {
    if (!this.activeField) return;

    // 色を設定
    this.selectedColor = color;

    // 入力フィールドに反映
    this.activeField.value = color;

    // プレビューを更新
    const fieldWrapper = this.activeField.closest('.cfs_color');
    const preview = qs('.cfs-color-preview', fieldWrapper);
    if (preview) {
      preview.style.backgroundColor = color;
    }

    // 変更イベントを発火して条件ロジックなどを更新
    const changeEvent = new Event('change', { bubbles: true });
    this.activeField.dispatchEvent(changeEvent);

    // カラーピッカーを閉じる
    this.closeColorpicker();
  }

  /**
   * カラーピッカー外クリック時のハンドラ
   * @param {Event} e - クリックイベント
   */
  onClickOutside(e) {
    // カラーピッカーが開かれていない場合は何もしない
    if (!this.container || this.container.style.display === 'none') {
      return;
    }

    // クリックがカラーピッカー内部またはアクティブな入力フィールドの場合は何もしない
    if (
      this.container.contains(e.target) ||
      (this.activeField && (this.activeField === e.target || this.activeField.contains(e.target))) ||
      e.target.classList.contains('cfs-color-preview')
    ) {
      return;
    }

    // それ以外の場所のクリックならカラーピッカーを閉じる
    this.closeColorpicker();
  }

  /**
   * カラーピッカーを閉じる
   */
  closeColorpicker() {
    if (this.container) {
      fadeOut(this.container, 200);
    }
    this.activeField = null;
  }

  /**
   * 動的に追加された行のハンドラ
   * @param {CustomEvent} e - イベント
   */
  handleRowAdded(e) {
    if (e.detail && e.detail.row) {
      const newColorFields = qsa('.cfs_color:not(.cfs-color-initialized)', e.detail.row);

      if (newColorFields.length > 0) {
        newColorFields.forEach(field => {
          // 入力フィールドと色プレビュー要素を取得
          const input = qs('input[type="text"]', field);

          // プレビュー要素がなければ作成
          let preview = qs('.cfs-color-preview', field);
          if (!preview && input) {
            preview = document.createElement('div');
            preview.className = 'cfs-color-preview';
            input.parentNode.insertBefore(preview, input.nextSibling);

            // プレビューの初期色を設定
            if (input.value && this.validateColor(input.value)) {
              preview.style.backgroundColor = input.value;
            }
          }

          if (input) {
            // 入力フィールドの変更イベント
            input.addEventListener('change', e => {
              const color = e.target.value;
              if (color && preview && this.validateColor(color)) {
                preview.style.backgroundColor = color;
              } else if (preview) {
                preview.style.backgroundColor = '';
              }
            });

            // クリックイベントを入力フィールドとプレビューに追加
            input.addEventListener('click', this.onInputClick);
            if (preview) {
              preview.addEventListener('click', () => this.onInputClick({ target: input }));
            }

            // クラスを追加して初期化済みとしてマーク
            addClass(field, 'cfs-color-initialized');
          }
        });
      }
    }
  }

  /**
   * カラーコードの検証
   * @param {string} color - 検証する色コード
   * @return {boolean} 有効な色コードならtrue
   */
  validateColor(color) {
    // null, undefined, 空文字列の場合は無効
    if (!color) return false;

    // 先頭の#は省略可能
    if (color.charAt(0) !== '#') {
      color = '#' + color;
    }

    // 3桁、6桁、8桁の16進数形式のみ許可
    return /^#([0-9A-F]{3}|[0-9A-F]{6}|[0-9A-F]{8})$/i.test(color);
  }
}

/**
 * カラーピッカーコンポーネント
 */
class CFSColorpicker {
  /**
   * コンストラクタ
   * @param {Object} options - カラーピッカーオプション
   */
  constructor(options = {}) {
    this.options = {
      colors: [],
      onChange: null,
      ...options
    };

    // 内部状態
    this.selectedColor = null;

    // 要素の作成
    this.element = this.createColorpickerElement();
  }

  /**
   * カラーピッカー要素の作成
   * @return {HTMLElement} カラーピッカー要素
   */
  createColorpickerElement() {
    const container = document.createElement('div');
    container.className = 'cfs-colorpicker-container';

    // カラーパレット
    const palette = document.createElement('div');
    palette.className = 'cfs-colorpicker-palette';

    // カラースウォッチの作成
    this.options.colors.forEach(color => {
      const swatch = document.createElement('div');
      swatch.className = 'cfs-colorpicker-swatch';
      swatch.style.backgroundColor = color;
      swatch.dataset.color = color;
      swatch.title = color;

      // スウォッチがクリックされたときの処理
      swatch.addEventListener('click', () => {
        this.selectColor(color);
      });

      palette.appendChild(swatch);
    });

    // カスタムカラー入力
    const customColorSection = document.createElement('div');
    customColorSection.className = 'cfs-colorpicker-custom';

    // カラー入力フィールド
    const colorInput = document.createElement('input');
    colorInput.type = 'text';
    colorInput.className = 'cfs-colorpicker-input';
    colorInput.placeholder = '#RRGGBB';
    colorInput.maxLength = 9; // 最大 #RRGGBBAA

    // 入力値が変更されたときの処理
    colorInput.addEventListener('input', e => {
      let color = e.target.value;

      // #で始まっていない場合は追加
      if (color && color.charAt(0) !== '#') {
        color = '#' + color;
        e.target.value = color;
      }
    });

    // Enterキーが押されたときの処理
    colorInput.addEventListener('keydown', e => {
      if (e.key === 'Enter') {
        e.preventDefault();

        const color = e.target.value;
        const colorRegex = /^#([0-9A-F]{3}|[0-9A-F]{6}|[0-9A-F]{8})$/i;

        if (colorRegex.test(color)) {
          this.selectColor(color);
        }
      }
    });

    // プレビュー要素
    const preview = document.createElement('div');
    preview.className = 'cfs-colorpicker-preview';

    // 適用ボタン
    const applyButton = document.createElement('button');
    applyButton.type = 'button';
    applyButton.className = 'cfs-colorpicker-apply';
    applyButton.textContent = '適用';
    applyButton.addEventListener('click', () => {
      const color = colorInput.value;
      const colorRegex = /^#([0-9A-F]{3}|[0-9A-F]{6}|[0-9A-F]{8})$/i;

      if (colorRegex.test(color)) {
        this.selectColor(color);
      }
    });

    customColorSection.appendChild(colorInput);
    customColorSection.appendChild(preview);
    customColorSection.appendChild(applyButton);

    // コンテナに要素を追加
    container.appendChild(palette);
    container.appendChild(customColorSection);

    return container;
  }

  /**
   * 色を選択
   * @param {string} color - 選択する色（HEX形式）
   */
  selectColor(color) {
    this.selectedColor = color;

    // 選択状態を更新
    const swatches = qsa('.cfs-colorpicker-swatch', this.element);
    swatches.forEach(swatch => {
      if (swatch.dataset.color === color) {
        addClass(swatch, 'selected');
      } else {
        removeClass(swatch, 'selected');
      }
    });

    // カスタム入力を更新
    const colorInput = qs('.cfs-colorpicker-input', this.element);
    if (colorInput) {
      colorInput.value = color;
    }

    // プレビューを更新
    const preview = qs('.cfs-colorpicker-preview', this.element);
    if (preview) {
      preview.style.backgroundColor = color;
    }

    // コールバックを呼び出す
    if (this.options.onChange && typeof this.options.onChange === 'function') {
      this.options.onChange(color);
    }
  }

  /**
   * 色を設定
   * @param {string} color - 設定する色（HEX形式）
   */
  setColor(color) {
    // 色を選択状態にする
    if (color) {
      // 選択状態を更新
      const swatches = qsa('.cfs-colorpicker-swatch', this.element);
      swatches.forEach(swatch => {
        if (swatch.dataset.color.toLowerCase() === color.toLowerCase()) {
          addClass(swatch, 'selected');
        } else {
          removeClass(swatch, 'selected');
        }
      });

      // カスタム入力を更新
      const colorInput = qs('.cfs-colorpicker-input', this.element);
      if (colorInput) {
        colorInput.value = color;
      }

      // プレビューを更新
      const preview = qs('.cfs-colorpicker-preview', this.element);
      if (preview) {
        preview.style.backgroundColor = color;
      }

      this.selectedColor = color;
    } else {
      // 選択をクリア
      const swatches = qsa('.cfs-colorpicker-swatch.selected', this.element);
      swatches.forEach(swatch => {
        removeClass(swatch, 'selected');
      });

      // カスタム入力をクリア
      const colorInput = qs('.cfs-colorpicker-input', this.element);
      if (colorInput) {
        colorInput.value = '';
      }

      // プレビューをクリア
      const preview = qs('.cfs-colorpicker-preview', this.element);
      if (preview) {
        preview.style.backgroundColor = '';
      }

      this.selectedColor = null;
    }
  }
}

// インスタンス作成
const colorField = new CFSColor();

// DOMコンテンツロード完了時に初期化
document.addEventListener('DOMContentLoaded', colorField.init);

// グローバルAPIとしても公開
if (typeof window.CFS === 'object') {
  window.CFS.colorField = colorField;
}

// エクスポート
export default colorField;
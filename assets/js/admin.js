/**
 * Custom Field Suite Extended - Admin JavaScript
 *
 * jQueryを使用せず、純粋なJavaScriptで実装されたフィールド管理スクリプト
 */

(function() {
  'use strict';

  // グローバル変数を定義
  const CFSE = {
    initialized: false,
    config: {},
    fields: {},
    ready: false
  };

  // DOMの準備ができたら初期化
  document.addEventListener('DOMContentLoaded', function() {
    initCFSE();
  });

  /**
   * CFSEを初期化
   */
  function initCFSE() {
    // 設定を取得
    if (typeof cfseConfig !== 'undefined') {
      CFSE.config = cfseConfig;
    }

    // フィールドコンテナを取得
    const fieldContainers = document.querySelectorAll('.cfse-fields-container');

    if (fieldContainers.length === 0) {
      return;
    }

    // 各フィールドタイプの初期化
    initFieldTypes();

    // 条件付き表示の初期化
    initConditionalLogic();

    CFSE.initialized = true;
    CFSE.ready = true;

    // カスタムイベントを発火
    document.dispatchEvent(new CustomEvent('cfse-ready'));
  }

  /**
   * 各フィールドタイプを初期化
   */
  function initFieldTypes() {
    // ループフィールドを初期化
    initLoopFields();

    // 日付フィールドを初期化
    initDateFields();

    // カラーフィールドを初期化
    initColorFields();

    // ファイルフィールドを初期化
    initFileFields();

    // リレーションシップフィールドを初期化
    initRelationshipFields();
  }

  /**
   * ループフィールドを初期化
   */
  function initLoopFields() {
    // ループフィールドを全て取得
    const loopFields = document.querySelectorAll('.cfse-loop-field');

    loopFields.forEach(function(loopField) {
      // 「行を追加」ボタンにイベントリスナーを設定
      const addButton = loopField.querySelector('.cfse-loop-add-row');
      if (addButton) {
        addButton.addEventListener('click', function() {
          addLoopRow(loopField);
        });
      }

      // 既存の行のイベントリスナーを設定
      const rows = loopField.querySelectorAll('.cfse-loop-row');
      rows.forEach(function(row) {
        setupLoopRowEvents(row, loopField);
      });

      // 並び替え可能にする
      const rowsContainer = loopField.querySelector('.cfse-loop-rows');
      if (rowsContainer) {
        setupLoopRowsSorting(rowsContainer, loopField);
      }
    });
  }

  /**
   * ループフィールドの行を追加
   *
   * @param {HTMLElement} loopField ループフィールド要素
   */
  function addLoopRow(loopField) {
    // テンプレートを取得
    const template = loopField.querySelector('.cfse-loop-template');
    if (!template) return;

    // 既存の行数を取得
    const rowsContainer = loopField.querySelector('.cfse-loop-rows');
    const rows = rowsContainer.querySelectorAll('.cfse-loop-row');
    const newIndex = rows.length;

    // 最大数の制限をチェック
    const maxAttr = loopField.getAttribute('data-max');
    if (maxAttr && parseInt(maxAttr, 10) > 0) {
      const max = parseInt(maxAttr, 10);
      if (rows.length >= max) {
        // 最大数に達している場合は追加しない
        return;
      }
    }

    // テンプレートからHTMLを生成
    let newRowHTML = template.innerHTML.replace(/\{\{index\}\}/g, newIndex);

    // 一時的なコンテナを作成
    const tempContainer = document.createElement('div');
    tempContainer.innerHTML = newRowHTML;

    // 新しい行を取得
    const newRow = tempContainer.firstElementChild;

    // 行をDOMに追加
    rowsContainer.appendChild(newRow);

    // 新しい行にイベントリスナーを設定
    setupLoopRowEvents(newRow, loopField);

    // フィールドの値を更新
    updateLoopFieldValue(loopField);

    // 行数表示を更新
    updateLoopCountDisplay(loopField);

    // 最大数に達した場合は追加ボタンを無効化
    checkLoopLimits(loopField);

    // カスタムイベントを発火
    loopField.dispatchEvent(new CustomEvent('cfse-row-added', {
      detail: {
        row: newRow,
        index: newIndex
      }
    }));
  }

  /**
   * ループフィールドの行にイベントリスナーを設定
   *
   * @param {HTMLElement} row 行要素
   * @param {HTMLElement} loopField ループフィールド要素
   */
  function setupLoopRowEvents(row, loopField) {
    // トグルボタン
    const toggleButton = row.querySelector('.cfse-loop-row-toggle');
    if (toggleButton) {
      toggleButton.addEventListener('click', function() {
        toggleLoopRow(row);
      });
    }

    // 削除ボタン
    const removeButton = row.querySelector('.cfse-loop-row-remove');
    if (removeButton) {
      removeButton.addEventListener('click', function() {
        removeLoopRow(row, loopField);
      });
    }

    // 入力フィールドの変更イベント
    const inputs = row.querySelectorAll('input, select, textarea');
    inputs.forEach(function(input) {
      input.addEventListener('change', function() {
        updateLoopFieldValue(loopField);
      });

      input.addEventListener('keyup', function() {
        updateLoopFieldValue(loopField);
      });
    });
  }

  /**
   * ループフィールドの行の表示/非表示を切り替え
   *
   * @param {HTMLElement} row 行要素
   */
  function toggleLoopRow(row) {
    const content = row.querySelector('.cfse-loop-row-content');
    const toggleButton = row.querySelector('.cfse-loop-row-toggle');

    if (content.classList.contains('cfse-loop-row-content-collapsed')) {
      // 展開
      content.classList.remove('cfse-loop-row-content-collapsed');
      toggleButton.innerHTML = '<span class="dashicons dashicons-arrow-down-alt2"></span>';
    } else {
      // 折りたたみ
      content.classList.add('cfse-loop-row-content-collapsed');
      toggleButton.innerHTML = '<span class="dashicons dashicons-arrow-right-alt2"></span>';
    }
  }

  /**
   * ループフィールドの行を削除
   *
   * @param {HTMLElement} row 行要素
   * @param {HTMLElement} loopField ループフィールド要素
   */
  function removeLoopRow(row, loopField) {
    // 確認ダイアログ
    const confirmMessage = CFSE.config.labels.confirmDelete || 'Are you sure you want to delete this item?';

    if (confirm(confirmMessage)) {
      // 行を削除
      row.parentNode.removeChild(row);

      // 行番号を振り直す
      reindexLoopRows(loopField);

      // フィールドの値を更新
      updateLoopFieldValue(loopField);

      // 行数表示を更新
      updateLoopCountDisplay(loopField);

      // 最大数に達した場合は追加ボタンを無効化
      checkLoopLimits(loopField);

      // カスタムイベントを発火
      loopField.dispatchEvent(new CustomEvent('cfse-row-removed'));
    }
  }

  /**
   * ループフィールドの行番号を振り直す
   *
   * @param {HTMLElement} loopField ループフィールド要素
   */
  function reindexLoopRows(loopField) {
    const rowsContainer = loopField.querySelector('.cfse-loop-rows');
    const rows = rowsContainer.querySelectorAll('.cfse-loop-row');

    rows.forEach(function(row, index) {
      // data-row-index属性を更新
      row.setAttribute('data-row-index', index);

      // 行ラベルを更新
      const rowLabel = row.querySelector('.cfse-loop-row-label');
      if (rowLabel) {
        // カスタムラベルフォーマットがあれば置換
        const rowLabelFormat = loopField.getAttribute('data-row-label-format');
        if (rowLabelFormat) {
          // フォーマットにインデックスを含める処理（必要であれば実装）
          // 今回はシンプルな実装で行番号のみ表示
          rowLabel.textContent = 'Row #' + (index + 1);
        } else {
          // デフォルトラベル
          rowLabel.textContent = CFSE.config.labels.rowLabel + ' #' + (index + 1);
        }
      }

      // 入力フィールドの名前を更新
      const inputs = row.querySelectorAll('input, select, textarea');
      inputs.forEach(function(input) {
        const name = input.getAttribute('name');
        if (name) {
          // 古いインデックスを新しいインデックスに置換
          const newName = name.replace(/\[\d+\]/, '[' + index + ']');
          input.setAttribute('name', newName);
        }

        const id = input.getAttribute('id');
        if (id) {
          // 古いインデックスを新しいインデックスに置換
          const newId = id.replace(/_\d+_/, '_' + index + '_');
          input.setAttribute('id', newId);
        }
      });
    });
  }

  /**
   * ループフィールドの値をJSONで更新
   *
   * @param {HTMLElement} loopField ループフィールド要素
   */
  function updateLoopFieldValue(loopField) {
    const rowsContainer = loopField.querySelector('.cfse-loop-rows');
    const rows = rowsContainer.querySelectorAll('.cfse-loop-row');
    const hiddenInput = loopField.querySelector('.cfse-loop-value');

    // 各行のデータを収集
    const rowsData = [];

    rows.forEach(function(row) {
      const rowData = {};
      const inputs = row.querySelectorAll('input:not([type="button"]):not([type="submit"]), select, textarea');

      inputs.forEach(function(input) {
        const name = input.getAttribute('name');

        // 名前からフィールドIDを抽出
        if (name) {
          const matches = name.match(/\[(\d+)\]\[([^\]]+)\]/);
          if (matches && matches.length === 3) {
            const fieldId = matches[2];

            // 値を取得
            let value;

            if (input.type === 'checkbox') {
              value = input.checked ? 1 : 0;
            } else if (input.type === 'radio') {
              if (input.checked) {
                value = input.value;
              } else {
                // ラジオボタンがチェックされていない場合はスキップ
                return;
              }
            } else {
              value = input.value;
            }

            // データに追加
            rowData[fieldId] = value;
          }
        }
      });

      rowsData.push(rowData);
    });

    // JSONに変換して隠しフィールドにセット
    if (hiddenInput) {
      hiddenInput.value = JSON.stringify(rowsData);
    }
  }

  /**
   * ループフィールドの行数表示を更新
   *
   * @param {HTMLElement} loopField ループフィールド要素
   */
  function updateLoopCountDisplay(loopField) {
    const rowsContainer = loopField.querySelector('.cfse-loop-rows');
    const rows = rowsContainer.querySelectorAll('.cfse-loop-row');
    const countDisplay = loopField.querySelector('.cfse-loop-count');

    if (countDisplay) {
      countDisplay.textContent = rows.length + ' rows';
    }
  }

  /**
   * ループフィールドの最小・最大制限をチェック
   *
   * @param {HTMLElement} loopField ループフィールド要素
   */
  function checkLoopLimits(loopField) {
    const rowsContainer = loopField.querySelector('.cfse-loop-rows');
    const rows = rowsContainer.querySelectorAll('.cfse-loop-row');
    const addButton = loopField.querySelector('.cfse-loop-add-row');
    const removeButtons = loopField.querySelectorAll('.cfse-loop-row-remove');

    // 最大数の制限をチェック
    const maxAttr = loopField.getAttribute('data-max');
    if (maxAttr && parseInt(maxAttr, 10) > 0) {
      const max = parseInt(maxAttr, 10);

      if (rows.length >= max) {
        // 最大数に達した場合は追加ボタンを無効化
        if (addButton) {
          addButton.disabled = true;
        }
      } else {
        // 最大数に達していない場合は追加ボタンを有効化
        if (addButton) {
          addButton.disabled = false;
        }
      }
    }

    // 最小数の制限をチェック
    const minAttr = loopField.getAttribute('data-min');
    if (minAttr && parseInt(minAttr, 10) > 0) {
      const min = parseInt(minAttr, 10);

      if (rows.length <= min) {
        // 最小数に達した場合は削除ボタンを無効化
        removeButtons.forEach(function(button) {
          button.disabled = true;
        });
      } else {
        // 最小数を超えている場合は削除ボタンを有効化
        removeButtons.forEach(function(button) {
          button.disabled = false;
        });
      }
    }
  }

  /**
   * ループフィールドの行の並び替えを設定
   *
   * @param {HTMLElement} rowsContainer 行コンテナ要素
   * @param {HTMLElement} loopField ループフィールド要素
   */
  function setupLoopRowsSorting(rowsContainer, loopField) {
    // Sortable.jsを使用（WordPressに標準で含まれているため）
    if (typeof Sortable !== 'undefined') {
      new Sortable(rowsContainer, {
        handle: '.cfse-loop-row-handle',
        animation: 150,
        onEnd: function() {
          // 並び替え後に行番号を振り直し
          reindexLoopRows(loopField);

          // フィールドの値を更新
          updateLoopFieldValue(loopField);
        }
      });
    } else {
      // Sortable.jsが利用できない場合はHTML5のDrag and Dropを実装
      setupNativeDragSort(rowsContainer, loopField);
    }
  }

  /**
   * リレーションシップの検索結果を表示
   *
   * @param {Array} results 検索結果
   * @param {HTMLElement} resultsArea 結果エリア要素
   * @param {HTMLElement} fieldWrapper フィールドラッパー要素
   */
  function renderRelationshipResults(results, resultsArea, fieldWrapper) {
    // 既に選択されているIDを取得
    const selectedArea = fieldWrapper.querySelector('.cfse-relationship-selected');
    const selectedItems = selectedArea ? selectedArea.querySelectorAll('.cfse-relationship-item') : [];
    const selectedIds = Array.from(selectedItems).map(item => item.getAttribute('data-id'));

    // 結果エリアをクリア
    resultsArea.innerHTML = '';

    if (results.length === 0) {
      resultsArea.innerHTML = '<p class="cfse-no-results">No results found</p>';
      return;
    }

    // 結果を表示
    results.forEach(function(result) {
      // 既に選択されている場合はスキップ
      if (selectedIds.includes(result.id.toString())) {
        return;
      }

      // 結果アイテムを作成
      const item = document.createElement('div');
      item.className = 'cfse-relationship-item';
      item.setAttribute('data-id', result.id);
      item.setAttribute('data-type', result.type);

      // タイトル
      const title = document.createElement('span');
      title.className = 'cfse-relationship-title';
      title.textContent = result.title;
      item.appendChild(title);

      // タイプ
      const type = document.createElement('span');
      type.className = 'cfse-relationship-type';
      type.textContent = result.type;
      item.appendChild(type);

      // 結果エリアに追加
      resultsArea.appendChild(item);
    });
  }

  /**
   * リレーションシップアイテムを選択
   *
   * @param {HTMLElement} item アイテム要素
   * @param {HTMLElement} selectedArea 選択エリア要素
   * @param {HTMLElement} hiddenInput 隠しフィールド要素
   */
  function selectRelationshipItem(item, selectedArea, hiddenInput) {
    // コピーを作成
    const newItem = item.cloneNode(true);

    // 削除ボタンを追加
    const removeButton = document.createElement('button');
    removeButton.type = 'button';
    removeButton.className = 'cfse-relationship-remove button';
    removeButton.innerHTML = '<span class="dashicons dashicons-no-alt"></span>';
    newItem.insertBefore(removeButton, newItem.firstChild);

    // ハンドルを追加
    const handle = document.createElement('span');
    handle.className = 'cfse-relationship-handle';
    handle.innerHTML = '<span class="dashicons dashicons-menu"></span>';
    newItem.insertBefore(handle, newItem.firstChild);

    // 選択エリアに追加
    selectedArea.appendChild(newItem);

    // 隠しフィールドの値を更新
    updateRelationshipValue(selectedArea, hiddenInput);

    // 元のアイテムを非表示
    item.style.display = 'none';
  }

  /**
   * リレーションシップアイテムの選択を解除
   *
   * @param {HTMLElement} item アイテム要素
   * @param {HTMLElement} selectedArea 選択エリア要素
   * @param {HTMLElement} hiddenInput 隠しフィールド要素
   */
  function unselectRelationshipItem(item, selectedArea, hiddenInput) {
    // 検索結果エリアの対応するアイテムを表示
    const itemId = item.getAttribute('data-id');
    const resultsArea = selectedArea.closest('.cfse-field-wrapper').querySelector('.cfse-relationship-results');
    const resultItem = resultsArea ? resultsArea.querySelector('.cfse-relationship-item[data-id="' + itemId + '"]') : null;

    if (resultItem) {
      resultItem.style.display = '';
    }

    // アイテムを削除
    selectedArea.removeChild(item);

    // 隠しフィールドの値を更新
    updateRelationshipValue(selectedArea, hiddenInput);
  }

  /**
   * リレーションシップの値を更新
   *
   * @param {HTMLElement} selectedArea 選択エリア要素
   * @param {HTMLElement} hiddenInput 隠しフィールド要素
   */
  function updateRelationshipValue(selectedArea, hiddenInput) {
    // 選択されているアイテムを取得
    const items = selectedArea.querySelectorAll('.cfse-relationship-item');

    // 値を配列に変換
    const values = Array.from(items).map(function(item) {
      return {
        id: item.getAttribute('data-id'),
        type: item.getAttribute('data-type')
      };
    });

    // 隠しフィールドに値をセット
    if (hiddenInput) {
      hiddenInput.value = JSON.stringify(values);
    }
  }

  /**
   * リレーションシップの並び替えを設定
   *
   * @param {HTMLElement} selectedArea 選択エリア要素
   * @param {HTMLElement} hiddenInput 隠しフィールド要素
   */
  function setupRelationshipSorting(selectedArea, hiddenInput) {
    // Sortable.jsを使用
    if (typeof Sortable !== 'undefined') {
      new Sortable(selectedArea, {
        handle: '.cfse-relationship-handle',
        animation: 150,
        onEnd: function() {
          // 並び替え後に値を更新
          updateRelationshipValue(selectedArea, hiddenInput);
        }
      });
    } else {
      // Sortable.jsが利用できない場合はHTML5のDrag and Dropを実装
      setupNativeDragSort(selectedArea, null, function() {
        updateRelationshipValue(selectedArea, hiddenInput);
      });
    }
  }

  /**
   * 条件付き表示を初期化
   */
  function initConditionalLogic() {
    // 条件付き表示を持つフィールドを全て取得
    const conditionalFields = document.querySelectorAll('[data-conditional-logic]');

    conditionalFields.forEach(function(field) {
      // 条件ロジックを取得
      let conditionalLogic;
      try {
        conditionalLogic = JSON.parse(field.getAttribute('data-conditional-logic'));
      } catch (e) {
        return;
      }

      // 条件の監視対象となるフィールドを取得
      const targetFields = getConditionalTargetFields(conditionalLogic);

      // 監視対象フィールドの変更イベントを監視
      targetFields.forEach(function(targetField) {
        const input = document.querySelector('[name$="[' + targetField + ']"]');
        if (input) {
          input.addEventListener('change', function() {
            checkConditionalLogic(field, conditionalLogic);
          });
        }
      });

      // 初期チェック
      checkConditionalLogic(field, conditionalLogic);
    });
  }

  /**
   * 条件付き表示の監視対象フィールドを取得
   *
   * @param {Object} conditionalLogic 条件ロジック
   * @return {Array} 監視対象フィールドの配列
   */
  function getConditionalTargetFields(conditionalLogic) {
    const targetFields = [];

    if (conditionalLogic && conditionalLogic.groups) {
      conditionalLogic.groups.forEach(function(group) {
        group.forEach(function(rule) {
          if (rule.field && !targetFields.includes(rule.field)) {
            targetFields.push(rule.field);
          }
        });
      });
    }

    return targetFields;
  }

  /**
   * 条件付き表示のチェック
   *
   * @param {HTMLElement} field フィールド要素
   * @param {Object} conditionalLogic 条件ロジック
   */
  function checkConditionalLogic(field, conditionalLogic) {
    // 条件を満たすかチェック
    const show = evalConditionalLogic(conditionalLogic);

    // 表示/非表示を切り替え
    if (show) {
      field.style.display = '';
    } else {
      field.style.display = 'none';
    }
  }

  /**
   * 条件ロジックを評価
   *
   * @param {Object} conditionalLogic 条件ロジック
   * @return {boolean} 条件を満たすかどうか
   */
  function evalConditionalLogic(conditionalLogic) {
    if (!conditionalLogic || !conditionalLogic.groups) {
      return true;
    }

    // アクション（デフォルトは「表示」）
    const action = conditionalLogic.action || 'show';

    // 各グループをOR条件で評価
    let result = false;

    for (let i = 0; i < conditionalLogic.groups.length; i++) {
      const group = conditionalLogic.groups[i];
      let groupResult = true;

      // 各ルールをAND条件で評価
      for (let j = 0; j < group.length; j++) {
        const rule = group[j];
        const fieldResult = evalConditionalRule(rule);

        if (!fieldResult) {
          groupResult = false;
          break;
        }
      }

      if (groupResult) {
        result = true;
        break;
      }
    }

    // アクションが「非表示」の場合は結果を反転
    return action === 'hide' ? !result : result;
  }

  /**
   * 条件ルールを評価
   *
   * @param {Object} rule ルール
   * @return {boolean} ルールを満たすかどうか
   */
  function evalConditionalRule(rule) {
    // ルールが不完全な場合は真を返す
    if (!rule || !rule.field || !rule.operator) {
      return true;
    }

    // フィールドの値を取得
    const fieldValue = getFieldValue(rule.field);

    // 演算子に応じて評価
    switch (rule.operator) {
      case '==':
        return fieldValue == rule.value;
      case '!=':
        return fieldValue != rule.value;
      case '>':
        return parseFloat(fieldValue) > parseFloat(rule.value);
      case '<':
        return parseFloat(fieldValue) < parseFloat(rule.value);
      case 'contains':
        return String(fieldValue).indexOf(rule.value) !== -1;
      case 'not_contains':
        return String(fieldValue).indexOf(rule.value) === -1;
      case 'empty':
        return fieldValue === '' || fieldValue === null || fieldValue === undefined;
      case 'not_empty':
        return fieldValue !== '' && fieldValue !== null && fieldValue !== undefined;
      default:
        return true;
    }
  }

  /**
   * フィールドの値を取得
   *
   * @param {string} fieldId フィールドID
   * @return {mixed} フィールドの値
   */
  function getFieldValue(fieldId) {
    // 入力フィールドを取得
    const input = document.querySelector('[name$="[' + fieldId + ']"]');

    if (!input) {
      return null;
    }

    // 入力タイプに応じて値を取得
    if (input.type === 'checkbox') {
      return input.checked ? input.value : '';
    } else if (input.type === 'radio') {
      const checkedRadio = document.querySelector('[name$="[' + fieldId + ']"]:checked');
      return checkedRadio ? checkedRadio.value : '';
    } else if (input.tagName.toLowerCase() === 'select') {
      return input.options[input.selectedIndex].value;
    } else {
      return input.value;
    }
  }

  /**
   * ユーティリティ関数: debounce
   *
   * @param {Function} func 関数
   * @param {number} wait 待機時間（ミリ秒）
   * @return {Function} debounce処理された関数
   */
  function debounce(func, wait) {
    let timeout;

    return function() {
      const context = this;
      const args = arguments;

      const later = function() {
        timeout = null;
        func.apply(context, args);
      };

      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }

  // グローバル変数にエクスポート
  window.CFSE = CFSE;
})();
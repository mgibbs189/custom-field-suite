/**
 * Custom Field Suite - Date Module
 * ES Module implementation for date fields
 *
 * @package CFS
 * @version 3.0.0
 */

import { qs, qsa, addClass, removeClass, fadeIn, fadeOut } from './utils.js';

/**
 * 日付フィールドの管理クラス
 */
class CFSDate {
  /**
   * コンストラクタ
   */
  constructor() {
    // 内部状態
    this.initialized = false;
    this.activeField = null;
    this.datepicker = null;
    this.container = null;
    this.selectedDate = null;

    // 設定
    this.options = {
      dateFormat: 'YYYY-MM-DD',
      firstDay: 0, // 0 = 日曜日から開始
      monthNames: [
        '1月', '2月', '3月', '4月', '5月', '6月',
        '7月', '8月', '9月', '10月', '11月', '12月'
      ],
      monthNamesShort: [
        '1月', '2月', '3月', '4月', '5月', '6月',
        '7月', '8月', '9月', '10月', '11月', '12月'
      ],
      dayNames: ['日', '月', '火', '水', '木', '金', '土'],
      dayNamesShort: ['日', '月', '火', '水', '木', '金', '土'],
      dayNamesMin: ['日', '月', '火', '水', '木', '金', '土'],
      prevText: '前',
      nextText: '次',
      currentText: '今日',
      closeText: '閉じる',
      clearText: 'クリア'
    };

    // メソッドバインド
    this.init = this.init.bind(this);
    this.initFields = this.initFields.bind(this);
    this.initDatepicker = this.initDatepicker.bind(this);
    this.onInputClick = this.onInputClick.bind(this);
    this.onSelectDate = this.onSelectDate.bind(this);
    this.onClearDate = this.onClearDate.bind(this);
    this.onClickOutside = this.onClickOutside.bind(this);
    this.closeDatepicker = this.closeDatepicker.bind(this);
    this.formatDate = this.formatDate.bind(this);
    this.parseDate = this.parseDate.bind(this);
    this.isSameDay = this.isSameDay.bind(this);
  }

  /**
   * 初期化処理
   * @param {Object} [customOptions] - カスタム設定
   */
  init(customOptions = {}) {
    // カスタム設定のマージ
    this.options = { ...this.options, ...customOptions };

    // WordPressのローカライズされた日付設定を適用（存在する場合）
    if (typeof window.CFS !== 'undefined' && window.CFS.date_settings) {
      this.options = { ...this.options, ...window.CFS.date_settings };
    }

    // デートピッカーコンテナの作成
    this.initDatepicker();

    // 既存フィールドの初期化
    this.initFields();

    // 動的に追加されたフィールドを監視
    document.addEventListener('cfs:row_added', this.handleRowAdded.bind(this));

    // 外部クリックのイベントリスナー
    document.addEventListener('click', this.onClickOutside);

    // イベント発火 - 初期化完了
    const event = new CustomEvent('cfs:date-init');
    document.dispatchEvent(event);

    this.initialized = true;
  }

  /**
   * 既存の日付フィールドの初期化
   */
  initFields() {
    const dateFields = qsa('.cfs_date:not(.cfs-date-initialized)');

    dateFields.forEach(field => {
      const input = qs('input[type="text"]', field);
      if (input) {
        // 入力フィールドにクリックイベントを追加
        input.addEventListener('click', this.onInputClick);

        // クラスを追加して初期化済みとしてマーク
        addClass(field, 'cfs-date-initialized');
      }
    });
  }

  /**
   * デートピッカーコンテナの初期化
   */
  initDatepicker() {
    // すでに存在する場合は何もしない
    if (this.container) return;

    // コンテナ作成
    this.container = document.createElement('div');
    this.container.className = 'cfs-datepicker';
    this.container.style.display = 'none';
    document.body.appendChild(this.container);

    // インスタンス変数としてデートピッカーを保持
    this.datepicker = new CFSDatepicker({
      onChange: this.onSelectDate,
      onClear: this.onClearDate,
      firstDay: this.options.firstDay,
      monthNames: this.options.monthNames,
      monthNamesShort: this.options.monthNamesShort,
      dayNames: this.options.dayNames,
      dayNamesShort: this.options.dayNamesShort,
      dayNamesMin: this.options.dayNamesMin,
      prevText: this.options.prevText,
      nextText: this.options.nextText,
      currentText: this.options.currentText,
      closeText: this.options.closeText,
      clearText: this.options.clearText
    });

    // デートピッカーをコンテナに追加
    this.container.appendChild(this.datepicker.element);
  }

  /**
   * 入力フィールドクリック時のハンドラ
   * @param {Event} e - クリックイベント
   */
  onInputClick(e) {
    e.preventDefault();
    e.stopPropagation();

    const input = e.target;
    const fieldWrapper = input.closest('.cfs_date');

    if (!fieldWrapper || !this.datepicker) return;

    // すでにこのフィールドがアクティブならデートピッカーを閉じる
    if (this.activeField === input) {
      this.closeDatepicker();
      return;
    }

    // 異なるフィールドがアクティブならいったん閉じる
    if (this.activeField) {
      this.closeDatepicker();
    }

    // 現在の入力値を取得
    const dateValue = input.value.trim();

    // 入力値があれば日付としてパース
    if (dateValue) {
      const date = this.parseDate(dateValue);
      if (date && !isNaN(date.getTime())) {
        this.selectedDate = date;
        this.datepicker.setDate(this.selectedDate);
      } else {
        this.selectedDate = null;
        this.datepicker.setDate(null);
      }
    } else {
      this.selectedDate = null;
      this.datepicker.setDate(null);
    }

    // アクティブフィールドを設定
    this.activeField = input;

    // 入力フィールドの位置に合わせてデートピッカーを配置
    const rect = input.getBoundingClientRect();
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;

    this.container.style.top = (rect.bottom + scrollTop) + 'px';
    this.container.style.left = (rect.left + scrollLeft) + 'px';

    // デートピッカーを表示
    this.container.style.display = 'block';

    // フォーカス設定
    setTimeout(() => {
      this.datepicker.focus();
    }, 0);
  }

  /**
   * 日付選択時のコールバック
   * @param {Date} date - 選択された日付
   */
  onSelectDate(date) {
    if (!this.activeField) return;

    // 日付を設定
    this.selectedDate = date;

    // 入力フィールドに反映
    this.activeField.value = this.formatDate(date);

    // 変更イベントを発火して条件ロジックなどを更新
    const changeEvent = new Event('change', { bubbles: true });
    this.activeField.dispatchEvent(changeEvent);

    // デートピッカーを閉じる
    this.closeDatepicker();
  }

  /**
   * クリアボタン押下時のコールバック
   */
  onClearDate() {
    if (!this.activeField) return;

    // 値をクリア
    this.activeField.value = '';
    this.selectedDate = null;

    // 変更イベントを発火して条件ロジックなどを更新
    const changeEvent = new Event('change', { bubbles: true });
    this.activeField.dispatchEvent(changeEvent);

    // デートピッカーを閉じる
    this.closeDatepicker();
  }

  /**
   * デートピッカー外クリック時のハンドラ
   * @param {Event} e - クリックイベント
   */
  onClickOutside(e) {
    // デートピッカーが開かれていない場合は何もしない
    if (!this.container || this.container.style.display === 'none') {
      return;
    }

    // クリックがデートピッカー内部またはアクティブな入力フィールドの場合は何もしない
    if (
      this.container.contains(e.target) ||
      (this.activeField && (this.activeField === e.target || this.activeField.contains(e.target)))
    ) {
      return;
    }

    // それ以外の場所のクリックならデートピッカーを閉じる
    this.closeDatepicker();
  }

  /**
   * デートピッカーを閉じる
   */
  closeDatepicker() {
    if (this.container) {
      this.container.style.display = 'none';
    }
    this.activeField = null;
  }

  /**
   * 動的に追加された行のハンドラ
   * @param {CustomEvent} e - イベント
   */
  handleRowAdded(e) {
    if (e.detail && e.detail.row) {
      const newDateFields = qsa('.cfs_date:not(.cfs-date-initialized)', e.detail.row);

      if (newDateFields.length > 0) {
        newDateFields.forEach(field => {
          const input = qs('input[type="text"]', field);
          if (input) {
            // 入力フィールドにクリックイベントを追加
            input.addEventListener('click', this.onInputClick);

            // クラスを追加して初期化済みとしてマーク
            addClass(field, 'cfs-date-initialized');
          }
        });
      }
    }
  }

  /**
   * 日付を指定されたフォーマットに変換
   * @param {Date} date - 日付オブジェクト
   * @return {string} フォーマットされた日付文字列
   */
  formatDate(date) {
    if (!date || isNaN(date.getTime())) {
      return '';
    }

    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    let result = this.options.dateFormat;

    // 基本的なフォーマット置換
    result = result.replace('YYYY', year);
    result = result.replace('MM', month);
    result = result.replace('DD', day);

    // 月名（長い形式）
    if (result.includes('MMMM')) {
      result = result.replace('MMMM', this.options.monthNames[date.getMonth()]);
    }

    // 月名（短い形式）
    if (result.includes('MMM')) {
      result = result.replace('MMM', this.options.monthNamesShort[date.getMonth()]);
    }

    // 曜日名（長い形式）
    if (result.includes('dddd')) {
      result = result.replace('dddd', this.options.dayNames[date.getDay()]);
    }

    // 曜日名（短い形式）
    if (result.includes('ddd')) {
      result = result.replace('ddd', this.options.dayNamesShort[date.getDay()]);
    }

    return result;
  }

  /**
   * 文字列から日付オブジェクトをパース
   * @param {string} dateStr - 日付文字列
   * @return {Date|null} 日付オブジェクトまたはnull
   */
  parseDate(dateStr) {
    if (!dateStr) return null;

    // ISO形式の日付（YYYY-MM-DD）からのパース
    const isoMatch = dateStr.match(/^(\d{4})-(\d{1,2})-(\d{1,2})$/);
    if (isoMatch) {
      const year = parseInt(isoMatch[1], 10);
      const month = parseInt(isoMatch[2], 10) - 1; // 月は0-11
      const day = parseInt(isoMatch[3], 10);
      return new Date(year, month, day);
    }

    // MM/DD/YYYY形式からのパース
    const usMatch = dateStr.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
    if (usMatch) {
      const month = parseInt(usMatch[1], 10) - 1; // 月は0-11
      const day = parseInt(usMatch[2], 10);
      const year = parseInt(usMatch[3], 10);
      return new Date(year, month, day);
    }

    // DD.MM.YYYY形式からのパース
    const euMatch = dateStr.match(/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/);
    if (euMatch) {
      const day = parseInt(euMatch[1], 10);
      const month = parseInt(euMatch[2], 10) - 1; // 月は0-11
      const year = parseInt(euMatch[3], 10);
      return new Date(year, month, day);
    }

    // その他のフォーマットはDateコンストラクタに任せる
    const date = new Date(dateStr);
    if (!isNaN(date.getTime())) {
      return date;
    }

    return null;
  }

  /**
   * 2つの日付が同じ日かどうかをチェック
   * @param {Date} date1 - 日付1
   * @param {Date} date2 - 日付2
   * @return {boolean} 同じ日ならtrue
   */
  isSameDay(date1, date2) {
    if (!date1 || !date2) return false;

    return (
      date1.getFullYear() === date2.getFullYear() &&
      date1.getMonth() === date2.getMonth() &&
      date1.getDate() === date2.getDate()
    );
  }
}

/**
 * デートピッカーコンポーネント
 */
class CFSDatepicker {
  /**
   * コンストラクタ
   * @param {Object} options - デートピッカーオプション
   */
  constructor(options = {}) {
    this.options = {
      onChange: null,
      onClear: null,
      firstDay: 0,
      monthNames: [
        '1月', '2月', '3月', '4月', '5月', '6月',
        '7月', '8月', '9月', '10月', '11月', '12月'
      ],
      monthNamesShort: [
        '1月', '2月', '3月', '4月', '5月', '6月',
        '7月', '8月', '9月', '10月', '11月', '12月'
      ],
      dayNames: ['日', '月', '火', '水', '木', '金', '土'],
      dayNamesShort: ['日', '月', '火', '水', '木', '金', '土'],
      dayNamesMin: ['日', '月', '火', '水', '木', '金', '土'],
      prevText: '前',
      nextText: '次',
      currentText: '今日',
      closeText: '閉じる',
      clearText: 'クリア',
      ...options
    };

    // 内部状態
    this.currentDate = new Date();
    this.selectedDate = null;
    this.focused = false;

    // 要素の作成
    this.element = this.createDatepickerElement();
  }

  /**
   * デートピッカー要素の作成
   * @return {HTMLElement} デートピッカー要素
   */
  createDatepickerElement() {
    const container = document.createElement('div');
    container.className = 'cfs-datepicker-container';

    // ヘッダー部分（月/年表示と移動ボタン）
    const header = document.createElement('div');
    header.className = 'cfs-datepicker-header';

    // 前月ボタン
    const prevButton = document.createElement('button');
    prevButton.type = 'button';
    prevButton.className = 'cfs-datepicker-prev';
    prevButton.innerHTML = this.options.prevText;
    prevButton.addEventListener('click', () => this.prevMonth());

    // 現在の月/年表示
    const titleDiv = document.createElement('div');
    titleDiv.className = 'cfs-datepicker-title';

    // 次月ボタン
    const nextButton = document.createElement('button');
    nextButton.type = 'button';
    nextButton.className = 'cfs-datepicker-next';
    nextButton.innerHTML = this.options.nextText;
    nextButton.addEventListener('click', () => this.nextMonth());

    header.appendChild(prevButton);
    header.appendChild(titleDiv);
    header.appendChild(nextButton);

    // カレンダー部分
    const calendar = document.createElement('div');
    calendar.className = 'cfs-datepicker-calendar';

    // フッター部分（ボタン）
    const footer = document.createElement('div');
    footer.className = 'cfs-datepicker-footer';

    // 今日ボタン
    const todayButton = document.createElement('button');
    todayButton.type = 'button';
    todayButton.className = 'cfs-datepicker-today';
    todayButton.innerHTML = this.options.currentText;
    todayButton.addEventListener('click', () => this.goToToday());

    // クリアボタン
    const clearButton = document.createElement('button');
    clearButton.type = 'button';
    clearButton.className = 'cfs-datepicker-clear';
    clearButton.innerHTML = this.options.clearText;
    clearButton.addEventListener('click', () => {
      if (this.options.onClear && typeof this.options.onClear === 'function') {
        this.options.onClear();
      }
    });

    // 閉じるボタン
    const closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.className = 'cfs-datepicker-close';
    closeButton.innerHTML = this.options.closeText;
    closeButton.addEventListener('click', () => {
      if (this.options.onClose && typeof this.options.onClose === 'function') {
        this.options.onClose();
      }
    });

    footer.appendChild(todayButton);
    footer.appendChild(clearButton);
    footer.appendChild(closeButton);

    // コンテナに要素を追加
    container.appendChild(header);
    container.appendChild(calendar);
    container.appendChild(footer);

    // カレンダーを更新
    this.updateCalendar(container);

    return container;
  }

  /**
   * カレンダー表示を更新
   * @param {HTMLElement} [container=this.element] - デートピッカーコンテナ
   */
  updateCalendar(container = this.element) {
    // タイトル部分の更新
    const titleDiv = container.querySelector('.cfs-datepicker-title');
    const year = this.currentDate.getFullYear();
    const month = this.currentDate.getMonth();
    titleDiv.textContent = `${this.options.monthNames[month]} ${year}`;

    // カレンダー部分の更新
    const calendarDiv = container.querySelector('.cfs-datepicker-calendar');
    calendarDiv.innerHTML = '';

    // テーブル要素
    const table = document.createElement('table');
    table.className = 'cfs-datepicker-table';

    // テーブルヘッダー（曜日）
    const thead = document.createElement('thead');
    const headerRow = document.createElement('tr');

    // 週始めの曜日を考慮して曜日を設定
    for (let i = 0; i < 7; i++) {
      const dayIndex = (i + this.options.firstDay) % 7;
      const th = document.createElement('th');
      th.textContent = this.options.dayNamesMin[dayIndex];
      th.title = this.options.dayNames[dayIndex];
      headerRow.appendChild(th);
    }

    thead.appendChild(headerRow);
    table.appendChild(thead);

    // テーブル本体（日付）
    const tbody = document.createElement('tbody');

    // 月の最初の日の曜日を取得
    const firstDay = new Date(year, month, 1).getDay();

    // 月の最終日を取得
    const lastDate = new Date(year, month + 1, 0).getDate();

    // 前月の最終日を取得
    const prevMonthLastDate = new Date(year, month, 0).getDate();

    // 現在の日付
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    // 曜日のオフセット（週始めの曜日を考慮）
    const dayOffset = (firstDay - this.options.firstDay + 7) % 7;

    // カレンダーの行を作成
    let date = 1;
    let nextMonthDate = 1;

    // 6週間分のカレンダーを表示
    for (let week = 0; week < 6; week++) {
      const row = document.createElement('tr');

      // 各曜日のセルを作成
      for (let day = 0; day < 7; day++) {
        const cell = document.createElement('td');

        // 月の開始日より前の場合は前月の日付を表示
        if (week === 0 && day < dayOffset) {
          const prevDate = prevMonthLastDate - dayOffset + day + 1;
          cell.textContent = prevDate;
          cell.className = 'cfs-datepicker-other-month';
        }
        // 月の最終日より後の場合は翌月の日付を表示
        else if (date > lastDate) {
          cell.textContent = nextMonthDate++;
          cell.className = 'cfs-datepicker-other-month';
        }
        // 当月の日付を表示
        else {
          cell.textContent = date;

          // 現在の日付かどうかをチェック
          const currentDateObj = new Date(year, month, date);

          // 今日の日付かどうか
          if (
            currentDateObj.getFullYear() === today.getFullYear() &&
            currentDateObj.getMonth() === today.getMonth() &&
            currentDateObj.getDate() === today.getDate()
          ) {
            cell.classList.add('cfs-datepicker-today');
          }

          // 選択された日付かどうか
          if (
            this.selectedDate &&
            currentDateObj.getFullYear() === this.selectedDate.getFullYear() &&
            currentDateObj.getMonth() === this.selectedDate.getMonth() &&
            currentDateObj.getDate() === this.selectedDate.getDate()
          ) {
            cell.classList.add('cfs-datepicker-selected');
          }

          // セルにクリックイベントを追加
          cell.addEventListener('click', () => {
            const selectedDate = new Date(year, month, parseInt(cell.textContent, 10));
            this.selectDate(selectedDate);
          });

          date++;
        }

        row.appendChild(cell);
      }

      tbody.appendChild(row);

      // すべての日付を表示し終えたら終了
      if (date > lastDate && week >= 3) {
        break;
      }
    }

    table.appendChild(tbody);
    calendarDiv.appendChild(table);
  }

  /**
   * 前月へ移動
   */
  prevMonth() {
    this.currentDate.setMonth(this.currentDate.getMonth() - 1);
    this.updateCalendar();
  }

  /**
   * 次月へ移動
   */
  nextMonth() {
    this.currentDate.setMonth(this.currentDate.getMonth() + 1);
    this.updateCalendar();
  }

  /**
   * 今日の日付へ移動
   */
  goToToday() {
    this.currentDate = new Date();
    this.updateCalendar();
    this.selectDate(new Date());
  }

  /**
   * 日付を選択
   * @param {Date} date - 選択する日付
   */
  selectDate(date) {
    this.selectedDate = date;
    this.updateCalendar();

    if (this.options.onChange && typeof this.options.onChange === 'function') {
      this.options.onChange(date);
    }
  }

  /**
   * 日付を設定
   * @param {Date} date - 設定する日付
   */
  setDate(date) {
    if (date) {
      this.currentDate = new Date(date.getFullYear(), date.getMonth(), 1);
      this.selectedDate = new Date(date);
    } else {
      this.currentDate = new Date();
      this.selectedDate = null;
    }

    this.updateCalendar();
  }

  /**
   * フォーカスを設定
   */
  focus() {
    this.focused = true;
    const firstButton = this.element.querySelector('button');
    if (firstButton) {
      firstButton.focus();
    }
  }
}

// インスタンス作成
const dateField = new CFSDate();

// DOMコンテンツロード完了時に初期化
document.addEventListener('DOMContentLoaded', dateField.init);

// グローバルAPIとしても公開
if (typeof window.CFS === 'object') {
  window.CFS.dateField = dateField;
}

// エクスポート
export default dateField;
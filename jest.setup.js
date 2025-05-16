// WordPress グローバル変数のモック
global.wp = {
  ajax: {
    post: jest.fn().mockImplementation((data, callback) => {
      // Ajax レスポンスのモック
      callback({ success: true });
    })
  },
  media: {
    view: {
      MediaFrame: {
        Select: jest.fn().mockImplementation(() => ({
          on: jest.fn(),
          open: jest.fn()
        }))
      }
    }
  }
};

// WordPress ローカライズデータのモック
global.CFS = {
  ajax_url: 'http://example.com/wp-admin/admin-ajax.php',
  nonce: 'test_nonce',
  assets_url: 'http://example.com/wp-content/plugins/custom-field-suite/assets/',
  text: {
    add_rule: '条件を追加',
    remove: '削除',
    loading: '読み込み中...',
    confirm_remove: '本当に削除しますか？',
    select_file: 'ファイルを選択',
    select_files: 'ファイルを選択',
    select_image: '画像を選択',
    select_images: '画像を選択'
  }
};

// requestAnimationFrameのモック
window.requestAnimationFrame = jest.fn(callback => {
  setTimeout(() => callback(Date.now()), 0);
  return 123; // ID
});

// tinymce グローバルオブジェクトのモック
global.tinymce = {
  init: jest.fn(),
  get: jest.fn(),
  editors: []
};

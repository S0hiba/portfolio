/* 更新種別マスタテーブル 初期データのインサート */
INSERT INTO portfolio.log_tag_master(log_tag_name, log_tag_icon)
VALUES('その他', 'bullhorn'),
      ('作品', 'laptop-code'),
      ('ブログ', 'book');

/* ブログ記事種別マスタテーブル 初期データのインサート */
INSERT INTO portfolio.article_tag_master(article_tag_name, article_sort_no)
VALUES('未分類', 1000);

/* 技術種別マスタテーブル 初期データのインサート */
INSERT INTO portfolio.technology_tag_master(technology_tag_name, technology_tag_sort_no)
VALUES('その他', 1000),
      ('Web(バックエンド)', 1),
      ('Web(インフラ)', 2),
      ('Web(フロントエンド)', 3);

/* 操作種別マスタテーブル 初期データのインサート */
INSERT INTO portfolio.action_type_master(action_type_name)
VALUES('login'),
      ('logout'),
      ('account_lock'),
      ('important_data_access'),
      ('db_insert'),
      ('db_update'),
      ('db_delete');

/* portfolioスキーマ作成 */
CREATE SCHEMA portfolio;

/* 更新履歴テーブル作成 */
CREATE TABLE portfolio.update_log(
    log_id SERIAL PRIMARY KEY,
    log_stamp TIMESTAMP NOT NULL,
    log_tag_id INTEGER NOT NULL,
    log_text TEXT NOT NULL,
    log_target_id INTEGER,
    log_link TEXT
);

/* 更新履歴テーブル インデックス作成 */
CREATE INDEX ON portfolio.update_log(log_tag_id);
CREATE INDEX ON portfolio.update_log(log_target_id);

/* 更新履歴テーブル コメント記述 */
COMMENT ON TABLE portfolio.update_log IS '更新履歴';
COMMENT ON COLUMN portfolio.update_log.log_id IS '更新履歴ID';
COMMENT ON COLUMN portfolio.update_log.log_stamp IS '更新日';
COMMENT ON COLUMN portfolio.update_log.log_tag_id IS '更新種別ID(log_tag_masterを参照)';
COMMENT ON COLUMN portfolio.update_log.log_text IS '更新内容';
COMMENT ON COLUMN portfolio.update_log.log_target_id IS '更新対象ID(更新対象のテーブルを参照)';
COMMENT ON COLUMN portfolio.update_log.log_link IS '更新対象URL';

/* 更新種別マスタテーブル作成 */
CREATE TABLE portfolio.log_tag_master(
    log_tag_id SERIAL PRIMARY KEY,
    log_tag_name TEXT NOT NULL,
    log_tag_icon TEXT NOT NULL
);

/* 更新種別マスタテーブル コメント記述 */
COMMENT ON TABLE portfolio.log_tag_master IS '更新種別マスタ';
COMMENT ON COLUMN portfolio.log_tag_master.log_tag_id IS '更新種別ID';
COMMENT ON COLUMN portfolio.log_tag_master.log_tag_name IS '更新種別名';
COMMENT ON COLUMN portfolio.log_tag_master.log_tag_icon IS '更新種別アイコン名(font awesome)';

/* ブログ記事テーブル作成 */
CREATE TABLE portfolio.blog_article(
    article_id SERIAL PRIMARY KEY,
    article_title TEXT NOT NULL,
    article_stamp TIMESTAMP NOT NULL,
    article_tag_id INTEGER NOT NULL,
    article_text TEXT NOT NULL
);

/* ブログ記事テーブル インデックス作成 */
CREATE INDEX ON portfolio.blog_article(article_tag_id);

/* ブログ記事テーブル コメント記述 */
COMMENT ON TABLE portfolio.blog_article IS 'ブログ記事';
COMMENT ON COLUMN portfolio.blog_article.article_id IS '記事ID';
COMMENT ON COLUMN portfolio.blog_article.article_title IS '記事タイトル';
COMMENT ON COLUMN portfolio.blog_article.article_stamp IS '記事作成日';
COMMENT ON COLUMN portfolio.blog_article.article_tag_id IS '記事種別ID(article_tag_masterを参照)';
COMMENT ON COLUMN portfolio.blog_article.article_text IS '記事本文';

/* ブログ記事種別マスタテーブル作成 */
CREATE TABLE portfolio.article_tag_master(
    article_tag_id SERIAL PRIMARY KEY,
    article_tag_name TEXT NOT NULL,
    article_sort_no INTEGER NOT NULL DEFAULT 999
);

/* ブログ記事種別マスタテーブル コメント記述 */
COMMENT ON TABLE portfolio.article_tag_master IS 'ブログ記事種別マスタ';
COMMENT ON COLUMN portfolio.article_tag_master.article_tag_id IS '記事種別ID';
COMMENT ON COLUMN portfolio.article_tag_master.article_tag_name IS '記事種別名';
COMMENT ON COLUMN portfolio.article_tag_master.article_sort_no IS '記事種別表示順';

/* ブログコメントテーブル作成 */
CREATE TABLE portfolio.blog_comment(
    comment_id SERIAL PRIMARY KEY,
    article_id INTEGER NOT NULL,
    comment_title TEXT NOT NULL,
    comment_stamp TIMESTAMP NOT NULL,
    comment_user TEXT NOT NULL,
    comment_text TEXT NOT NULL
);

/* ブログコメントテーブル インデックス作成 */
CREATE INDEX ON portfolio.blog_comment(article_id);

/* ブログコメントテーブル コメント記述 */
COMMENT ON TABLE portfolio.blog_comment IS 'ブログコメント';
COMMENT ON COLUMN portfolio.blog_comment.article_id IS 'コメント対象記事ID(blog_articleを参照)';
COMMENT ON COLUMN portfolio.blog_comment.comment_id IS 'コメントID';
COMMENT ON COLUMN portfolio.blog_comment.comment_title IS 'コメントタイトル';
COMMENT ON COLUMN portfolio.blog_comment.comment_stamp IS 'コメント日';
COMMENT ON COLUMN portfolio.blog_comment.comment_user IS 'コメント者';
COMMENT ON COLUMN portfolio.blog_comment.comment_text IS 'コメント本文';

/* カウンタログテーブル作成 */
CREATE TABLE portfolio.counter_log(
    counter_url TEXT NOT NULL,
    counter_stamp TIMESTAMP NOT NULL
);

/* カウンタログテーブル コメント記述 */
COMMENT ON TABLE portfolio.counter_log IS 'カウンタログ';
COMMENT ON COLUMN portfolio.counter_log.counter_url IS 'カウント対象ページURL';
COMMENT ON COLUMN portfolio.counter_log.counter_stamp IS 'カウント日';

/* カウンタ集計テーブル作成 */
CREATE TABLE portfolio.counter_summary(
    counter_url TEXT,
    counter_stamp TIMESTAMP,
    counter_num INTEGER NOT NULL,
    PRIMARY KEY(counter_url, counter_stamp)
);

/* カウンタ集計テーブル コメント記述 */
COMMENT ON TABLE portfolio.counter_summary IS 'カウンタ集計';
COMMENT ON COLUMN portfolio.counter_summary.counter_url IS 'カウント対象ページURL';
COMMENT ON COLUMN portfolio.counter_summary.counter_stamp IS 'カウント日';
COMMENT ON COLUMN portfolio.counter_summary.counter_num IS 'カウント数';

/* 操作ログテーブル作成 */
CREATE TABLE portfolio.action_log(
    action_stamp TIMESTAMP NOT NULL,
    action_url TEXT NOT NULL,
    action_type_id INTEGER NOT NULL,
    action_result INTEGER NOT NULL,
    ip_address TEXT,
    action_db_table TEXT,
    action_data_id INTEGER
);

/* 操作ログテーブル コメント記述 */
COMMENT ON TABLE portfolio.action_log IS '操作ログ';
COMMENT ON COLUMN portfolio.action_log.action_stamp IS '操作日';
COMMENT ON COLUMN portfolio.action_log.action_url IS '操作URL';
COMMENT ON COLUMN portfolio.action_log.action_type_id IS '操作種別ID(action_type_masterを参照)';
COMMENT ON COLUMN portfolio.action_log.action_result IS '操作結果(1:成功、2:失敗)';
COMMENT ON COLUMN portfolio.action_log.ip_address IS '操作者のIPアドレス';
COMMENT ON COLUMN portfolio.action_log.action_db_table IS '操作対象テーブル';
COMMENT ON COLUMN portfolio.action_log.action_data_id IS '操作対象ID(操作対象テーブルを参照)';

/* 操作種別マスタテーブル作成 */
CREATE TABLE portfolio.action_type_master(
    action_type_id SERIAL PRIMARY KEY,
    action_type_name TEXT NOT NULL
);

/* 操作種別マスタテーブル コメント記述 */
COMMENT ON TABLE portfolio.action_type_master IS '操作種別マスタ';
COMMENT ON COLUMN portfolio.action_type_master.action_type_id IS '操作種別ID';
COMMENT ON COLUMN portfolio.action_type_master.action_type_name IS '操作種別名';

/* 作品概要テーブル作成 */
CREATE TABLE portfolio.work_overview(
    work_id SERIAL PRIMARY KEY,
    work_name TEXT NOT NULL,
    work_code TEXT NOT NULL,
    work_url TEXT NOT NULL,
    work_text TEXT NOT NULL,
    work_sort_no INTEGER NOT NULL DEFAULT 999
);

/* 作品概要テーブル コメント記述 */
COMMENT ON TABLE portfolio.work_overview IS '作品概要';
COMMENT ON COLUMN portfolio.work_overview.work_id IS '作品ID';
COMMENT ON COLUMN portfolio.work_overview.work_name IS '作品名';
COMMENT ON COLUMN portfolio.work_overview.work_code IS '作品コード';
COMMENT ON COLUMN portfolio.work_overview.work_url IS '作品URL';
COMMENT ON COLUMN portfolio.work_overview.work_text IS '作品の説明';
COMMENT ON COLUMN portfolio.work_overview.work_sort_no IS '作品表示順';

/* 技術マスタテーブル作成 */
CREATE TABLE portfolio.technology_master(
    technology_id SERIAL PRIMARY KEY,
    technology_name TEXT NOT NULL,
    technology_tag_id INTEGER NOT NULL
);

/* 技術マスタテーブル コメント記述 */
COMMENT ON TABLE portfolio.technology_master IS '技術マスタ';
COMMENT ON COLUMN portfolio.technology_master.technology_id IS '技術ID';
COMMENT ON COLUMN portfolio.technology_master.technology_name IS '技術名';
COMMENT ON COLUMN portfolio.technology_master.technology_tag_id IS '技術種別ID(technology_tag_masterを参照)';

/* 技術種別マスタテーブル作成 */
CREATE TABLE portfolio.technology_tag_master(
    technology_tag_id SERIAL PRIMARY KEY,
    technology_tag_name TEXT NOT NULL,
    technology_tag_sort_no INTEGER NOT NULL DEFAULT 999
);

/* 技術種別マスタテーブル */
COMMENT ON TABLE portfolio.technology_tag_master IS '技術種別マスタ';
COMMENT ON COLUMN portfolio.technology_tag_master.technology_tag_id IS '技術種別ID';
COMMENT ON COLUMN portfolio.technology_tag_master.technology_tag_name IS '技術種別名';
COMMENT ON COLUMN portfolio.technology_tag_master.technology_tag_sort_no IS '技術種別表示順';

/* 作品概要-技術マスタ 中間テーブル作成 */
CREATE TABLE portfolio.work_technology(
    work_id INTEGER,
    technology_id INTEGER,
    PRIMARY KEY(work_id, technology_id)
);

/* 作品概要-技術マスタ 中間テーブル コメント記述 */
COMMENT ON TABLE portfolio.work_technology IS '作品概要-技術マスタ 中間テーブル';
COMMENT ON COLUMN portfolio.work_technology.work_id IS '作品ID(work_overviewを参照)';
COMMENT ON COLUMN portfolio.work_technology.technology_id IS '技術ID(technology_masterを参照)';

/* 技術スキルテーブル作成 */
CREATE TABLE portfolio.technology_skill(
    technology_id INTEGER PRIMARY KEY,
    skill_experience INTEGER NOT NULL,
    skill_evaluation INTEGER NOT NULL,
    skill_sort_no INTEGER NOT NULL DEFAULT 999
);

/* 技術スキルテーブル コメント記述 */
COMMENT ON TABLE portfolio.technology_skill IS '技術スキル';
COMMENT ON COLUMN portfolio.technology_skill.technology_id IS '技術ID(technology_masterを参照)';
COMMENT ON COLUMN portfolio.technology_skill.skill_experience IS '技術スキル経験年数';
COMMENT ON COLUMN portfolio.technology_skill.skill_evaluation IS '技術スキル自己評価';
COMMENT ON COLUMN portfolio.technology_skill.skill_sort_no IS '技術スキル表示順';

/* 取得資格マスタテーブル作成 */
CREATE TABLE portfolio.license_master(
    license_id SERIAL PRIMARY KEY,
    license_name TEXT NOT NULL,
    license_host TEXT NOT NULL,
    license_stamp TIMESTAMP NOT NULL,
    license_sort_no INTEGER NOT NULL DEFAULT 999,
    license_homepage_url TEXT
);

/* 取得資格マスタテーブル コメント記述 */
COMMENT ON TABLE portfolio.license_master IS '取得資格マスタテーブル';
COMMENT ON COLUMN portfolio.license_master.license_id IS '取得資格ID';
COMMENT ON COLUMN portfolio.license_master.license_name IS '取得資格名';
COMMENT ON COLUMN portfolio.license_master.license_host IS '主催団体名';
COMMENT ON COLUMN portfolio.license_master.license_stamp IS '資格取得日';
COMMENT ON COLUMN portfolio.license_master.license_sort_no IS '資格表示順';
COMMENT ON COLUMN portfolio.license_master.license_homepage_url IS '資格ホームページのURL';

<?php
# -----------------------------
# ポートフォリオサイト本体 ブログ共通ナビゲーションモデル
# 2021.05.24 s0hiba 初版作成
# -----------------------------


//モデルコア抽象クラスのファイルを読み込む
include_once("{$projectDirPath}/apps/core/model.php");

class BlogCommonNavigationModel extends ModelCore
{
    public function getArticleListNew()
    {
        //データ取得対象のキャッシュのキー名を指定
        $cacheKey = 'portfolio_article_list_new';

        //データ取得用のSQLクエリオブジェクトを用意
        $queryObj = $this->dataStore->createPlaneSqlQueryObj();
        $queryObj->setSelect('portfolio.blog_article');
        $queryObj->setOrder(array(
            'article_stamp DESC',
        ));
        $queryObj->setLimit(5);

        //取得したデータを返す
        return $this->dataStore->getData($cacheKey, $queryObj);
    }

    public function getTagList()
    {
        //データ取得対象のキャッシュのキー名を指定
        $cacheKey = 'portfolio_tag_list';

        //データ取得用のSQLクエリオブジェクトを用意
        $queryObj = $this->dataStore->createPlaneSqlQueryObj();
        $queryObj->setSelect('portfolio.article_tag_master');
        $queryObj->setOrder(array(
            'article_sort_no ASC',
            'article_tag_id ASC',
        ));

        //取得したデータを返す
        return $this->dataStore->getData($cacheKey, $queryObj);
    }

    public function getMonthList()
    {
        //データ取得対象のキャッシュのキー名を指定
        $cacheKey = 'portfolio_article_list_stamp_asc';

        //データ取得用のSQLクエリオブジェクトを用意
        $queryObj = $this->dataStore->createPlaneSqlQueryObj();
        $queryObj->setSelect('portfolio.blog_article');
        $queryObj->setOrder(array(
            'article_stamp ASC',
        ));

        //データを取得
        $articleListAsc = $this->dataStore->getData($cacheKey, $queryObj);

        //取得したデータが正しい配列形式ではない場合、空の配列を返す
        if (!isset($articleListAsc) || !is_array($articleListAsc) || count($articleListAsc) <= 0) {
            return array();
        }

        //ブログ記事一覧から、ブログ記事の投稿年月一覧を作成
        foreach ($articleListAsc as $articleData) {
            //投稿日時から年と月を取得
            $year = substr($articleData['article_stamp'], 0, 4);
            $month = substr($articleData['article_stamp'], 5, 2);

            //一覧を配列形式で作成
            $monthList[$year][$month] = $month;
        }

        //作成した配列を返す
        return $monthList;
    }
}

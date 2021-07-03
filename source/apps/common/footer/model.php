<?php
# -----------------------------
# ポートフォリオサイト本体 共通フッターモデル
# 2021.05.24 s0hiba 初版作成
# -----------------------------


//モデルコア抽象クラスのファイルを読み込む
include_once("{$projectDirPath}/apps/core/model.php");

class CommonFooterModel extends ModelCore
{
    public function getArticleListFooter()
    {
        //データ取得対象のキャッシュのキー名を指定
        $cacheKey = 'portfolio_article_list_footer';

        //データ取得用のSQLクエリオブジェクトを用意
        $queryObj = $this->dataStore->createPlaneSqlQueryObj();
        $queryObj->setSelect('portfolio.blog_article');
        $queryObj->setOrder(array(
            'article_stamp DESC',
        ));
        $queryObj->setLimit(3);

        //取得したデータを返す
        return $this->dataStore->getData($cacheKey, $queryObj);
    }

    public function getWorkListFooter()
    {
        //データ取得対象のキャッシュのキー名を指定
        $cacheKey = 'portfolio_work_list_footer';

        //データ取得用のSQLクエリオブジェクトを用意
        $queryObj = $this->dataStore->createPlaneSqlQueryObj();
        $queryObj->setSelect('portfolio.work_overview');
        $queryObj->setOrder(array(
            'work_sort_no ASC',
            'work_id ASC',
        ));
        $queryObj->setLimit(3);

        //取得したデータを返す
        return $this->dataStore->getData($cacheKey, $queryObj);
    }
}

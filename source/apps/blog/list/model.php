<?php
# -----------------------------
# ポートフォリオサイト本体 ブログ記事一覧ページモデル
# 2021.05.24 s0hiba 初版作成
# -----------------------------


//モデルコア抽象クラスのファイルを読み込む
include_once("{$projectDirPath}/apps/core/model.php");

class BlogListModel extends ModelCore
{
    protected $pageNum = 1;

    public function setPageNum($pageNum)
    {
        //指定された値が正しいページ番号であれば、プロパティへ値をセット
        if (!$this->isCorrectPageNum($pageNum)) {
            return;
        }

        $this->pageNum = $pageNum;
    }

    public function getPageNum()
    {
        return $this->pageNum;
    }

    public function getArticleList($whereArray)
    {
        //ページ数からOFFSETの指定値を取得
        $offset = $this->getOffset($this->pageNum);

        //データ取得対象のキャッシュのキー名を取得
        $cacheKey = $this->getListCacheKey($this->pageNum);

        //データ取得用のSQLクエリオブジェクトを用意
        $queryObj = $this->dataStore->createPlaneSqlQueryObj();
        $queryObj->setSelect('portfolio.blog_article');
        $queryObj->setJoin(array(
            'portfolio.article_tag_master' => 'article_tag_id',
        ));
        $queryObj->setWhere($whereArray);
        $queryObj->setOrder(array(
            'article_stamp DESC',
        ));
        $queryObj->setLimit(5);
        $queryObj->setOffset($offset);

        //取得したデータを返す
        return $this->dataStore->getData($cacheKey, $queryObj);
    }

    public function getPageAll($whereArray)
    {
        //データ取得対象のキャッシュのキー名を取得
        $cacheKey = $this->getPageAllCacheKey();

        //データ取得用のSQLクエリオブジェクトを用意
        $queryObj = $this->dataStore->createPlaneSqlQueryObj();
        //取得対象となる記事の総数を取得
        $queryObj->setSelect('portfolio.blog_article', array(
            'COUNT(*)',
        ));
        $queryObj->setWhere($whereArray);
        $queryObj->setSingleRowMode(true);

        //データを取得
        $articleCount = $this->dataStore->getData($cacheKey, $queryObj);

        //取得した記事の総数からページ総数を算出
        $pageAll = floor(($articleCount['count'] + 4) / 5);

        //算出したページ総数を返す
        return $pageAll;
    }

    private function getOffset()
    {
        //ページからOFFSETの指定値を取得
        $offset = ($this->pageNum - 1) * 5;
        return $offset;
    }

    public function getWhereArray($execDateTime)
    {
        return array();
    }

    public function getBreadcrumb($whereArray)
    {
        return '';
    }

    public function getOptionPath()
    {
        return '';
    }

    protected function getListCacheKey()
    {
        return "portfolio_article_list_{$this->pageNum}";
    }

    protected function getPageAllCacheKey()
    {
        return 'portfolio_article_list_page_all';
    }

    private function isCorrectPageNum($pageNum)
    {
        //ページ番号が0より大きい数であればtrueを返す
        if (isset($pageNum) && ctype_digit(strval($pageNum)) && $pageNum > 0) {
            return true;
        }

        //正しい形式でない場合はfalseを返す
        return false;
    }
}

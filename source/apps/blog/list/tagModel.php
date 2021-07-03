<?php
# -----------------------------
# ポートフォリオサイト本体 タグ別ブログ記事ページモデル
# 2021.05.31 s0hiba 初版作成
# -----------------------------


class BlogListTagModel extends BlogListModel
{
    private $targetTagId;

    public function setTargetTagId($targetTagId)
    {
        $this->targetTagId = $targetTagId;
    }

    public function isCorrectTargetTagId($targetTagId)
    {
        if (isset($targetTagId) && ctype_digit($targetTagId) && $targetTagId > 0) {
            return true;
        }

        return false;
    }

    public function getWhereArray($execDateTime)
    {
        //パスに指定されたタグのIDの値をチェック
        if (!$this->isCorrectTargetTagId($this->targetTagId)) {
            //タグIDが正しく指定されていない場合、空の配列を返す
            return array();
        }

        //ブログ記事の検索条件を配列で指定
        $whereArray = array(
            array('key' => 'article_tag_id', 'value' => $this->targetTagId, 'operator' => '=', 'type' => PDO::PARAM_INT),
        );

        //検索条件の配列を返す
        return $whereArray;
    }

    public function getBreadcrumb($whereArray)
    {
        //データ取得対象のキャッシュのキー名を取得
        $cacheKey = "portfolio_article_tag_data_{$this->targetTagId}";

        //データ取得用のSQLクエリオブジェクトを用意
        $queryObj = $this->dataStore->createPlaneSqlQueryObj();
        $queryObj->setSelect('portfolio.article_tag_master');
        $queryObj->setWhere($whereArray);
        $queryObj->setSingleRowMode(true);

        //データを取得
        $tag = $this->dataStore->getData($cacheKey, $queryObj);

        //パンくずリスト用に記事種別名を返す
        $breadcrumb = $tag['article_tag_name'];
        return $breadcrumb;
    }

    public function getOptionPath()
    {
        //パスのオプション部分を指定して返す
        $optionPath = "tag/{$this->targetTagId}/";
        return $optionPath;
    }

    protected function getListCacheKey()
    {
        return "portfolio_article_list_tag_{$this->targetTagId}_{$this->pageNum}";
    }

    protected function getPageAllCacheKey()
    {
        return "portfolio_article_list_page_all_tag_{$this->targetTagId}";
    }
}

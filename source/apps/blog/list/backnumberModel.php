<?php
# -----------------------------
# ポートフォリオサイト本体 ブログ記事バックナンバーページモデル
# 2021.05.31 s0hiba 初版作成
# -----------------------------


class BlogListBacknumberModel extends BlogListModel
{
    private $targetYearNum;
    private $targetMonthNum;
    private $targetMonthStartObj;
    private $targetMonthEndObj;

    public function setBackNumberTargetMonth($targetYearNum, $targetMonthNum)
    {
        $this->targetYearNum        = $targetYearNum;
        $this->targetMonthNum       = $targetMonthNum;
        $this->targetMonthStartObj  = new DateTime("{$targetYearNum}-{$targetMonthNum}-01 00:00:00");
        $this->targetMonthEndObj    = new DateTime("last day of {$targetYearNum}-{$targetMonthNum}-01 23:59:59");
    }

    public function isCorrectYearNum($yearNum)
    {
        if (isset($yearNum) || $yearNum > 0) {
            return true;
        }

        return false;
    }

    public function isCorrectMonthNum($monthNum)
    {
        if (isset($monthNum) && $monthNum > 0 && $monthNum <= 12) {
            return true;
        }

        return false;
    }

    public function getWhereArray($execDateTime)
    {
        //指定された年月の値をチェック
        if (!$this->isCorrectYearNum($this->targetYearNum)
         || !$this->isCorrectMonthNum($this->targetMonthNum)
         || $this->targetMonthStartObj > $execDateTime) {
            //指定された年月が正しくない場合、空の配列を返す
            return array();
        }

        //DateTimeオブジェクトを文字列へフォーマット
        $monthStartStamp    = $this->targetMonthStartObj->format('Y-m-d H:i:s');
        $monthEndStamp      = $this->targetMonthEndObj->format('Y-m-d H:i:s');

        //ブログ記事の検索条件を指定
        $whereArray = array(
            array('key' => 'article_stamp', 'value' => $monthStartStamp,    'operator' => '>=', 'type' => PDO::PARAM_STR),
            array('key' => 'article_stamp', 'value' => $monthEndStamp,      'operator' => '<=', 'type' => PDO::PARAM_STR),
        );

        return $whereArray;
    }

    public function getBreadcrumb($whereArray)
    {
        //パンくずリスト用に年月を文字列で取得して返す
        $breadcrumb = $this->targetMonthStartObj->format('Y年m月');
        return $breadcrumb;
    }

    public function getOptionPath()
    {
        //パスのオプション部分を指定して返す
        $optionPath = "backnumber/{$this->targetYearNum}/{$this->targetMonthNum}/";
        return $optionPath;
    }

    protected function getListCacheKey()
    {
        return "portfolio_article_list_backnumber_{$this->targetYearNum}_{$this->targetMonthNum}_{$this->pageNum}";
    }

    protected function getPageAllCacheKey()
    {
        return "portfolio_article_list_page_all_backnumber_{$this->targetYearNum}_{$this->targetMonthNum}";
    }
}
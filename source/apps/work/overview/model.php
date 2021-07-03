<?php
# -----------------------------
# ポートフォリオサイト本体 作品概要ページモデル
# 2021.05.24 s0hiba 初版作成
# -----------------------------


//モデルコア抽象クラスのファイルを読み込む
include_once("{$projectDirPath}/apps/core/model.php");

class WorkOverviewModel extends ModelCore
{
    private $workId = 1;

    public function setWorkId($workId)
    {
        //指定された値が正しい作品Dであれば、プロパティへ値をセット
        if ($this->isCorrectWorkId($workId)) {
            $this->workId = $workId;
        }
    }

    public function getWork()
    {
        //データ取得対象のキャッシュのキー名を指定
        $cacheKey = "portfolio_work_data_{$this->workId}";

        //データ取得用のSQLクエリオブジェクトを用意
        $queryObj = $this->dataStore->createPlaneSqlQueryObj();
        $queryObj->setSelect('portfolio.work_overview');
        $queryObj->setWhere(array(
            array('key' => 'work_id', 'value' => $this->workId, 'operator' => '=', 'type' => PDO::PARAM_INT),
        ));
        $queryObj->setSingleRowMode(true);

        //取得したデータを返す
        return $this->dataStore->getData($cacheKey, $queryObj);
    }

    public function getTechnologyList()
    {
        //データ取得対象のキャッシュのキー名を指定
        $cacheKey = "portfolio_technology_list_{$this->workId}";

        //データ取得用のSQLクエリオブジェクトを用意
        $queryObj = $this->dataStore->createPlaneSqlQueryObj();
        $queryObj->setSelect('portfolio.work_technology');
        $queryObj->setJoin(array(
            'portfolio.technology_master' => 'technology_id',
        ));
        $queryObj->setWhere(array(
            array('key' => 'work_id', 'value' => $this->workId, 'operator' => '=', 'type' => PDO::PARAM_INT),
        ));
        $queryObj->setOrder(array(
            'technology_id ASC',
        ));

        //データを取得
        $technologyArray = $this->dataStore->getData($cacheKey, $queryObj);

        //取得したデータが正しい配列形式ではない場合、空の配列を返す
        if (!isset($technologyArray) || !is_array($technologyArray) || count($technologyArray) <= 0) {
            return array();
        }

        //技術名から画像ファイルパスを生成して配列に追加
        foreach ($technologyArray as $technologyData) {
            //技術名を小文字に、半角スペースをアンダーバーに変換
            $technologyNameLower = strtolower($technologyData['technology_name']);
            $imageName = str_replace(' ', '_', $technologyNameLower);

            //パスと結合
            $technologyImage = "https://cdn.s0hiba.site/img/{$imageName}.png";

            //表示用の配列を作成
            $technologyList[] = array(
                'technology_name'   => $technologyData['technology_name'],
                'technology_image'  => $technologyImage,
            );
        }

        //整形した配列を返す
        return $technologyList;
    }

    public function isCorrectWorkId($workId)
    {
        //0より大きい整数である場合はtrueを返す
        if (isset($workId) && ctype_digit(strval($workId)) && $workId > 0) {
            return true;
        }

        //条件に合致しない場合はfalseを返す
        return false;
    }
}

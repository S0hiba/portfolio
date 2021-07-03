<?php
# -----------------------------
# ポートフォリオサイト本体 作品一覧ページモデル
# 2021.05.24 s0hiba 初版作成
# -----------------------------


//モデルコア抽象クラスのファイルを読み込む
include_once("{$projectDirPath}/apps/core/model.php");

class WrokListModel extends ModelCore
{
    public function getWorkList()
    {
        //データ取得対象のキャッシュのキー名を指定
        $cacheKey = 'portfolio_work_list';

        //データ取得用のSQLクエリオブジェクトを用意
        $queryObj = $this->dataStore->createPlaneSqlQueryObj();
        $queryObj->setSelect('portfolio.work_overview');
        $queryObj->setOrder(array(
            'work_sort_no ASC',
            'work_id ASC',
        ));

        //取得したデータを返す
        return $this->dataStore->getData($cacheKey, $queryObj);
    }
}
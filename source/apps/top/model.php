<?php
# -----------------------------
# ポートフォリオサイト本体 トップページモデル
# 2021.05.24 s0hiba 初版作成
# -----------------------------


//モデルコア抽象クラスのファイルを読み込む
include_once("{$projectDirPath}/apps/core/model.php");

class TopModel extends ModelCore
{
    public function getLogList()
    {
        //データ取得対象のキャッシュのキー名を指定
        $cacheKey = 'portfolio_log_list';

        //データ取得用のSQLクエリオブジェクトを用意
        $queryObj = $this->dataStore->createPlaneSqlQueryObj();
        $queryObj->setSelect('portfolio.update_log');
        $queryObj->setJoin(array(
            'portfolio.log_tag_master' => 'log_tag_id',
        ));
        $queryObj->setOrder(array(
            'log_stamp DESC',
        ));
        $queryObj->setLimit(5);

        //データを取得
        $logArray = $this->dataStore->getData($cacheKey, $queryObj);

        //取得したデータが正しい配列形式ではない場合、空の配列を返す
        if (!isset($logArray) || !is_array($logArray) || count($logArray) <= 0) {
            return array();
        }

        //更新履歴の更新日から時間部分を削除して整形
        foreach ($logArray as $logData) {
            $logDate = substr($logData['log_stamp'], 0, 10);
            $logList[] = array(
                'log_id'            => $logData['log_id'],
                'log_date'          => $logDate,
                'log_tag_id'        => $logData['log_tag_id'],
                'log_text'          => $logData['log_text'],
                'log_target_id'     => $logData['log_target_id'],
                'log_link'          => $logData['log_link'],
                'log_tag_name'      => $logData['log_tag_name'],
                'log_tag_icon'      => $logData['log_tag_icon'],
            );
        }

        //整形した配列を返す
        return $logList;
    }
}

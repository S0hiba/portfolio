<?php
# -----------------------------
# ポートフォリオサイト本体 カウンタモデル
# 2021.06.05 s0hiba 初版作成
# -----------------------------


//モデルコア抽象クラスのファイルを読み込む
include_once("{$projectDirPath}/apps/core/model.php");

class CounterModel extends ModelCore
{
    public function writeCountData($countTargetUrl, $execDateTime)
    {
        //現在の日付と日時を取得
        $nowDateStamp = $execDateTime->format('Y-m-d');

        //カウンタログの保持期限を取得
        $expireDateTime = clone $execDateTime;
        $expireDateTime->modify('+2 days midnight');
        $expireStamp = $expireDateTime->format('U');

        //カウンタログを追加し、保持期限を設定
        $this->dataStore->pushCacheList("portfolio_counter_{$nowDateStamp}", $countTargetUrl);
        $this->dataStore->changeCacheExpire("portfolio_counter_{$nowDateStamp}", $expireStamp);
    }
}
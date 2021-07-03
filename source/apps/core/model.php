<?php
# -----------------------------
# ポートフォリオサイト本体 モデル抽象クラス
# 202105.31 s0hiba 初版作成
# -----------------------------


abstract class ModelCore
{
    /**
     * データ操作に用いるデータストアオブジェクト
     */
    protected QueryBuilderWithPhpRedisDataStore $dataStore;

    /**
     * コンストラクタ
     * @param QueryBuilderWithPhpRedisDataStore $dataStore データ操作に用いるデータストアオブジェクト
     */
    public function __construct(QueryBuilderWithPhpRedisDataStore $dataStore)
    {
        $this->dataStore    = $dataStore;
    }
}
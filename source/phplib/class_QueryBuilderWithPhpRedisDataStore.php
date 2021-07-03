<?php
# -----------------------------
# 自作クエリビルダとPHPRedisを用いた、データストアクラス
# 2021.05.17 s0hiba 初版作成
# -----------------------------


class QueryBuilderWithPhpRedisDataStore
{
    /**
     * キャッシュ接続オブジェクト
     * @var Redis
     */
    private Redis $cacheObj;

    /**
     * SQLクエリの実行用オブジェクト
     * @var PDOQueryRunner
     */
    private PDOQueryRunner $queryRunnerObj;

    /**
     * コンストラクタ
     * @param Redis             $cacheObj
     * @param PDOQueryRunner    $queryRunnerObj
     */
    public function __construct(Redis $cacheObj, PDOQueryRunner $queryRunnerObj)
    {
        $this->cacheObj         = $cacheObj;
        $this->queryRunnerObj   = $queryRunnerObj;
    }

    /**
     * 空のSQLクエリオブジェクトを生成
     * @return PsqlQuery
     */
    public function createPlaneSqlQueryObj()
    {
        return $this->queryRunnerObj->createPlaneSqlQueryObj();
    }

    /**
     * データ取得
     * @param   string      $cacheKey   データ取得対象のキャッシュのキー名
     * @param   PsqlQuery   $queryObj   データ取得用のSQLクエリオブジェクト
     * @return  array                   取得したデータ
     */
    public function getData(string $cacheKey, PsqlQuery $queryObj)
    {
        //キャッシュからデータ取得を試みる
        $cacheDataJson = $this->cacheObj->get($cacheKey);
        $cacheDataArray = json_decode($cacheDataJson, true);

        //キャッシュからデータが取得できた場合、取得したデータを返す
        if (!empty($cacheDataArray)) {
            return $cacheDataArray;
        }

        //DBでクエリを実行
        $dbData = $this->executeDbQuery($queryObj);

        //データが取得できなかった場合、空の配列を返す
        if (empty($dbData)) {
            return array();
        }

        //DBからデータが取得できた場合、取得したデータをキャッシュへ保存
        $cacheDataJson = json_encode($dbData);
        $this->cacheObj->set($cacheKey, $cacheDataJson);

        //DBから取得したデータを返す
        return $dbData;
    }

    /**
     * データ書き込み
     * @param   array       $cacheKeyArray  書き込み時、キャッシュから消去するキー名を列挙した配列
     * @param   PsqlQuery   $queryObj       データ書き込み用のSQLクエリオブジェクト
     * @return  array                       書き込みクエリの実行結果
     */
    public function writeData(array $cacheKeyArray, PsqlQuery $queryObj)
    {
        //DBでクエリを実行
        $dbData = $this->executeDbQuery($queryObj);

        //指定されたキーのキャッシュデータを削除
        $this->cacheObj->del($cacheKeyArray);

        //DBから取得したデータを返す
        return $dbData;
    }

    /**
     * DBでクエリを実行し、結果を取得する
     * @param   PsqlQuery $queryObj 実行するSQLクエリのオブジェクト
     * @return  array               クエリの実行結果
     */
    public function executeDbQuery(PsqlQuery $queryObj)
    {
        //クエリの実行を試みる
        try {
            $this->queryRunnerObj->beginTransaction();
            $dbData = $this->queryRunnerObj->getSqlResult($queryObj);
            $this->queryRunnerObj->commit();
        } catch (PDOException $e) {
            //例外発生時、DB操作をロールバックしてから例外をそのまま投げる
            $this->queryRunnerObj->rollBack();
            throw $e;
        }

        //クエリの実行結果を返す
        return $dbData;
    }

    /**
     * リスト型キャッシュへのデータ追加
     * @param string $cacheKey  データを追加するキャッシュのキー名
     * @param string $pushValue 追加する値
     * @return void
     */
    public function pushCacheList(string $cacheKey, string $pushValue)
    {
        $this->cacheObj->rpush($cacheKey, $pushValue);
    }

    /**
     * キャッシュ保持期限の指定・変更
     * @param string $cacheKey      保持期限の変更対象となるキャッシュのキー名
     * @param string $expireStamp   設定する保持期限(yyyy-mm-dd hh:ii:ss)
     * @return void
     */
    public function changeCacheExpire(string $cacheKey, string $expireStamp)
    {
        $this->cacheObj->expireAt($cacheKey, $expireStamp);
    }
}

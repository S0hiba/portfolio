<?php
# -----------------------------
# クエリビルダ PDOを使用したSQL実行用クラス
# 2021.05.17 s0hiba MyPDOクラスをベースに初版作成
# -----------------------------


class PDOQueryRunner
{
    /**
     * DB接続情報
     * @var string
     */
    private string $dsn;

    /**
     * DB接続オブジェクト
     * @var PDO
     */
    private ?PDO $pdo;

    /**
     * コンストラクタ
     * @param string $dsn DB接続情報
     */
    public function __construct(string $dsn)
    {
        $this->dsn = $dsn;
    }

    /**
     * 空のSQLクエリオブジェクトを生成
     * @return PsqlQuery
     */
    public function createPlaneSqlQueryObj()
    {
        return new PsqlQuery();
    }

    /**
     * トランザクション開始
     * @return void
     */
    public function beginTransaction()
    {
        //DBへ接続
        $this->connectDb();

        $this->pdo->beginTransaction();
    }

    /**
     * トランザクションのコミット
     * @return void
     */
    public function commit()
    {
        $this->pdo->commit();
    }

    /**
     * トランザクションのロールバック
     * @return void
     */
    public function rollBack()
    {
        $this->pdo->rollBack();
    }

    /**
     * SQLを実行して結果を取得
     * @param   PsqlQuery $queryObj 実行するSQLのクエリオブジェクト
     * @return  array               SQLの実行結果
     */
    public function getSqlResult(PsqlQuery $queryObj)
    {
        //DBへ接続
        $this->connectDb();

        //SQLクエリオブジェクトからパラメータを取得
        $sql = $queryObj->getSqlStr();
        $bindArray = $queryObj->getBindArray();
        $afterBindArray = $queryObj->getAfterBindArray();

        //SQLの実行を試みる
        try {
            //SQL文をprepare
            $statement = $this->pdo->prepare($sql);

            //bindValue用の配列が正しい配列形式だったら、全てbind
            if (isset($bindArray) && is_array($bindArray) && count($bindArray) > 0) {
                foreach ($bindArray as $bindKey => $bindRow) {
                    //bindValue用の値に「afterBind」が指定されていたら、
                    //bindValueせずに次のループへ
                    if ($bindRow['value'] === 'afterBind') {
                        continue;
                    }

                    $statement->bindValue(":{$bindKey}", $bindRow['value'], $bindRow['type']);
                }
            }

            //実行結果を初期化
            $result = array();

            //afterBindArrayが設定されているなら、SQLを複数回実行
            //そうでないなら、SQLを1回だけ実行
            if (isset($afterBindArray) && is_array($afterBindArray) && count($afterBindArray) > 0) {
                foreach ($afterBindArray as $afterBindData) {
                    //afterBindArrayの要素が正しい配列形式かチェック
                    if (!isset($afterBindData) || !is_array($afterBindData) || count($afterBindData) == 0) {
                        //正しい配列形式でないなら、次のループへ
                        continue;
                    }

                    //SQL1回分のbindValue用の値を全てbind
                    foreach ($afterBindData as $afterBindRow) {
                        $statement->bindValue(":{$afterBindRow['key']}", $afterBindRow['value'], $this->bindArray[$afterBindRow['key']]['type']);
                    }

                    //SQLを実行
                    $statement->execute();

                    //クエリの1行取得モードに応じて実行結果を取得
                    if ($queryObj->isSingleRowMode()) {
                        $result[] = $statement->fetch(PDO::FETCH_ASSOC);
                    } else {
                        $result[] = $statement->fetchAll(PDO::FETCH_ASSOC);
                    }
                    
                }
            } else {
                //SQLを実行
                $statement->execute();

                //クエリの1行取得モードに応じて実行結果を取得
                if ($queryObj->isSingleRowMode()) {
                    $result = $statement->fetch(PDO::FETCH_ASSOC);
                } else {
                    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                }
            }
        } catch(PDOException $e) {
            //エラーがあった場合Exceptionを投げる
            throw $e;
        }

        //結果を返す
        return $result;
    }

    /**
     * DBへ接続
     * すでに接続済みの場合は何もせずに終了(多重接続はしない)
     * @return void
     */
    private function connectDb()
    {
        //既にDBへ接続済みの場合、接続せずに終了
        if (!empty($this->pdo)) {
            return;
        }

        //DBへ接続し、PDOの設定を変更
        $this->pdo = new PDO($this->dsn);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }
}

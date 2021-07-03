<?php
# -----------------------------
# PDO拡張クラス
# 2018.12.15 s0hiba 初版作成
# 2020.10.25 s0hiba コンストラクタ引数からDSNを受け取るように変更
# -----------------------------


class MyPDO extends PDO
{
    /**
     * WHERE句で使用する演算子の一覧
     * @var array
     */
    private const OPERATOR_LIST = array(
        '>'     => 1,
        '<'     => 2,
        '>='    => 3,
        '<='    => 4,
    );

    /**
     * 実行するSQLの種類
     * @var string
     */
    private $mode = '';

    /**
     * 実行するSQL文
     * @var string
     */
    private $sql = '';

    /**
     * bindValue用の配列
     * @var array
     */
    private $bindArray = array();

    /**
     * 複数回SQLを実行する際の、bindValue用の配列
     * @var array
     */
    private $afterBindArray = array();

    /**
     * コンストラクタ
     */
    public function __construct($dsn)
    {
        parent::__construct($dsn);
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }

    /**
     * INSERT文作成
     * @param string $table SELECT対象のテーブル名
     */
    public function setInsert($table)
    {
        $this->mode = 'insert';
        $this->sql = "INSERT INTO {$table}";
    }

    /**
     * UPDATE文作成
     * @param string $table UPDATE対象のテーブル名
     */
    public function setUpdate($table)
    {
        $this->mode = 'update';
        $this->sql = "UPDATE {$table}";
    }

    /**
     * DELETE文作成
     * @param string $table DELETE対象のテーブル名
     */
    public function setDelete($table)
    {
        $this->mode = 'delete';
        $this->sql = "DELETE FROM {$table}";
    }

    /**
     * SELECT文作成
     * @param string $table       SELECT対象のテーブル名
     * @param array  $columnArray SELECT対象のカラムの配列
     */
    public function setSelect($table, $columnArray = array())
    {
        //実行するSQLの種類にSELECTを指定
        $this->mode = 'select';

        //カラムの配列が正しい配列形式かチェック
        if (!isset($columnArray) || !is_array($columnArray) || count($columnArray) == 0) {
            //正しい配列形式でないなら、全カラムを対象とするSELECT文を指定
            $this->sql = "SELECT * FROM {$table}";
            return;
        }

        //SQL文の生成部分を初期化
        $columns = '';
        $delimiter = '';

        //カラム指定部分を生成
        foreach ($columnArray as $columnRow) {
            $columns .= "{$delimiter} {$columnRow}";

            //2回目以降は「,」で区切る
            $delimiter = ',';
        }

        //整形してSQL文に指定
        $this->mode = 'select';
        $this->sql = "SELECT {$columns} FROM {$table}";
    }

    /**
     * INSERT・UPDATE用の値設定
     * @param array $valueArray bindValue用の値の配列
     */
    public function setValue($valueArray)
    {
        //引数が正しい配列形式かチェック
        if (!isset($valueArray) || !is_array($valueArray) || count($valueArray) == 0) {
            //正しい配列形式でないなら、値を設定しない
            return;
        }

        //SQL文生成用のデリミタを初期化
        $delimiter = '';

        //INSERT・UPDATEでの処理を切り分け
        switch ($this->mode) {
            case 'insert': //INSERT
                //SQL文の生成部分を初期化
                $column = '';
                $values = '';

                //カラム指定部分とVALUES()部分を生成
                foreach ($valueArray as $valueRow) {
                    //カラム指定部分
                    $column .= "{$delimiter} {$valueRow['key']}";
                    //VALUES()部分
                    $values .= "{$delimiter} :{$valueRow['key']}";

                    //bindValue用のプロパティを取得
                    $this->bindArray[$valueRow['key']] = $valueRow;

                    //2回目以降は「,」で区切る
                    $delimiter = ',';
                }

                //整形してSQL文に結合
                $this->sql .= "({$column}) VALUES({$values})";

                break;
            case 'update': //UPDATE
                //SQL文の生成部分を初期化
                $set = '';

                //SET部分を生成
                foreach ($valueArray as $valueRow) {
                    $set .= "{$delimiter} {$valueRow['key']} = :{$valueRow['key']}";

                    //bindValue用のプロパティを取得
                    $this->bindArray[$valueRow['key']] = $valueRow;

                    //2回目以降は「,」で区切る
                    $delimiter = ',';
                }

                //整形してSQL文に結合
                $this->sql .= " SET {$set}";
        }
    }

    /**
     * INNER JOIN句追加
     * @param array $joinArray 結合対象のテーブル名と、結合に用いるカラム名の配列
     */
    public function setJoin($joinArray)
    {
        //引数が正しい配列形式かチェック
        if (!isset($joinArray) || !is_array($joinArray) || count($joinArray) == 0) {
            //正しい配列形式でないなら、JOIN句を設定しない
            return;
        }

        //INNER JOIN句をSQL文に結合
        foreach ($joinArray as $table => $column) {
            $this->sql .= " INNER JOIN {$table}
                            USING({$column})";
        }
    }

    /**
     * WHERE句追加
     * @param array $whereArray bindValue用の条件値の配列
     */
    public function setWhere($whereArray)
    {
        //引数が正しい配列形式かチェック
        if (!isset($whereArray) || !is_array($whereArray) || count($whereArray) == 0) {
            //正しい配列形式でないなら、WHERE句を設定しない
            return;
        }

        //SQL文生成用の各種変数を初期化
        $where = '';
        $delimiter = '';

        //条件部分を生成
        foreach ($whereArray as $whereRow) {
            //条件演算子を切り分ける
            switch ($whereRow['operator']) {
                case '>':
                case '<':
                case '>=':
                case '<=':
                    $operator = $whereRow['operator'];
                    $bindKey = "{$whereRow['key']}_" . self::OPERATOR_LIST[$operator];
                    break;
                default:
                    $operator = '=';
                    $bindKey = "{$whereRow['key']}_0";
            }

            //条件をSQL文に結合
            $where .= " {$delimiter} {$whereRow['key']} {$operator} :{$bindKey}";

            //bindValue用のプロパティを取得
            $this->bindArray[$bindKey] = $whereRow;

            //2回目以降は「AND」で区切る
            $delimiter = 'AND';
        }

        //整形してSQL文に結合
        $this->sql .= " WHERE {$where}";
    }

    /**
     * GROUP BY句追加
     * @param array $columnArray グループ化するカラム名の配列
     */
    public function setGroup($columnArray)
    {
        //引数が正しい配列形式かチェック
        if (!isset($columnArray) || !is_array($columnArray) || count($columnArray) == 0) {
            //正しい配列形式でないなら、ORDER BY句を設定しない
            return;
        }

        //SQL文生成用の各種変数を初期化
        $group = '';
        $delimiter = '';

        //ソート指定部分を生成
        foreach ($columnArray as $columnRow) {
            $group .= "{$delimiter} {$columnRow}";

            //2回目以降は「,」で区切る
            $delimiter = ',';
        }

        //整形してSQL文に結合
        $this->sql .= " GROUP BY {$group}";
    }

    /**
     * ORDER BY句追加
     * @param array $columnArray ソート対象となるカラム名の配列
     */
    public function setOrder($columnArray)
    {
        //引数が正しい配列形式かチェック
        if (!isset($columnArray) || !is_array($columnArray) || count($columnArray) == 0) {
            //正しい配列形式でないなら、ORDER BY句を設定しない
            return;
        }

        //SQL文生成用の各種変数を初期化
        $order = '';
        $delimiter = '';

        //ソート指定部分を生成
        foreach ($columnArray as $columnRow) {
            $order .= "{$delimiter} {$columnRow}";

            //2回目以降は「,」で区切る
            $delimiter = ',';
        }

        //整形してSQL文に結合
        $this->sql .= " ORDER BY {$order}";
    }

    /**
     * LIMIT句追加
     * @param int $limit LIMITに指定する値
     */
    public function setLimit($limit)
    {
        //LIMIT句をSQL文に追加
        $this->sql .= " LIMIT :limit";

        //bindValue用のプロパティを取得
        $this->bindArray['limit'] = array('value' => $limit, 'type' => PDO::PARAM_INT);
    }

    /**
     * OFFSET句追加
     * @param int $offset OFFSETに指定する値
     */
    public function setOffset($offset)
    {
        //OFFSET句をSQL文に追加
        $this->sql .= " OFFSET :offset";

        //bindValue用のプロパティを取得
        $this->bindArray['offset'] = array('value' => $offset, 'type' => PDO::PARAM_INT);
    }

    /**
     * RETURNING句追加
     */
    public function setReturning()
    {
        //RETURNING句をSQL文に追加
        $this->sql .= "RETURNING *";
    }

    /**
     * 複数回SQLを実行する際のbindValue用の配列を取得
     * @param array $afterBindArray 複数回SQLを実行する際の、bindValue用の値の配列
     */
    public function setAfterBind($afterBindArray)
    {
        $this->afterBindArray = $afterBindArray;
    }

    /**
     * SQL実行結果を全件取得
     * @return array SQLの実行結果
     */
    public function getSqlResult()
    {
        //SQLの実行を試みる
        try {
            //SQL文をprepare
            $statement = $this->prepare($this->sql);

            //bindValue用の配列が正しい配列形式だったら、全てbind
            if (isset($this->bindArray) && is_array($this->bindArray) && count($this->bindArray) > 0) {
                foreach ($this->bindArray as $bindKey => $bindRow) {
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
            if (isset($this->afterBindArray) && is_array($this->afterBindArray) && count($this->afterBindArray) > 0) {
                foreach ($this->afterBindArray as $afterBindData) {
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
                    $result[] = $statement->fetchAll(PDO::FETCH_ASSOC);
                }
            } else {
                //SQLを実行
                $statement->execute();
                $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch(PDOException $e) {
            //エラーがあった場合、プロパティをリセットしてからExceptionを投げる
            $this->mode = '';
            $this->sql = '';
            $this->bindArray = array();
            $this->afterBindArray = array();
            throw $e;
        }

        //プロパティをリセット
        $this->mode = '';
        $this->sql = '';
        $this->bindArray = array();
        $this->afterBindArray = array();

        //結果を返す
        return $result;
    }

    /**
     * SQL実行結果を1行取得
     * @return array SQLの実行結果
     */
    public function getSqlResultRow()
    {
        //SQLの実行を試みる
        try {
            //SQL文をprepare
            $statement = $this->prepare($this->sql);

            //bindValue用の配列が正しい配列形式だったら、全てbind
            if (isset($this->bindArray) && is_array($this->bindArray) && count($this->bindArray) > 0) {
                foreach ($this->bindArray as $bindKey => $bindRow) {
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
            if (isset($this->afterBindArray) && is_array($this->afterBindArray) && count($this->afterBindArray) > 0) {
                foreach ($this->afterBindArray as $afterBindData) {
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
                    $result[] = $statement->fetch(PDO::FETCH_ASSOC);
                }
            } else {
                //SQLを実行
                $statement->execute();
                $result = $statement->fetch(PDO::FETCH_ASSOC);
            }
        } catch(PDOException $e) {
            //エラーがあった場合、プロパティをリセットしてからExceptionを投げる
            $this->mode = '';
            $this->sql = '';
            $this->bindArray = array();
            $this->afterBindArray = array();
            throw $e;
        }

        //プロパティをリセット
        $this->mode = '';
        $this->sql = '';
        $this->bindArray = array();
        $this->afterBindArray = array();

        //結果を返す
        return $result;
    }
}

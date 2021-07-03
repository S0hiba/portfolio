<?php
# -----------------------------
# クエリビルダ PostgreSql用SQLクエリクラス
# 2021.05.17 s0hiba MyPDOクラスをベースに初版作成
# -----------------------------


class PsqlQuery
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
    private string $mode = '';

    /**
     * 1行取得設定、デフォルトはfalse
     */
    private bool $isSingleRowMode = false;

    /**
     * 実行するSQL文
     * @var string
     */
    private string $sql = '';

    /**
     * bindValue用の配列
     * @var array
     */
    private array $bindArray = array();

    /**
     * 複数回SQLを実行する際の、bindValue用の配列
     * @var array
     */
    private array $afterBindArray = array();

    /**
     * INSERT文作成
     * @param string $table SELECT対象のテーブル名
     */
    public function setInsert(string $table)
    {
        $this->mode = 'insert';
        $this->sql = "INSERT INTO {$table}";
    }

    /**
     * UPDATE文作成
     * @param string $table UPDATE対象のテーブル名
     */
    public function setUpdate(string $table)
    {
        $this->mode = 'update';
        $this->sql = "UPDATE {$table}";
    }

    /**
     * DELETE文作成
     * @param string $table DELETE対象のテーブル名
     */
    public function setDelete(string $table)
    {
        $this->mode = 'delete';
        $this->sql = "DELETE FROM {$table}";
    }

    /**
     * SELECT文作成
     * @param string $table       SELECT対象のテーブル名
     * @param array  $columnArray SELECT対象のカラムの配列
     */
    public function setSelect(string $table, array $columnArray = array())
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
    public function setValue(array $valueArray)
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
     * @param   array $joinArray 結合対象のテーブル名と、結合に用いるカラム名の配列
     * @return  void
     */
    public function setJoin(array $joinArray)
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
     * @param   array $whereArray bindValue用の条件値の配列
     * @return  void
     */
    public function setWhere(array $whereArray)
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
     * @param   array $columnArray グループ化するカラム名の配列
     * @return  void
     */
    public function setGroup(array $columnArray)
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
     * @param   array $columnArray ソート対象となるカラム名の配列
     * @return  void
     */
    public function setOrder(array $columnArray)
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
     * @param   int $limit LIMITに指定する値
     * @return  void
     */
    public function setLimit(int $limit)
    {
        //LIMIT句をSQL文に追加
        $this->sql .= " LIMIT :limit";

        //bindValue用のプロパティを取得
        $this->bindArray['limit'] = array('value' => $limit, 'type' => PDO::PARAM_INT);
    }

    /**
     * OFFSET句追加
     * @param   int $offset OFFSETに指定する値
     * @return  void
     */
    public function setOffset(int $offset)
    {
        //OFFSET句をSQL文に追加
        $this->sql .= " OFFSET :offset";

        //bindValue用のプロパティを取得
        $this->bindArray['offset'] = array('value' => $offset, 'type' => PDO::PARAM_INT);
    }

    /**
     * RETURNING句追加
     * @return void
     */
    public function setReturning()
    {
        //RETURNING句をSQL文に追加
        $this->sql .= "RETURNING *";
    }

    /**
     * 1行取得設定の変更
     * trueに設定すると1行取得、falseを設定すると全件取得
     * @return void
     */
    public function setSingleRowMode(bool $isSingleRowMode)
    {
        $this->isSingleRowMode = $isSingleRowMode;
    }

    /**
     * 複数回SQLを実行する際のbindValue用の配列を追加
     * @param   array $afterBindArray 複数回SQLを実行する際の、bindValue用の値の配列
     * @return  void
     */
    public function setAfterBind(array $afterBindArray)
    {
        $this->afterBindArray = $afterBindArray;
    }

    /**
     * 実行するSQL文の文字列を取得
     * @return string 実行するSQL文の文字列
     */
    public function getSqlStr()
    {
        return $this->sql;
    }

    /**
     * bindValue用の配列を取得
     * @return array bindValue用の配列
     */
    public function getBindArray()
    {
        return $this->bindArray;
    }

    /**
     * 複数回SQLを実行する際のbindValue用の配列を取得
     * @return array 複数回SQLを実行する際の、bindValue用の配列
     */
    public function getAfterBindArray()
    {
        return $this->afterBindArray;
    }

    /**
     * 1行取得設定の状態、デフォルトはfalse(全件取得)
     * 変更するには setSingleRowMode() メソッドを使用
     * @return boolean 1行取得設定
     */
    public function isSingleRowMode()
    {
        return $this->isSingleRowMode;
    }
}

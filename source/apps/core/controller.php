<?php
# -----------------------------
# ポートフォリオサイト本体 コントローラ抽象クラス
# 2021.06.02 s0hiba 初版作成
# -----------------------------


abstract class ControllerCore
{
    /**
     * データ操作に用いるデータストアオブジェクト
     */
    protected QueryBuilderWithPhpRedisDataStore $dataStore;

    /**
     * ビューに使用するSmartyオブジェクト
     */
    protected Smarty $viewSmarty;

    /**
     * 実行日時のDateTimeオブジェクト
     */
    protected DateTime $execDateTime;

    /**
     * パスをスラッシュで分解した配列
     */
    protected array $pathQuery;

    /**
     * POSTパラメータ配列
     */
    protected array $postParam;

    /**
     * コンストラクタ
     * @param QueryBuilderWithPhpRedisDataStore $dataStore      データ操作に用いるデータストアオブジェクト
     * @param Smarty                            $viewSmarty     ビューに使用するSmartyオブジェクト
     * @param DateTime                          $execDateTime   実行日時のDateTimeオブジェクト
     * @param array                             $pathQuery      パスをスラッシュで分解した配列
     * @param array                             $postParam      POSTパラメータ配列、基本的に$_POSTをそのまま渡せばOK
     */
    public function __construct(
        QueryBuilderWithPhpRedisDataStore $dataStore, Smarty $viewSmarty, DateTime $execDateTime, array $pathQuery, array $postParam)
    {
        $this->dataStore    = $dataStore;
        $this->viewSmarty   = $viewSmarty;
        $this->execDateTime = $execDateTime;
        $this->pathQuery    = $pathQuery;
        $this->postParam    = $postParam;
    }

    /**
     * 各コントローラのメイン処理
     * @return string ビューの出力文字列
     */
    abstract public function action();

    /**
     * POSTパラメータの共通バリデーション
     * [ memo ] POSTパラメータ自体をクラス化してControllerから切り出す事も検討
     * @param   array       $postArray  POSTパラメータ配列
     * @return  boolean                 POSTパラメータが正しい場合true、不正な形式の場合false
     */
    protected function isCorrectPostParam(array $postArray)
    {
        //正しい配列形式でなければfalse
        if (!isset($postArray) || !is_array($postArray) || count($postArray) <= 0) {
            return false;
        }

        //POSTリクエストの文字エンコーディングと制御文字をチェック
        foreach ($postArray as $postData) {
            //POSTリクエストのデータが配列だった場合、全ての値を結合した文字列に変換
            if (is_array($postData)) {
                $postData = implode($postData);
            }

            //文字エンコーディングがUTF-8でない、もしくは制御文字を含むなら、false
            if (!mb_check_encoding($postData, 'UTF-8') || preg_match('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', $postData)) {
                return false;
            }
        }

        //チェックを通過している場合はtrue
        return true;
    }

    /**
     * パス配列のHTMLエスケープ処理
     * [ memo ] パス配列自体をクラス化してControllerから切り出す事も検討
     * @param   array $pathQuery    パスをスラッシュで分解した配列
     * @return  array               HTMLタグをエスケープしたパス配列
     */
    protected function escapeHtmlFromPathQuery(array $pathQuery)
    {
        if (!isset($pathQuery) || !is_array($pathQuery) || count($pathQuery) <= 0) {
            return array();
        }

        //パスのHTMLをエスケープ
        foreach ($pathQuery as $key => $path) {
            $escapedPathQuery[$key] = htmlspecialchars($path, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return $escapedPathQuery;
    }
}
<?php
# -----------------------------
# ポートフォリオサイト本体 ブログ記事ページモデル
# 2018.09.29 s0hiba 初版作成
# 2019.01.20 s0hiba MyPDOクラスを使用開始
# 2019.03.23 s0hiba 入力チェックを導入
# 2019.03.24 s0hiba XSS対策導入
# 2019.11.24 s0hiba DB操作ログの保存を追加
# -----------------------------


//モデルコア抽象クラスのファイルを読み込む
include_once("{$projectDirPath}/apps/core/model.php");

class BlogArticleModel extends ModelCore
{
    private $articleId = 1;

    public function setArticleId($articleId)
    {
        //指定された値が正しい記事IDであれば、プロパティへ値をセット
        if ($this->isCorrectArticleId($articleId)) {
            $this->articleId = $articleId;
        }
    }

    public function getArticle()
    {
        //データ取得対象のキャッシュのキー名を指定
        $cacheKey = "portfolio_article_data_{$this->articleId}";

        //データ取得用のSQLクエリオブジェクトを用意
        $queryObj = $this->dataStore->createPlaneSqlQueryObj();
        $queryObj->setSelect('portfolio.blog_article');
        $queryObj->setJoin(array(
            'portfolio.article_tag_master' => 'article_tag_id',
        ));
        $queryObj->setWhere(array(
            array('key' => 'article_id', 'value' => $this->articleId, 'operator' => '=', 'type' => PDO::PARAM_INT),
        ));
        $queryObj->setSingleRowMode(true);

        //取得したデータを返す
        return $this->dataStore->getData($cacheKey, $queryObj);
    }

    public function getCommentList()
    {
        //データ取得対象のキャッシュのキー名を指定
        $cacheKey = "portfolio_comment_list_{$this->articleId}";

        //データ取得用のSQLクエリオブジェクトを用意
        $queryObj = $this->dataStore->createPlaneSqlQueryObj();
        $queryObj->setSelect('portfolio.blog_comment');
        $queryObj->setWhere(array(
            array('key' => 'article_id', 'value' => $this->articleId, 'operator' => '=', 'type' => PDO::PARAM_INT),
        ));
        $queryObj->setOrder(array(
            'comment_stamp ASC',
        ));

        //取得したデータを返す
        return $this->dataStore->getData($cacheKey, $queryObj);
    }

    public function writeComment($postArray, $execDateTime)
    {
        //現在の日時を取得
        $nowStamp = $execDateTime->format('Y-m-d H:i:s');

        //POSTリクエストのHTMLをエスケープ
        $commentTitle = htmlspecialchars($postArray['comment_title'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $commentUser = htmlspecialchars($postArray['comment_user'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $commentText = htmlspecialchars($postArray['comment_text'], ENT_QUOTES | ENT_HTML5, 'UTF-8');

        //消去対象のRedisキーを列挙
        $delCacheKeyList = array(
            "portfolio_comment_list_{$this->articleId}",
        );

        //クエリ組み立て
        $queryObj = $this->dataStore->createPlaneSqlQueryObj();
        $queryObj->setInsert('portfolio.blog_comment');
        $queryObj->setValue(array(
            array('key' => 'article_id',    'value' => $this->articleId,    'type' => PDO::PARAM_INT),
            array('key' => 'comment_title', 'value' => $commentTitle,       'type' => PDO::PARAM_STR),
            array('key' => 'comment_stamp', 'value' => $nowStamp,           'type' => PDO::PARAM_STR),
            array('key' => 'comment_user',  'value' => $commentUser,        'type' => PDO::PARAM_STR),
            array('key' => 'comment_text',  'value' => $commentText,        'type' => PDO::PARAM_STR),
        ));
        $queryObj->setReturning();
        $queryObj->setSingleRowMode(true);

        //データを書き込み、書き込んだデータを取得
        $insertedComment = $this->dataStore->writeData($delCacheKeyList, $queryObj);

        //追加したコメントを返す
        return $insertedComment;
    }

    public function writeDbActionLog($insertComment, $execDateTime)
    {
        //現在の日時を取得
        $nowStamp = $execDateTime->format('Y-m-d H:i:s');

        //DB操作結果を失敗として初期化
        $result = 2;

        //ログへ書き込むデータの配列を作成
        $logValue = array(
            array('key' => 'action_stamp', 'value' => $nowStamp, 'type' => PDO::PARAM_STR),
            array('key' => 'action_url', 'value' => "/blog/article/{$this->articleId}/", 'type' => PDO::PARAM_STR),
            array('key' => 'action_type_id', 'value' => 5, 'type' => PDO::PARAM_INT),
            array('key' => 'ip_address', 'value' => $_SERVER['REMOTE_ADDR'], 'type' => PDO::PARAM_STR),
            array('key' => 'action_db_table', 'value' => 'blog_comment', 'type' => PDO::PARAM_STR),
        );

        //追加したコメントのIDが正しく取得できている場合の処理
        if (isset($insertComment['comment_id']) && ctype_digit(strval($insertComment['comment_id'])) && $insertComment['comment_id'] > 0) {
            //ログへ書き込むデータにコメントのIDを追加
            $logValue[] = array('key' => 'action_data_id', 'value' => $insertComment['comment_id'], 'type' => PDO::PARAM_INT);

            //DB操作結果を成功とする
            $result = 1;
        }

        //ログへ書き込むデータ配列へ、DB操作結果の値を追加する
        $logValue[] = array('key' => 'action_result', 'value' => $result, 'type' => PDO::PARAM_INT);

        //クエリ組み立て
        $queryObj = $this->dataStore->createPlaneSqlQueryObj();
        $queryObj->setInsert('portfolio.action_log');
        $queryObj->setValue($logValue);
        $queryObj->setSingleRowMode(true);

        //データを書き込む
        $this->dataStore->writeData(array(), $queryObj);
    }

    public function isCorrectArticleId($articleId)
    {
        //0より大きい整数である場合はtrueを返す
        if (isset($articleId) && ctype_digit(strval($articleId)) && $articleId > 0) {
            return true;
        }

        //条件に合致しない場合はfalseを返す
        return false;
    }

    public function isCorrectCommentParam($commentParamArray)
    {
        //コメントデータの型と文字数をチェック
        if (isset($commentParamArray['comment_title']) && is_string($commentParamArray['comment_title']) && mb_strlen($commentParamArray['comment_title'])  <= 30
         || isset($commentParamArray['comment_user'])  && is_string($commentParamArray['comment_user']) &&  mb_strlen($commentParamArray['comment_user'])   <= 8
         || isset($commentParamArray['comment_text'])  && is_string($commentParamArray['comment_text']) &&  mb_strlen($commentParamArray['comment_text'])   <= 800) {
            //正しい形式であればtrueを返す
            return true;
        }

        //正しい形式でない場合はfalseを返す
        return false;
    }

    public function replaceCodeTag($articleData)
    {
        //開始タグを置換
        $resultText = preg_replace('/\#\#code start (.*)\#\#/u', '<code class="prettyprint linenums lang-${1}">', $articleData['article_text']);

        //終了タグを置換
        $resultText = str_replace('##code end##', '</code>', $resultText);

        //記事本文を置換後の文字列に置き換え、記事データを返す
        $articleData['article_text'] = $resultText;
        return $articleData;
    }

    /**
     * URLのaタグ変換
     * preg_replaceだと改行コードの処理が難しい為、preg_match_allを使用し手動でリプレイスしている
     * @param array $articleData
     * @return array
     */
    public function replaceUrl($articleData)
    {
        //文中からURLを検出
        preg_match_all("/^https?:.*$/um", $articleData['article_text'], $matchList);

        //URLが存在しなかった場合、そのままの記事データを返す
        if (!isset($matchList[0]) || !is_array($matchList[0]) || count($matchList[0]) === 0) {
            return $articleData;
        }

        //検出したURL配列から、重複を除去
        $matchListUnique = array_unique($matchList[0]);

        //全てのURLをaタグへ変換
        foreach ($matchListUnique as $matchStr) {
            $matchStrNoNl = str_replace(array("\n", "\r"), '', $matchStr);
            $replaceTag = '<a href="' . $matchStrNoNl . '" target="_blank">' . "{$matchStrNoNl}</a>";
            $articleData['article_text'] = str_replace($matchStrNoNl, $replaceTag, $articleData['article_text']);
        }

        //本文のURLをaタグへ置換した記事データを返す
        return $articleData;
    }

    public function postCommentToSlack($commentedArticle, $insertComment)
    {
        //slack通知用のcurl設定
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://hooks.slack.com/services/XXXXXXXX/XXXXXXXX/xxxxxxxx');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);

        //Slackにコメントを通知
        $slackPostParam = array(
            'payload' => json_encode(array(
                'channel'   => '#portfolio_comment',
                'text'      => "「{$commentedArticle['article_title']}」へのコメント\n\n{$insertComment['comment_text']}",
            ))
        );
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($slackPostParam));
        $slackPostResult = curl_exec($curl);

        //コメント通知リクエストの結果を返す
        return $slackPostResult;
    }
}

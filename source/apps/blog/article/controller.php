<?php
# -----------------------------
# ポートフォリオサイト本体 ブログ記事ページコントローラ
# 2018.09.28 s0hiba 初版作成
# 2019.03.21 s0hiba 入力チェックを導入
# 2021.01.13 s0hiba パス構造を変更
# 2021.04.26 s0hiba プロジェクトディレクトリパスを変数化
# -----------------------------


//コントローラコア抽象クラスのファイルを読み込む
include_once("{$projectDirPath}/apps/core/controller.php");

//依存するモデルクラスファイルを読み込む
include_once("{$projectDirPath}/apps/blog/article/model.php");

//共通HTML用のコントローラを読み込む
include_once("{$projectDirPath}/apps/common/footer/controller.php");
include_once("{$projectDirPath}/apps/common/header/controller.php");
include_once("{$projectDirPath}/apps/blog/common/navigation/controller.php");

class BlogArticleController extends ControllerCore
{
    private $model;

    public function action()
    {
        //共通フッターHTMLを生成
        $commonFooterController = new CommonFooterController(
            $this->dataStore, $this->viewSmarty, $this->execDateTime, $this->pathQuery, $this->postParam);
        $commonFooterHtml = $commonFooterController->action();

        //共通ヘッダーHTMLを生成
        $commonHeaderController = new CommonHeadtagController(
            $this->dataStore, $this->viewSmarty, $this->execDateTime, $this->pathQuery, $this->postParam);
        $commonHeaderHtml = $commonHeaderController->action();

        //ブログ共通ナビゲーションHTMLを生成
        $blogCommonNavigationController = new BlogCommonNavigationController(
            $this->dataStore, $this->viewSmarty, $this->execDateTime, $this->pathQuery, $this->postParam);
        $blogCommonNavigationHtml = $blogCommonNavigationController->action();

        //モデルを生成し、パスから記事IDを指定
        $this->model = new BlogArticleModel($this->dataStore);
        $this->model->setArticleId($this->pathQuery[2]);

        //記事を取得し、記事本文のHTMLタグ変換を実行
        $article = $this->model->getArticle();
        $article = $this->model->replaceCodeTag($article);
        $article = $this->model->replaceUrl($article);

        //POSTパラメータにコメントデータが正しく指定されていた場合、コメントを書き込む
        if ($this->isCorrectPostParam($this->postParam) && $this->model->isCorrectCommentParam($this->postParam)) {
            // POST処理を実行
            $insertedComment = $this->model->writeComment($this->postParam, $this->execDateTime);
            $this->model->writeDbActionLog($insertedComment, $this->execDateTime);
            $this->model->postCommentToSlack($article, $insertedComment);
        }

        //smartyに変数をアサイン
        $this->viewSmarty->assign(array(
            'article'               => $article,
            'commentList'           => $this->model->getCommentList(),
            'footerHtml'            => $commonFooterHtml,
            'headerHtml'            => $commonHeaderHtml,
            'blogNavigationHtml'    => $blogCommonNavigationHtml,
        ));

        //ビューHTMLの文字列を返す
        return $this->viewSmarty->fetch("../apps/blog/article/article.html");
    }
}

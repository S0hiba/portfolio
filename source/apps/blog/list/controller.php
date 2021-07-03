<?php
# -----------------------------
# ポートフォリオサイト本体 ブログ記事一覧ページコントローラ
# 2018.09.28 s0hiba 初版作成
# 2021.01.13 s0hiba パス構造を変更
# 2021.04.26 s0hiba プロジェクトディレクトリパスを変数化
# -----------------------------


//コントローラコア抽象クラスのファイルを読み込む
include_once("{$projectDirPath}/apps/core/controller.php");

//依存するモデルクラスファイルを読み込む
include_once("{$projectDirPath}/apps/blog/list/model.php");
include_once("{$projectDirPath}/apps/blog/list/backnumberModel.php");
include_once("{$projectDirPath}/apps/blog/list/tagModel.php");

//共通HTML用のコントローラを読み込む
include_once("{$projectDirPath}/apps/common/footer/controller.php");
include_once("{$projectDirPath}/apps/common/header/controller.php");
include_once("{$projectDirPath}/apps/blog/common/navigation/controller.php");

class BlogListController extends ControllerCore
{
    private $model;

    public function action()
    {
        //共通フッターHTMLを生成
        $commonFooterController = new CommonFooterController(
            $this->dataStore, $this->viewSmarty, $this->execDateTime, $this->pathQuery, $this->postParam);
        $commonFooterHtml = $commonFooterController->action($this->execDateTime);

        //共通ヘッダーHTMLを生成
        $commonHeaderController = new CommonHeadtagController(
            $this->dataStore, $this->viewSmarty, $this->execDateTime, $this->pathQuery, $this->postParam);
        $commonHeaderHtml = $commonHeaderController->action();

        //ブログ共通ナビゲーションHTMLを生成
        $blogCommonNavigationController = new BlogCommonNavigationController(
            $this->dataStore, $this->viewSmarty, $this->execDateTime, $this->pathQuery, $this->postParam);
        $blogCommonNavigationHtml = $blogCommonNavigationController->action();

        //パスに対して、HTML文字列をエスケープしておく
        $escapedPathQuery = $this->escapeHtmlFromPathQuery($this->pathQuery);

        //パスをもとにモデルを生成
        $this->model = $this->getModelFromPathQuery($escapedPathQuery);

        //表示対象記事の絞り込み条件の配列を取得
        $whereArray = $this->model->getWhereArray($this->execDateTime);

        //smartyに変数をアサイン
        $this->viewSmarty->assign(array(
            'breadcrumb'            => $this->model->getBreadcrumb($whereArray),
            'articleList'           => $this->model->getArticleList($whereArray),
            'page'                  => $this->model->getPageNum(),
            'pageAll'               => $this->model->getPageAll($whereArray),
            'optionPath'            => $this->model->getOptionPath(),
            'footerHtml'            => $commonFooterHtml,
            'headerHtml'            => $commonHeaderHtml,
            'blogNavigationHtml'    => $blogCommonNavigationHtml,
        ));

        //ビューHTMLの文字列を返す
        return $this->viewSmarty->fetch("../apps/blog/list/list.html");
    }

    public function getModelFromPathQuery($pathQuery)
    {
        //パスに応じたモデルオブジェクトを生成
        switch ($pathQuery[2]) {
            case 'backnumber':
                $model = new BlogListBacknumberModel($this->dataStore);
                $model->setBackNumberTargetMonth($pathQuery[3], $pathQuery[4]);
                $model->setPageNum($pathQuery[5]);

                break;
            case 'tag':
                $model = new BlogListTagModel($this->dataStore);
                $model->setTargetTagId($pathQuery[3]);
                $model->setPageNum($pathQuery[4]);

                break;
            default:
                $model = new BlogListModel($this->dataStore);
                $model->setPageNum($pathQuery[2]);
        }

        return $model;
    }
}

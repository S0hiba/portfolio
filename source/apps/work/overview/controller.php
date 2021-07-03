<?php
# -----------------------------
# ポートフォリオサイト本体 作品概要ページコントローラ
# 2018.10.10 s0hiba 初版作成
# 2019.03.22 s0hiba 入力チェックを導入
# 2021.01.13 s0hiba パス構造を変更
# 2021.04.26 s0hiba プロジェクトディレクトリパスを変数化
# -----------------------------


//コントローラコア抽象クラスのファイルを読み込む
include_once("{$projectDirPath}/apps/core/controller.php");

//依存するモデルクラスファイルを読み込む
include_once("{$projectDirPath}/apps/work/overview/model.php");

//共通HTML用のコントローラを読み込む
include_once("{$projectDirPath}/apps/common/footer/controller.php");
include_once("{$projectDirPath}/apps/common/header/controller.php");

class WorkOverviewController extends ControllerCore
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

        //モデルを生成し、パスから作品IDを指定
        $this->model = new WorkOverviewModel($this->dataStore);
        $this->model->setWorkId($this->pathQuery[2]);

        //smartyに変数をアサイン
        $this->viewSmarty->assign(array(
            'work'              => $this->model->getWork(),
            'technologyList'    => $this->model->getTechnologyList(),
            'footerHtml'        => $commonFooterHtml,
            'headerHtml'        => $commonHeaderHtml,
        ));

        //ビューHTMLの文字列を返す
        return $this->viewSmarty->fetch('../apps/work/overview/overview.html');
    }
}

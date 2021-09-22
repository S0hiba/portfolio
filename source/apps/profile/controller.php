<?php
# -----------------------------
# ポートフォリオサイト本体 プロフィールページコントローラ
# 2018.07.20 s0hiba 初版作成
# 2021.01.13 s0hiba パス構造を変更
# 2021.04.26 s0hiba プロジェクトディレクトリパスを変数化
# -----------------------------


//コントローラコア抽象クラスのファイルを読み込む
include_once("{$projectDirPath}/apps/core/controller.php");

//依存するモデルクラスファイルを読み込む
include_once("{$projectDirPath}/apps/profile/model.php");

//共通HTML用のコントローラを読み込む
include_once("{$projectDirPath}/apps/common/footer/controller.php");
include_once("{$projectDirPath}/apps/common/header/controller.php");

class ProfileController extends ControllerCore
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

        //モデルを生成
        $this->model = new ProfileModel($this->dataStore);

        //smartyに変数をアサイン
        $this->viewSmarty->assign(array(
            'age'               => $this->model->getAge($this->execDateTime),
            'tagList'           => $this->model->getTagList(),
            'skillList'         => $this->model->getSkillList(),
            'licenseList'       => $this->model->getLicenseList(),
            'headerHtml'        => $commonHeaderHtml,
            'footerHtml'        => $commonFooterHtml,
        ));

        //ビューHTMLの文字列を返す
        return $this->viewSmarty->fetch('../apps/profile/profile.html');
    }
}

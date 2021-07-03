<?php
# -----------------------------
# ポートフォリオサイト本体 HTML 共通ヘッダーコントローラ
# 2021.06.05 s0hiba 初版作成
# -----------------------------


//コントローラコア抽象クラスのファイルを読み込む
include_once("{$projectDirPath}/apps/core/controller.php");

//依存するモデルクラスファイルを読み込む
include_once("{$projectDirPath}/apps/common/header/model.php");

class CommonHeadtagController extends ControllerCore
{
    private $model;

    public function action()
    {
        //モデルを生成
        $this->model = new CommonHeaderModel($this->dataStore);

        //smartyに変数をアサイン
        $this->viewSmarty->assign(array(
            'keyVisualImageName' => $this->model->getKeyVisualImageName($this->pathQuery[0]),
        ));

        //ビューHTMLの文字列を返す
        return $this->viewSmarty->fetch('../apps/common/header/header.html');
    }
}

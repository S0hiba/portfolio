<?php
# -----------------------------
# ポートフォリオサイト本体 共通フッターコントローラ
# 2021.05.18 s0hiba 初版作成
# -----------------------------


//コントローラコア抽象クラスのファイルを読み込む
include_once("{$projectDirPath}/apps/core/controller.php");

//依存するモデルクラスファイルを読み込む
include_once("{$projectDirPath}/apps/common/footer/model.php");

class CommonFooterController extends ControllerCore
{
    private $model;

    public function action()
    {
        //モデルを生成
        $this->model = new CommonFooterModel($this->dataStore);

        //smartyに変数をアサイン
        $this->viewSmarty->assign(array(
            'articleListFooter' => $this->model->getArticleListFooter(),
            'workListFooter'    => $this->model->getWorkListFooter(),
            'nowYear'           => $this->execDateTime->format('Y'),
        ));

        //ビューHTMLの文字列を返す
        return $this->viewSmarty->fetch('../apps/common/footer/footer.html');
    }
}



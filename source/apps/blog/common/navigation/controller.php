<?php
# -----------------------------
# ポートフォリオサイト本体 ブログ共通ナビゲーションコントローラ
# 2021.05.18 s0hiba 初版作成
# -----------------------------


//コントローラコア抽象クラスのファイルを読み込む
include_once("{$projectDirPath}/apps/core/controller.php");

//依存するモデルクラスファイルを読み込む
include_once("{$projectDirPath}/apps/blog/common/navigation/model.php");

class BlogCommonNavigationController extends ControllerCore
{
    private $model;

    public function action()
    {
        //モデルを生成
        $this->model = new BlogCommonNavigationModel($this->dataStore);

        //smartyに変数をアサイン
        $this->viewSmarty->assign(array(
            'articleListNew'    => $this->model->getArticleListNew(),
            'tagList'           => $this->model->getTagList(),
            'monthList'         => $this->model->getMonthList(),
        ));

        //ビューHTMLの文字列を返す
        return $this->viewSmarty->fetch('../apps/blog/common/navigation/navigation.html');
    }
}



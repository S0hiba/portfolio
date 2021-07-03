<?php
# -----------------------------
# ポートフォリオサイト本体 ブログページコントローラ
# 2018.07.20 s0hiba 初版作成
# 2021.01.13 s0hiba パス構造を変更
# 2021.04.26 s0hiba プロジェクトディレクトリパスを変数化
# -----------------------------


//パスに応じて使用するコントローラを切り分ける
switch ($pathQuery[1]) {
    case 'article':
        include_once("{$projectDirPath}/apps/blog/{$pathQuery[1]}/controller.php");
        $controller = new BlogArticleController($dataStore, $smarty, $execDateTime, $pathQuery, $_POST);
        break;
    case 'list':
        include_once("{$projectDirPath}/apps/blog/{$pathQuery[1]}/controller.php");
        $controller = new BlogListController($dataStore, $smarty, $execDateTime, $pathQuery, $_POST);
        break;
    default:
        include_once("{$projectDirPath}/apps/blog/list/controller.php");
        $controller = new BlogListController($dataStore, $smarty, $execDateTime, $pathQuery, $_POST);
}

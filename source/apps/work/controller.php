<?php
# -----------------------------
# ポートフォリオサイト本体 作品ページコントローラ
# 2018.10.10 s0hiba 初版作成
# 2021.01.13 s0hiba パス構造を変更
# 2021.04.26 s0hiba プロジェクトディレクトリパスを変数化
# -----------------------------


//パスに応じて処理を切り分ける
switch ($pathQuery[1]) {
    case 'list':
        include_once("{$projectDirPath}/apps/work/{$pathQuery[1]}/controller.php");
        $controller = new WorkListController($dataStore, $smarty, $execDateTime, $pathQuery, $_POST);
        break;
    case 'overview':
        include_once("{$projectDirPath}/apps/work/{$pathQuery[1]}/controller.php");
        $controller = new WorkOverviewController($dataStore, $smarty, $execDateTime, $pathQuery, $_POST);
        break;
    default:
        include_once("{$projectDirPath}/apps/work/list/controller.php");
        $controller = new WorkListController($dataStore, $smarty, $execDateTime, $pathQuery, $_POST);
};

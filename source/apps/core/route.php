<?php
# -----------------------------
# ポートフォリオサイト本体 ルーティング記述
# 2021.09.04 s0hiba 初版作成
# -----------------------------


//各コントローラクラスファイルをインクルード
include_once("{$projectDirPath}/apps/blog/article/controller.php");
include_once("{$projectDirPath}/apps/blog/list/controller.php");
include_once("{$projectDirPath}/apps/counter/controller.php");
include_once("{$projectDirPath}/apps/profile/controller.php");
include_once("{$projectDirPath}/apps/top/controller.php");
include_once("{$projectDirPath}/apps/work/overview/controller.php");
include_once("{$projectDirPath}/apps/work/list/controller.php");

//ルーティング定義配列を作成
$routeArray = array(
    'blog/article'  => new BlogArticleController($dataStore, $smarty, $execDateTime, $pathQuery, $_POST),
    'blog/list'     => new BlogListController($dataStore, $smarty, $execDateTime, $pathQuery, $_POST),
    'counter'       => new CounterController($dataStore, $smarty, $execDateTime, $pathQuery, $_POST),
    'profile'       => new ProfileController($dataStore, $smarty, $execDateTime, $pathQuery, $_POST),
    'top'           => new TopController($dataStore, $smarty, $execDateTime, $pathQuery, $_POST),
    'work/overview' => new WorkOverviewController($dataStore, $smarty, $execDateTime, $pathQuery, $_POST),
    'work/list'     => new WorkListController($dataStore, $smarty, $execDateTime, $pathQuery, $_POST),
);

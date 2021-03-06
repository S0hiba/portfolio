<?php
# -----------------------------
# ポートフォリオサイト マスターコントローラ
# 2018.07.20 s0hiba 初版作成
# 2019.03.21 s0hiba パスの入力チェックを導入
# 2021.01.13 s0hiba パス構造を変更
# 2021.04.26 s0hiba プロジェクトディレクトリパスを変数化
# -----------------------------


//composer読み込み
require_once('../vendor/autoload.php');

//パスを初期化
$pathQuery = array();

//パスの文字エンコーディングと制御文字をチェック
$pathString = substr($_SERVER['REQUEST_URI'], 1, -1);
if (mb_check_encoding($pathString, 'UTF-8') && !preg_match('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', $pathString)) {
    //パスを取得
    $pathQuery = explode('/', $pathString);

    //パスの文字数をチェック
    if (isset($pathQuery) && is_array($pathQuery) && count($pathQuery) > 0) {
        foreach ($pathQuery as $tmpPath) {
            //英小文字と数字で1～13文字でないなら、パスを初期化
            if (!preg_match('/\A[a-z0-9]{1,13}\Z/ui', $tmpPath)) {
                $pathQuery = array();
            }
        }
    }
}

//現在日時をDateTimeオブジェクトで作成
$execDateTime = new DateTime();

//ドメインに応じて、プロジェクトディレクトリパスを切り分け
//[ memo ] 環境変数にしてしまった方がいいかも
$projectDirPath = '/mnt/homepage/develop/s0hiba.site/www.s0hiba.site';

//smartyをインスタンス化
$smarty = new Smarty();

//接続先DBのDSNを環境変数から取得し、DBクエリ実行オブジェクトを生成
$psqlHost = $_SERVER['PSQL_HOST'];
$psqlPort = $_SERVER['PSQL_PORT'];
$psqlUser = $_SERVER['PSQL_USER'];
$psqlPassword = $_SERVER['PSQL_PASSWORD'];
$psqlDbName = $_SERVER['PSQL_DB_NAME'];
$dsn = "pgsql:host={$psqlHost};port={$psqlPort};dbname={$psqlDbName};user={$psqlUser};password={$psqlPassword}";
$queryRunner = new PDOQueryRunner($dsn);

//Redisへ接続
$redisHost = $_SERVER['REDIS_HOST'];
$redisPort = $_SERVER['REDIS_PORT'];
$redis = new Redis();
$redis->connect($redisHost, $redisPort);

//データストアオブジェクトを生成
$dataStore = new QueryBuilderWithPhpRedisDataStore($redis, $queryRunner);

//現在の年を取得
$nowYear = $execDateTime->format('Y');

//ルーティング設定ファイルをインクルードし、ルーティング定義配列を取得
include_once("{$projectDirPath}/apps/core/route.php");
if (!isset($routeArray) || !is_array($routeArray) || count($routeArray) === 0) {
    //ルーティング定義配列が正しい配列形式でなかった場合、404エラーを出力し処理を終了
    header('HTTP/1.0 404 Not Found');
    exit;
}

//パスに応じたコントローラをルーティング定義配列から取得
foreach($routeArray as $routePath => $routeController) {
    if (strpos($pathString, $routePath) === 0) {
        $controller = $routeController;
        break;
    }
}

//ルーティング定義配列からコントローラが取得できなかった場合の処理
if (empty($controller)) {
    if (!empty($routeArray['top'])) {
        //パス「top」にコントローラが指定されている場合、それを取得
        $controller = $routeArray['top'];
    } else {
        //そうでない場合、404エラーを出力し処理を終了
        header('HTTP/1.0 404 Not Found');
        exit;
    }
}

//コントローラアクションを実行し、実行結果のHTMLを出力
$actionResultHtml = $controller->action();
print $actionResultHtml;

exit;

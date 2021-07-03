<?php
# -----------------------------
# ポートフォリオサイト本体 カウンタコントローラ
# 2021.06.05 s0hiba 旧カウンタ処理をベースに初版作成
# -----------------------------


//コントローラコア抽象クラスのファイルを読み込む
include_once("{$projectDirPath}/apps/core/controller.php");

//依存するモデルクラスファイルを読み込む
include_once("{$projectDirPath}/apps/counter/model.php");
include_once("{$projectDirPath}/apps/blog/list/model.php");
include_once("{$projectDirPath}/apps/blog/article/model.php");
include_once("{$projectDirPath}/apps/blog/list/backnumberModel.php");
include_once("{$projectDirPath}/apps/blog/list/tagModel.php");
include_once("{$projectDirPath}/apps/work/overview/model.php");

class CounterController extends ControllerCore
{
    private $model;

    public function action()
    {
        //カウント対象となるURLを取得
        $countTargetUrl = $this->getCountTargetUrl($this->pathQuery);

        //モデルを生成し、カウンタデータを書き込み
        $this->model = new CounterModel($this->dataStore);
        $this->model->writeCountData($countTargetUrl, $this->execDateTime);

        //画像出力用にcontent-typeヘッダーを出力
        header('Content-type: image/png');

        //ビューとして、1x1の透明画像を返す
        return file_get_contents(__DIR__ . '/view.png');
    }

    public function getCountTargetUrl($pathQuery)
    {
        //パスをチェックし、カウント用URLを生成
        switch ($pathQuery[1]) {
            case 'blog':
                $countTargetUrl = '/blog/';
                switch ($pathQuery[2]) {
                    case 'article':
                        $countTargetUrl .= $this->getBlogArticlePath($pathQuery[3]);
                        break;
                    case 'list':
                        $countTargetUrl .= 'list/';
                        switch ($pathQuery[3]) {
                            case 'backnumber':
                                $countTargetUrl .= $this->getBlogListBacknumberPath($pathQuery[4], $pathQuery[5]);
                                break;
                            case 'tag':
                                $countTargetUrl .= $this->getBlogListTagPath($pathQuery[4]);
                                break;
                        }
                        break;
                }
                break;
            case 'profile':
                $countTargetUrl = '/profile/';
                break;
            case 'top':
                $countTargetUrl = '/top/';
                break;
            case 'work':
                $countTargetUrl = '/work/';
                switch ($pathQuery[2]) {
                    case 'list':
                        $countTargetUrl .= 'list/';
                        break;
                    case 'overview':
                        $countTargetUrl .= $this->getWorkOverviewPath($pathQuery[3]);
                }
        }

        return $countTargetUrl;
    }

    public function getBlogArticlePath($pathArticleId)
    {
        //パラメータチェック用に、ブログ記事モデルを生成
        $model = new BlogArticleModel($this->dataStore);

        //ブログ記事のパスを指定
        $blogArticlePath = 'article/';


        //パスに指定されたブログ記事IDが正しい値だった場合、パスにIDを追加
        if ($model->isCorrectArticleId($pathArticleId)) {
            $blogArticlePath .= "{$pathArticleId}/";
        }

        //ブログ記事のパスを返す
        return $blogArticlePath;
    }

    public function getBlogListBacknumberPath($pathYearNum, $pathMonthNum)
    {
        //パラメータチェック用に、ブログ記事バックナンバー一覧モデルを生成
        $model = new BlogListBacknumberModel($this->dataStore);

        //ブログバックナンバーのパスを指定
        $blogListBacknumberPath = 'backnumber/';

        //パスに指定された年月が正しい値だった場合、パスに年月を追加
        if ($model->isCorrectYearNum($pathYearNum) && $model->isCorrectMonthNum($pathMonthNum)) {
            $blogListBacknumberPath .= "{$pathYearNum}/{$pathMonthNum}/";
        }

        //ブログバックナンバーのパスを返す
        return $blogListBacknumberPath;
    }

    public function getBlogListTagPath($pathTagId)
    {
        //パラメータチェック用に、タグ別ブログ記事一覧モデルを生成
        $model = new BlogListTagModel($this->dataStore);

        //タグ別ブログ記事一覧のパスを指定
        $blogListTagPath = 'tag/';

        //パスに指定されたタグIDが正しい値だった場合、パスにタグIDを追加
        if ($model->isCorrectTargetTagId($pathTagId)) {
            $blogListTagPath .= "{$pathTagId}/";
        }

        //タグ別ブログ記事一覧のパスを返す
        return $pathTagId;
    }

    public function getWorkOverviewPath($pathWorkId)
    {
        //パラメータチェック用に、作品詳細モデルを生成
        $model = new WorkOverviewModel($this->dataStore);

        //作品詳細のパスを指定
        $workOverviewPath = 'overview/';

        //パスに指定された作品IDが正しい値だった場合、パスに作品IDを追加
        if ($model->isCorrectWorkId($pathWorkId)) {
            $workOverviewPath .= "{$pathWorkId}/";
        }

        //作品詳細のパスを返す
        return $workOverviewPath;
    }
}
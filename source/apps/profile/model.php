<?php
# -----------------------------
# ポートフォリオサイト本体 プロフィールページモデル
# 2021.05.24 s0hiba 初版作成
# -----------------------------


//モデルコア抽象クラスのファイルを読み込む
include_once("{$projectDirPath}/apps/core/model.php");

class ProfileModel extends ModelCore
{
    public function getAge($execDateTime)
    {
        //誕生日から年齢を算出
        $birthDay = new DateTime('1995-09-18');
        $ageInterval = $execDateTime->diff($birthDay);
        return $ageInterval->format('%Y');
    }

    public function getTagList()
    {
        //データ取得対象のキャッシュのキー名を指定
        $cacheKey = 'portfolio_technology_tag_list';

        //データ取得用のSQLクエリオブジェクトを用意
        $queryObj = $this->dataStore->createPlaneSqlQueryObj();
        $queryObj->setSelect('portfolio.technology_tag_master');
        $queryObj->setOrder(array('technology_tag_sort_no ASC'));

        //データを取得
        $tagArray = $this->dataStore->getData($cacheKey, $queryObj);

        //取得したデータが正しい配列形式ではない場合、空の配列を返す
        if (!isset($tagArray) || !is_array($tagArray) || count($tagArray) <= 0) {
            return array();
        }

        //IDをキーとする配列に整形
        foreach ($tagArray as $tagRow) {
            $tagList[$tagRow['technology_tag_id']] = $tagRow;
        }

        //整形した配列を返す
        return $tagList;
    }

    public function getSkillList()
    {
        //データ取得対象のキャッシュのキー名を指定
        $cacheKey = 'portfolio_skill_list';

        //データ取得用のSQLクエリオブジェクトを用意
        $queryObj = $this->dataStore->createPlaneSqlQueryObj();
        $queryObj->setSelect('portfolio.technology_skill');
        $queryObj->setJoin(array(
            'portfolio.technology_master'       => 'technology_id',
            'portfolio.technology_tag_master'   => 'technology_tag_id',
        ));
        $queryObj->setOrder(array(
            'technology_tag_sort_no',
            'skill_sort_no ASC'
        ));

        //データを取得
        $skillArray = $this->dataStore->getData($cacheKey, $queryObj);

        //取得したデータが正しい配列形式ではない場合、空の配列を返す
        if (!isset($skillArray) || !is_array($skillArray) || count($skillArray) <= 0) {
            return array();
        }

        //取得したデータから、技術種別ごとのスキル一覧を作成
        foreach($skillArray as $skillRow) {
            //技術名から画像ファイルパスを生成
            $technologyNameLower = strtolower($skillRow['technology_name']);
            $imageName = str_replace(' ', '_', $technologyNameLower);
            $skillRow['technology_image'] = "https://cdn.s0hiba.site/img/{$imageName}.png";

            //技術種別ごとのスキル一覧の配列に要素を追加
            $skillList[$skillRow['technology_tag_id']][] = $skillRow;
        }

        //作成した配列を返す
        return $skillList;
    }
}

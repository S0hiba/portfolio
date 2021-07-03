<?php
# -----------------------------
# ポートフォリオサイト本体 HTML 共通ヘッダーコントローラ
# 2021.06.05 s0hiba 初版作成
# -----------------------------


//モデルコア抽象クラスのファイルを読み込む
include_once("{$projectDirPath}/apps/core/model.php");

class CommonHeaderModel extends ModelCore
{
    public function getKeyVisualImageName($pageName)
    {
        //ページ名に応じたキービジュアル画像名を返す
        switch ($pageName) {
            case 'blog':
            case 'profile':
            case 'top':
            case 'work':
                return "keyVisual_{$pageName}";
            default:
                return "keyVisual_top";
        }
    }
}
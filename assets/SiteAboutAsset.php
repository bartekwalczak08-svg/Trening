<?php

/**
 * Site about page asset bundle.
 */
namespace app\assets;

use yii\web\AssetBundle;

class SiteAboutAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site-about.css',
    ];
    public $depends = [
        'app\\assets\\ThemeAsset',
    ];
}

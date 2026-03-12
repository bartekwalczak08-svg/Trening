<?php

/**
 * Site index page asset bundle.
 */
namespace app\assets;

use yii\web\AssetBundle;

class SiteIndexAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site-index.css',
    ];
    public $depends = [
        'app\\assets\\ThemeAsset',
    ];
}

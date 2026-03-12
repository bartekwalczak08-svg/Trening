<?php

/**
 * Theme asset bundle for app-specific styles and scripts.
 */
namespace app\assets;

use yii\web\AssetBundle;

class ThemeAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
    ];
    public $js = [
        'js/change-theme.js',
    ];
    public $depends = [
        'app\\assets\\AppAsset',
    ];
}

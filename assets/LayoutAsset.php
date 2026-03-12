<?php

/**
 * Layout-specific asset bundle.
 */
namespace app\assets;

use yii\web\AssetBundle;

class LayoutAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/layout.css',
    ];
    public $depends = [
        'app\\assets\\ThemeAsset',
    ];
}

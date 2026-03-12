<?php

/**
 * Dashboard page asset bundle.
 */
namespace app\assets;

use yii\web\AssetBundle;

class DashboardAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/dashboard-index.css',
    ];
    public $depends = [
        'app\\assets\\ThemeAsset',
    ];
}

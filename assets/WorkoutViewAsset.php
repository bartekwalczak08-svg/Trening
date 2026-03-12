<?php

/**
 * Workout details view page asset bundle.
 */
namespace app\assets;

use yii\web\AssetBundle;

class WorkoutViewAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/workout-view.css',
    ];
    public $depends = [
        'app\\assets\\ThemeAsset',
    ];
}

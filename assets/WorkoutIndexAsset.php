<?php

/**
 * Workout index page asset bundle.
 */
namespace app\assets;

use yii\web\AssetBundle;

class WorkoutIndexAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/workout-index.css',
    ];
    public $depends = [
        'app\\assets\\ThemeAsset',
    ];
}

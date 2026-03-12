<?php

/**
 * Workout calendar page asset bundle.
 */
namespace app\assets;

use yii\web\AssetBundle;

class WorkoutCalendarAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/workout-calendar.css',
    ];
    public $depends = [
        'app\\assets\\ThemeAsset',
    ];
}

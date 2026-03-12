<?php

/**
 * Workout exercise create/update form asset bundle.
 */
namespace app\assets;

use yii\web\AssetBundle;

class WorkoutExerciseFormAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/workout-exercise-form.css',
    ];
    public $depends = [
        'app\\assets\\ThemeAsset',
    ];
}

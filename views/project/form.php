<?php

use app\models\Project;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/* @var Project $project */

$form = ActiveForm::begin(
    [
        'action' => [Url::to('project/form')],
        'method' => 'POST',
        'layout' => 'horizontal'
    ]
);
?>

<h3 class="m-0 mt-1"><?= Yii::t("app", "Create Project") ?></h3>
<hr class="mt-1"/>
<?= $form->field($project, 'title')->input('text') ?>
<?= $form->field($project, 'description')->input('text') ?>
<?= $form->field($project, 'technologies')->input('text') ?>
<?= $form->field($project, 'image_url')->input('text') ?>
<?= $form->field($project, 'link')->input('text') ?>


<?php
echo Html::submitButton(
    'Create',
    ['class' => 'btn btn-success float-right mb-2']
);
ActiveForm::end();
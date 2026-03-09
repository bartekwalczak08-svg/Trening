<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\generated\Workouts $model */

$this->title = 'Dodaj trening';
$this->params['breadcrumbs'][] = ['label' => 'Plany treningowe', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="workout-create">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="workout-form">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

        <div class="form-group">
            <?= Html::submitButton('Zapisz', ['class' => 'btn btn-success']) ?>
            <?= Html::a('Anuluj', ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

<?php

/** @var yii\web\View $this */
/** @var app\models\SignupForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Sign up';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-signup">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Please fill out the following fields to register:</p>

    <?php $form = ActiveForm::begin([
        'id' => 'signup-form',
    ]); ?>

    <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>

    <?= $form->field($model, 'email')->input('email') ?>

    <?= $form->field($model, 'password')->passwordInput() ?>

    <?= $form->field($model, 'passwordRepeat')->passwordInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Sign up', ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

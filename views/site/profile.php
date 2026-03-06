<?php

/** @var yii\web\View $this */
/** @var app\models\ChangeCredentialsForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Profile';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-profile">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin([ 'id' => 'profile-form' ]); ?>

    <?= $form->field($model, 'username')->textInput() ?>

    <?= $form->field($model, 'email')->input('email') ?>

    <?= $form->field($model, 'currentPassword')->passwordInput() ?>

    <?= $form->field($model, 'newPassword')->passwordInput() ?>

    <?= $form->field($model, 'newPasswordRepeat')->passwordInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save changes', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

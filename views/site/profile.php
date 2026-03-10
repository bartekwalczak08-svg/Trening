<?php

/**
 * Opis: Widok odpowiedzialny za renderowanie interfejsu uytkownika.
 */


/** @var yii\web\View $this */
/** @var app\models\ChangeCredentialsForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Profil';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-profile">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin([ 'id' => 'profile-form' ]); ?>

    <?= $form->field($model, 'username')->textInput() ?>

    <?= $form->field($model, 'email')->input('email') ?>

    <?= $form->field($model, 'currentPassword', [
        'template' => "{label}\n<div class=\"input-group\">{input}<button class=\"btn btn-outline-secondary toggle-password\" type=\"button\" data-target=\"profile-current-password\" aria-label=\"Toggle password visibility\"><i class=\"bi bi-eye\"></i></button></div>\n{error}",
        'errorOptions' => ['class' => 'invalid-feedback d-block'],
    ])->passwordInput(['id' => 'profile-current-password', 'autocomplete' => 'off', 'value' => '']) ?>

    <?= $form->field($model, 'newPassword', [
        'template' => "{label}\n<div class=\"input-group\">{input}<button class=\"btn btn-outline-secondary toggle-password\" type=\"button\" data-target=\"profile-new-password\" aria-label=\"Toggle password visibility\"><i class=\"bi bi-eye\"></i></button></div>\n{error}",
        'errorOptions' => ['class' => 'invalid-feedback d-block'],
    ])->passwordInput(['id' => 'profile-new-password', 'autocomplete' => 'new-password', 'value' => '']) ?>

    <?= $form->field($model, 'newPasswordRepeat', [
        'template' => "{label}\n<div class=\"input-group\">{input}<button class=\"btn btn-outline-secondary toggle-password\" type=\"button\" data-target=\"profile-new-password-repeat\" aria-label=\"Toggle password visibility\"><i class=\"bi bi-eye\"></i></button></div>\n{error}",
        'errorOptions' => ['class' => 'invalid-feedback d-block'],
    ])->passwordInput(['id' => 'profile-new-password-repeat', 'autocomplete' => 'new-password', 'value' => '']) ?>

    <div class="form-group">
        <?= Html::submitButton('Zapisz zmiany', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php $this->registerJs(<<<'JS'
(function() {
    var toggleButtons = document.querySelectorAll('.toggle-password');
    toggleButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var input = document.getElementById(this.getAttribute('data-target'));
            if (!input) return;
            var icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                if (icon) {
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                }
            } else {
                input.type = 'password';
                if (icon) {
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            }
        });
    });
})();
JS
); ?>

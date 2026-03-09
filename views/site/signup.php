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

    <?= $form->field($model, 'password', [
        'template' => "{label}\n<div class=\"input-group\">{input}<button class=\"btn btn-outline-secondary toggle-password\" type=\"button\" data-target=\"signup-password\" aria-label=\"Toggle password visibility\"><i class=\"bi bi-eye\"></i></button></div>\n{error}",
        'errorOptions' => ['class' => 'invalid-feedback d-block'],
    ])->passwordInput(['id' => 'signup-password']) ?>

    <?= $form->field($model, 'passwordRepeat', [
        'template' => "{label}\n<div class=\"input-group\">{input}<button class=\"btn btn-outline-secondary toggle-password\" type=\"button\" data-target=\"signup-password-repeat\" aria-label=\"Toggle password visibility\"><i class=\"bi bi-eye\"></i></button></div>\n{error}",
        'errorOptions' => ['class' => 'invalid-feedback d-block'],
    ])->passwordInput(['id' => 'signup-password-repeat']) ?>

    <div class="form-group">
        <?= Html::submitButton('Sign up', ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
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

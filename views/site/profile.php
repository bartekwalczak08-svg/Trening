<?php

/**
 * Opis: Widok odpowiedzialny za renderowanie interfejsu uytkownika.
 */


/** @var yii\web\View $this */
/** @var app\models\ChangeCredentialsForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = Yii::t('app', 'Profil');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-profile">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin(['id' => 'profile-form']); ?>

    <?= $form->field($model, 'username')->textInput() ?>

    <?= $form->field($model, 'email')->input('email') ?>

    <?= $form->field($model, 'currentPassword', [
        'template' => "{label}\n<div class=\"input-group\">{input}<button class=\"btn btn-outline-secondary toggle-password\" type=\"button\" data-target=\"profile-current-password\" aria-label=\"" . Yii::t('app', 'Pokaż lub ukryj hasło') . "\"><i class=\"bi bi-eye\"></i></button></div>\n{error}",
        'errorOptions' => ['class' => 'invalid-feedback d-block'],
    ])->passwordInput(['id' => 'profile-current-password', 'autocomplete' => 'off', 'value' => '']) ?>

    <?= $form->field($model, 'newPassword', [
        'template' => "{label}\n<div class=\"input-group\">{input}<button class=\"btn btn-outline-secondary toggle-password\" type=\"button\" data-target=\"profile-new-password\" aria-label=\"" . Yii::t('app', 'Pokaż lub ukryj hasło') . "\"><i class=\"bi bi-eye\"></i></button></div>\n{error}",
        'errorOptions' => ['class' => 'invalid-feedback d-block'],
    ])->passwordInput(['id' => 'profile-new-password', 'autocomplete' => 'new-password', 'value' => '']) ?>

    <?= $form->field($model, 'newPasswordRepeat', [
        'template' => "{label}\n<div class=\"input-group\">{input}<button class=\"btn btn-outline-secondary toggle-password\" type=\"button\" data-target=\"profile-new-password-repeat\" aria-label=\"" . Yii::t('app', 'Pokaż lub ukryj hasło') . "\"><i class=\"bi bi-eye\"></i></button></div>\n{error}",
        'errorOptions' => ['class' => 'invalid-feedback d-block'],
    ])->passwordInput(['id' => 'profile-new-password-repeat', 'autocomplete' => 'new-password', 'value' => '']) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Zapisz zmiany'), ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

    <hr class="my-4">

    <?php // Dedicated account lifecycle actions (deactivate / delayed delete). ?>
    <section class="danger-zone" aria-labelledby="danger-zone-title">
        <div class="danger-zone-badge mb-2"><?= Yii::t('app', 'Strefa konta') ?></div>
        <h5 id="danger-zone-title" class="danger-zone-title mb-2"><?= Yii::t('app', 'Zarządzanie kontem') ?></h5>
        <p class="danger-zone-text mb-3"><?= Yii::t('app', 'Możesz dezaktywować konto lub oznaczyć je do usunięcia po 30 dniach.') ?></p>

        <div class="d-flex flex-wrap gap-2 align-items-start">
            <?= Html::beginForm(['/site/deactivate-account'], 'post', ['class' => 'd-inline']) ?>
            <?= Html::submitButton(Yii::t('app', 'Dezaktywuj konto'), [
                'class' => 'btn btn-outline-warning',
                'data' => [
                    'confirm' => Yii::t('app', 'Czy na pewno chcesz dezaktywować konto?'),
                ],
            ]) ?>
            <?= Html::endForm() ?>

            <?= Html::beginForm(['/site/cancel-delete-account'], 'post', ['class' => 'd-inline']) ?>
            <?= Html::submitButton(Yii::t('app', 'Anuluj usunięcie konta'), [
                'class' => 'btn btn-outline-success',
            ]) ?>
            <?= Html::endForm() ?>
        </div>

        <?= Html::beginForm(['/site/delete-account'], 'post', ['class' => 'mt-3']) ?>
        <label for="delete-account-password" class="form-label mb-1"><?= Yii::t('app', 'Potwierdź aktualnym hasłem') ?></label>
        <div class="d-flex flex-column flex-sm-row gap-2 align-items-stretch align-items-sm-start">
            <div class="input-group" style="max-width: 360px;">
                <?= Html::passwordInput('delete_account_password', '', [
                    'id' => 'delete-account-password',
                    'class' => 'form-control',
                    'placeholder' => Yii::t('app', 'Wpisz aktualne hasło'),
                    'autocomplete' => 'current-password',
                    'required' => true,
                ]) ?>
                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="delete-account-password" aria-label="<?= Yii::t('app', 'Pokaż lub ukryj hasło') ?>">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
            <?= Html::submitButton(Yii::t('app', 'Usuń konto (30 dni)'), [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => Yii::t('app', 'Czy na pewno chcesz oznaczyć konto do usunięcia za 30 dni?'),
                ],
            ]) ?>
        </div>
        <?= Html::endForm() ?>
    </section>
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

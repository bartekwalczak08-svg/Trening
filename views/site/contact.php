<?php

/**
 * Opis: Widok odpowiedzialny za renderowanie interfejsu użytkownika.
 */


/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\models\ContactForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\captcha\Captcha;

$this->title = 'Kontakt';
$this->params['breadcrumbs'][] = $this->title;
$captchaAvailable = \app\models\ContactForm::isCaptchaAvailable();
?>
<div class="site-contact">
    <section class="card p-4 mb-4">
        <h1 class="mb-2"><?= Html::encode($this->title) ?></h1>
        <p class="text-muted mb-0">
            Masz pytanie, pomysł na nową funkcję albo trafiłeś na błąd?
            Napisz do nas przez formularz poniżej.
        </p>
    </section>

    <?php if (Yii::$app->session->hasFlash('contactFormSubmitted')): ?>

        <div class="alert alert-success mb-0">
            Dziękujemy za wiadomość. Odpowiemy tak szybko, jak to możliwe.
        </div>

    <?php else: ?>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card p-4 h-100">
                    <?php $form = ActiveForm::begin(['id' => 'contact-form']); ?>

                        <?= $form->field($model, 'name')->textInput([
                            'autofocus' => true,
                            'placeholder' => 'Twoje imię',
                        ]) ?>

                        <?= $form->field($model, 'email')->input('email', [
                            'placeholder' => 'twój@email.pl',
                        ]) ?>

                        <?= $form->field($model, 'subject')->textInput([
                            'placeholder' => 'Temat wiadomości',
                        ]) ?>

                        <?= $form->field($model, 'body')->textarea([
                            'rows' => 6,
                            'placeholder' => 'Napisz, w czym możemy pomóc...',
                        ]) ?>

                        <?php if ($captchaAvailable): ?>
                            <?= $form->field($model, 'verifyCode')->widget(Captcha::class, [
                                'template' => '<div class="row g-2 align-items-center"><div class="col-sm-4">{image}</div><div class="col-sm-8">{input}</div></div>',
                            ]) ?>
                        <?php else: ?>
                            <div class="alert alert-info">
                                Captcha jest tymczasowo wyłączona, ponieważ brakuje rozszerzenia GD/FreeType lub ImageMagick.
                            </div>
                        <?php endif; ?>

                        <div class="form-group mt-2">
                            <?= Html::submitButton('Wyślij wiadomość', ['class' => 'btn btn-primary', 'name' => 'contact-button']) ?>
                        </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card p-4 h-100">
                    <h2 class="h5 mb-3">Informacje dodatkowe</h2>
                    <ul class="text-muted mb-3">
                        <li>odpowiadamy zazwyczaj w ciągu 24-48 godzin,</li>
                        <li>im więcej szczegółów podasz, tym szybciej pomożemy,</li>
                        <li>błędy techniczne opisz krok po kroku.</li>
                    </ul>
                    <?php if (Yii::$app->mailer->useFileTransport): ?>
                        
                    <?php endif; ?>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>

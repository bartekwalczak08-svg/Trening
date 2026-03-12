<?php

/**
 * Opis: Widok odpowiedzialny za renderowanie interfejsu uytkownika.
 */


use app\assets\WorkoutExerciseFormAsset;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\models\WorkoutExercises $model */
/** @var app\models\Workouts $workout */
/** @var app\models\Exercises[] $exercises */

$this->title = Yii::t('app', 'Dodaj ćwiczenie');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Plany treningowe'), 'url' => ['index']];
    $this->params['breadcrumbs'][] = ['label' => $workout->name, 'url' => ['view', 'id' => $workout->id]];
$this->params['breadcrumbs'][] = $this->title;
WorkoutExerciseFormAsset::register($this);

$durationUnit = 'sec';
if (method_exists($model, 'hasAttribute') && $model->hasAttribute('duration_unit')) {
    $durationUnit = $model->getAttribute('duration_unit') ?: 'sec';
}
?>

<div class="workout-exercise-create">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="bi bi-plus-circle me-2"></i><?= Html::encode($this->title) ?></h4>
        </div>

        <div class="card-body">
            <p class="workout-form-muted mb-4">
                <i class="bi bi-info-circle me-1"></i>
                <?= Yii::t('app', 'Trening') ?>: <strong><?= Html::encode($workout->name) ?></strong>
            </p>

            <?php $form = ActiveForm::begin(); ?>

            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-bold">
                        <i class="bi bi-list-ul me-1"></i><?= Yii::t('app', 'Wybierz ćwiczenie z listy') ?>
                    </label>
                    <?= Html::dropDownList(
                        'WorkoutExercises[exercise_id]',
                        $model->exercise_id,
                        ArrayHelper::map($exercises, 'id', 'name'),
                        ['class' => 'form-select form-select-lg', 'prompt' => Yii::t('app', '-- Wybierz ćwiczenie --')]
                    ) ?>
                    <?= Html::error($model, 'exercise_id', ['class' => 'invalid-feedback d-block']) ?>
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">
                        <i class="bi bi-plus me-1"></i><?= Yii::t('app', 'Lub wpisz nazwę nowego ćwiczenia') ?>
                    </label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light"><i class="bi bi-pencil"></i></span>
                        <input type="text" name="custom_exercise_name" class="form-control" placeholder="<?= Yii::t('app', 'Wpisz nazwę ćwiczenia') ?>">
                    </div>
                    <div class="form-text workout-form-muted">
                        <i class="bi bi-info-circle me-1"></i><?= Yii::t('app', 'Jeśli wpiszesz nowe ćwiczenie, zostanie ono dodane do listy.') ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold"><i class="bi bi-layers me-1"></i><?= Yii::t('app', 'Serie') ?></label>
                    <div class="input-group">
                        <input type="text" name="WorkoutExercises[sets]" class="form-control form-control-lg" placeholder="<?= Yii::t('app', 'np. 3') ?>" value="<?= Html::encode($model->sets) ?>">
                        <span class="input-group-text bg-light"><?= Yii::t('app', 'serii') ?></span>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold"><i class="bi bi-arrow-repeat me-1"></i><?= Yii::t('app', 'Powtórzenia') ?></label>
                    <div class="input-group">
                        <input type="text" name="WorkoutExercises[reps]" class="form-control form-control-lg" placeholder="<?= Yii::t('app', 'np. 8-12') ?>" value="<?= Html::encode($model->reps) ?>">
                        <span class="input-group-text bg-light"><?= Yii::t('app', 'powt.') ?></span>
                    </div>
                    <div class="form-text workout-form-muted"><?= Yii::t('app', 'Wpisz zakres np. 6-10, 8-12, 10-15') ?></div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold"><i class="bi bi-clock me-1"></i><?= Yii::t('app', 'Czas') ?></label>
                    <div class="input-group">
                        <input type="number" min="0" name="WorkoutExercises[duration_sec]" class="form-control" placeholder="<?= Yii::t('app', 'np. 60') ?>" value="<?= Html::encode($model->duration_sec) ?>">
                        <?= Html::dropDownList(
                            'WorkoutExercises[duration_unit]',
                            $durationUnit,
                            ['sec' => Yii::t('app', 'sek'), 'min' => Yii::t('app', 'min')],
                            ['class' => 'form-select', 'style' => 'max-width: 95px;']
                        ) ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold"><i class="bi bi-hourglass-split me-1"></i><?= Yii::t('app', 'Odpoczynek (sekundy)') ?></label>
                    <div class="input-group">
                        <input type="number" min="0" name="WorkoutExercises[rest_sec]" class="form-control" placeholder="<?= Yii::t('app', 'np. 60') ?>" value="<?= Html::encode($model->rest_sec ?? 60) ?>">
                        <span class="input-group-text bg-light"><?= Yii::t('app', 'sek') ?></span>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-between align-items-center">
                <?= Html::a('<i class="bi bi-arrow-left me-1"></i>' . Yii::t('app', 'Anuluj'), ['view', 'id' => $workout->id], ['class' => 'btn btn-outline-secondary btn-lg']) ?>
                <?= Html::submitButton('<i class="bi bi-check-lg me-1"></i>' . Yii::t('app', 'Zapisz'), ['class' => 'btn btn-success btn-lg']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>


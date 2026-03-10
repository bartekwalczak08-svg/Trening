<?php

/**
 * Opis: Widok odpowiedzialny za renderowanie interfejsu uytkownika.
 */


use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\models\WorkoutExercises $model */
/** @var app\models\Workouts $workout */
/** @var app\models\Exercises[] $exercises */

$this->title = 'Edytuj ćwiczenie';
$this->params['breadcrumbs'][] = ['label' => 'Plany treningowe', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $workout->name, 'url' => ['view', 'id' => $workout->id]];
$this->params['breadcrumbs'][] = 'Edytuj';

$durationUnit = 'sec';
$durationValue = $model->duration_sec;

if (method_exists($model, 'hasAttribute') && $model->hasAttribute('duration_unit')) {
    $storedUnit = $model->getAttribute('duration_unit');
    if ($storedUnit === 'min' || $storedUnit === 'sec') {
        $durationUnit = $storedUnit;
    }
}

if ($durationValue !== null && $durationUnit === 'min') {
    $durationValue = $durationValue / 60;
}
?>

<div class="workout-exercise-update">
    <div class="card shadow-sm">
        <div class="card-header bg-warning text-dark">
            <h4 class="mb-0"><i class="bi bi-pencil-square me-2"></i><?= Html::encode($this->title) ?></h4>
        </div>

        <div class="card-body">
            <p class="workout-form-muted mb-4">
                <i class="bi bi-info-circle me-1"></i>
                Trening: <strong><?= Html::encode($workout->name) ?></strong>
            </p>

            <?php $form = ActiveForm::begin(); ?>

            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-bold">
                        <i class="bi bi-list-ul me-1"></i>Wybierz ćwiczenie z listy
                    </label>
                    <?= Html::dropDownList(
                        'WorkoutExercises[exercise_id]',
                        $model->exercise_id,
                        ArrayHelper::map($exercises, 'id', 'name'),
                        ['class' => 'form-select form-select-lg']
                    ) ?>
                    <?= Html::error($model, 'exercise_id', ['class' => 'invalid-feedback d-block']) ?>
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">
                        <i class="bi bi-plus me-1"></i>Lub wpisz nazwę nowego ćwiczenia
                    </label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light"><i class="bi bi-pencil"></i></span>
                        <input type="text" name="custom_exercise_name" class="form-control" placeholder="Wpisz nazwę ćwiczenia">
                    </div>
                    <div class="form-text workout-form-muted">
                        <i class="bi bi-info-circle me-1"></i>Jeśli wpiszesz nowe ćwiczenie, zostanie ono dodane do listy.
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold"><i class="bi bi-layers me-1"></i>Serie</label>
                    <div class="input-group">
                        <input type="text" name="WorkoutExercises[sets]" class="form-control form-control-lg" placeholder="np. 3" value="<?= Html::encode($model->sets) ?>">
                        <span class="input-group-text bg-light">serii</span>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold"><i class="bi bi-arrow-repeat me-1"></i>Powtórzenia</label>
                    <div class="input-group">
                        <input type="text" name="WorkoutExercises[reps]" class="form-control form-control-lg" placeholder="np. 8-12" value="<?= Html::encode($model->reps) ?>">
                        <span class="input-group-text bg-light">powt.</span>
                    </div>
                    <div class="form-text workout-form-muted">Wpisz zakres np. 6-10, 8-12, 10-15</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold"><i class="bi bi-clock me-1"></i>Czas</label>
                    <div class="input-group">
                        <input type="number" min="0" name="WorkoutExercises[duration_sec]" class="form-control" placeholder="np. 60" value="<?= Html::encode($durationValue) ?>">
                        <?= Html::dropDownList(
                            'WorkoutExercises[duration_unit]',
                            $durationUnit,
                            ['sec' => 'sek', 'min' => 'min'],
                            ['class' => 'form-select', 'style' => 'max-width: 95px;']
                        ) ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold"><i class="bi bi-hourglass-split me-1"></i>Odpoczynek (sekundy)</label>
                    <div class="input-group">
                        <input type="number" min="0" name="WorkoutExercises[rest_sec]" class="form-control" placeholder="np. 60" value="<?= Html::encode($model->rest_sec ?? 60) ?>">
                        <span class="input-group-text bg-light">sek</span>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-between align-items-center">
                <?= Html::a('<i class="bi bi-arrow-left me-1"></i>Anuluj', ['view', 'id' => $workout->id], ['class' => 'btn btn-outline-secondary btn-lg']) ?>
                <?= Html::submitButton('<i class="bi bi-check-lg me-1"></i>Zapisz', ['class' => 'btn btn-success btn-lg']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<style>
.workout-exercise-update .card {
    border-radius: 15px;
    border: none;
}
.workout-exercise-update .card-header {
    border-radius: 15px 15px 0 0 !important;
}
.workout-exercise-update .form-control-lg,
.workout-exercise-update .form-select-lg {
    border-radius: 10px;
}
.workout-exercise-update .input-group-text {
    border-radius: 10px;
}
.workout-exercise-update .btn {
    border-radius: 10px;
}
.workout-exercise-update .workout-form-muted {
    color: var(--text-muted, #a0a0cc);
}
</style>


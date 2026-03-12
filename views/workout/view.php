<?php

/**
 * Opis: Widok odpowiedzialny za renderowanie interfejsu uytkownika.
 */


use app\assets\WorkoutViewAsset;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Workouts $workout */
/** @var app\models\Exercises[] $exercises */

$this->title = $workout->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Plany treningowe'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
WorkoutViewAsset::register($this);

$exerciseTotal = count($workoutExercises);
$exerciseCompleted = 0;
foreach ($workoutExercises as $exerciseItem) {
    if ($exerciseItem->hasAttribute('is_completed') && (int) $exerciseItem->getAttribute('is_completed') === 1) {
        $exerciseCompleted++;
    }
}

$statusLabel = Yii::t('app', 'Nie rozpoczęty');
$statusClass = 'bg-secondary';
if ($exerciseTotal > 0 && $exerciseCompleted === $exerciseTotal) {
    $statusLabel = Yii::t('app', 'Ukończony');
    $statusClass = 'bg-success';
} elseif ($exerciseCompleted > 0 && $exerciseCompleted < $exerciseTotal) {
    $statusLabel = Yii::t('app', 'W trakcie');
    $statusClass = 'bg-warning text-dark';
}
?>

<div class="workout-view">
    <!-- Header Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="bi bi-clipboard-data text-primary me-2"></i>
                        <?= Html::encode($workout->name) ?>
                    </h1>
                    <div class="mb-2 d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge <?= $statusClass ?>">
                            <i class="bi bi-flag me-1"></i><?= Html::encode($statusLabel) ?>
                        </span>
                        <small class="workout-muted"><?= Yii::t('app', '{done}/{total} ukończonych ćwiczeń', ['done' => $exerciseCompleted, 'total' => $exerciseTotal]) ?></small>
                    </div>
                    <?php if ($workout->description): ?>
                        <p class="workout-muted mb-0"><?= Html::encode($workout->description) ?></p>
                    <?php endif; ?>
                </div>
                <div class="btn-group" role="group">
                    <?= Html::a('<i class="bi bi-pencil"></i> ' . Yii::t('app', 'Edytuj'), ['update', 'id' => $workout->id], ['class' => 'btn btn-outline-primary']) ?>
                    <?= Html::a('<i class="bi bi-trash"></i> ' . Yii::t('app', 'Usuń'), ['delete', 'id' => $workout->id], [
                        'class' => 'btn btn-outline-danger',
                        'data' => [
                            'confirm' => Yii::t('app', 'Czy na pewno chcesz usunąć ten trening?'),
                            'method' => 'post',
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Exercises Card -->
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h4 class="mb-0">
                <i class="bi bi-list-check text-success me-2"></i>
                <?= Yii::t('app', 'Ćwiczenia') ?>
            </h4>
            <?= Html::a('<i class="bi bi-plus-circle me-1"></i> ' . Yii::t('app', 'Dodaj ćwiczenie'), ['add-exercise', 'workout_id' => $workout->id], ['class' => 'btn btn-success']) ?>
        </div>
        <div class="card-body p-0">
            <?php if (empty($workoutExercises)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-emoji-frown display-1 workout-muted"></i>
                    <p class="workout-muted mt-3 mb-0"><?= Yii::t('app', 'Ten trening nie zawiera żadnych ćwiczeń.') ?></p>
                    <?= Html::a('<i class="bi bi-plus-lg me-1"></i>' . Yii::t('app', 'Dodaj pierwsze ćwiczenie'), ['add-exercise', 'workout_id' => $workout->id], ['class' => 'btn btn-outline-success mt-2']) ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 60px;">#</th>
                                <th>
                                    <i class="bi bi-activity me-1"></i><?= Yii::t('app', 'Ćwiczenie') ?>
                                </th>
                                <th class="text-center">
                                    <i class="bi bi-layers me-1"></i><?= Yii::t('app', 'Serie') ?>
                                </th>
                                <th class="text-center">
                                    <i class="bi bi-arrow-repeat me-1"></i><?= Yii::t('app', 'Powtórzenia') ?>
                                </th>
                                <th class="text-center">
                                    <i class="bi bi-clock me-1"></i><?= Yii::t('app', 'Czas') ?>
                                </th>
                                <th class="text-center">
                                    <i class="bi bi-hourglass-split me-1"></i><?= Yii::t('app', 'Odpoczynek') ?>
                                </th>
                                <th class="text-center" style="width: 140px;">
                                    <i class="bi bi-check2-square me-1"></i><?= Yii::t('app', 'Ukończone') ?>
                                </th>
                                <th class="text-center" style="width: 150px;">
                                    <i class="bi bi-gear me-1"></i><?= Yii::t('app', 'Akcje') ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($workoutExercises as $index => $we): ?>
                                <tr>
                                    <td class="text-center workout-muted"><?= $index + 1 ?></td>
                                    <td>
                                        <span class="badge bg-primary rounded-pill me-1">
                                            <i class="bi bi-dumbbell"></i>
                                        </span>
                                        <?= Html::encode($we->exercise->name) ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info rounded-pill"><?= $we->sets ?: '-' ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning text-dark rounded-pill"><?= $we->reps ?: '-' ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($we->duration_sec): ?>
                                            <?php
                                            $unit = method_exists($we, 'hasAttribute') && $we->hasAttribute('duration_unit')
                                                ? ($we->getAttribute('duration_unit') ?: 'sec')
                                                : 'sec';
                                            if ($unit === 'min') {
                                                $minutes = $we->duration_sec / 60;
                                                $display = ((int) $minutes == $minutes) ? (int) $minutes : rtrim(rtrim(number_format($minutes, 2, '.', ''), '0'), '.');
                                                echo $display . ' ' . Yii::t('app', 'min');
                                            } else {
                                                echo $we->duration_sec . ' ' . Yii::t('app', 'sek');
                                            }
                                            ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?= $we->rest_sec ? $we->rest_sec . Yii::t('app', 's') : '-' ?>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                            $checked = $we->hasAttribute('is_completed') && (int) $we->getAttribute('is_completed') === 1;
                                            echo Html::beginForm(['toggle-exercise-completion', 'id' => $we->id], 'post', ['class' => 'd-inline-block']);
                                            echo Html::hiddenInput('returnUrl', Yii::$app->request->url);
                                            echo Html::checkbox('completed', $checked, [
                                                'value' => 1,
                                                'label' => '',
                                                'class' => 'form-check-input workout-complete-checkbox',
                                                'onchange' => 'this.form.submit();',
                                            ]);
                                            echo Html::endForm();
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <?= Html::a('<i class="bi bi-pencil"></i>', ['update-exercise', 'id' => $we->id], [
                                                'class' => 'btn btn-outline-primary',
                                                'title' => Yii::t('app', 'Edytuj'),
                                            ]) ?>
                                            <?= Html::a('<i class="bi bi-trash"></i>', ['delete-exercise', 'id' => $we->id], [
                                                'class' => 'btn btn-outline-danger',
                                                'title' => Yii::t('app', 'Usuń'),
                                                'data' => [
                                                    'confirm' => Yii::t('app', 'Czy na pewno chcesz usunąć to ćwiczenie z treningu?'),
                                                    'method' => 'post',
                                                ],
                                            ]) ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Back Button -->
    <div class="mt-4">
        <?= Html::a('<i class="bi bi-arrow-left me-1"></i> ' . Yii::t('app', 'Wróć do listy'), ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>
</div>


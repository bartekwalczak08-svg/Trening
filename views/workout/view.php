<?php

/**
 * Opis: Widok odpowiedzialny za renderowanie interfejsu uytkownika.
 */


use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Workouts $workout */
/** @var app\models\Exercises[] $exercises */

$this->title = $workout->name;
$this->params['breadcrumbs'][] = ['label' => 'Plany treningowe', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$exerciseTotal = count($workoutExercises);
$exerciseCompleted = 0;
foreach ($workoutExercises as $exerciseItem) {
    if ($exerciseItem->hasAttribute('is_completed') && (int) $exerciseItem->getAttribute('is_completed') === 1) {
        $exerciseCompleted++;
    }
}

$statusLabel = 'Nie rozpoczęty';
$statusClass = 'bg-secondary';
if ($exerciseTotal > 0 && $exerciseCompleted === $exerciseTotal) {
    $statusLabel = 'Ukończony';
    $statusClass = 'bg-success';
} elseif ($exerciseCompleted > 0 && $exerciseCompleted < $exerciseTotal) {
    $statusLabel = 'W trakcie';
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
                        <small class="workout-muted"><?= $exerciseCompleted ?>/<?= $exerciseTotal ?> ukończonych ćwiczeń</small>
                    </div>
                    <?php if ($workout->description): ?>
                        <p class="workout-muted mb-0"><?= Html::encode($workout->description) ?></p>
                    <?php endif; ?>
                </div>
                <div class="btn-group" role="group">
                    <?= Html::a('<i class="bi bi-pencil"></i> Edytuj', ['update', 'id' => $workout->id], ['class' => 'btn btn-outline-primary']) ?>
                    <?= Html::a('<i class="bi bi-trash"></i> Usuń', ['delete', 'id' => $workout->id], [
                        'class' => 'btn btn-outline-danger',
                        'data' => [
                            'confirm' => 'Czy na pewno chcesz usun�
ć ten trening?',
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
                Ćwiczenia
            </h4>
            <?= Html::a('<i class="bi bi-plus-circle me-1"></i> Dodaj ćwiczenie', ['add-exercise', 'workout_id' => $workout->id], ['class' => 'btn btn-success']) ?>
        </div>
        <div class="card-body p-0">
            <?php if (empty($workoutExercises)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-emoji-frown display-1 workout-muted"></i>
                    <p class="workout-muted mt-3 mb-0">Ten trening nie zawiera żadnych ćwiczeń.</p>
                    <?= Html::a('<i class="bi bi-plus-lg me-1"></i>Dodaj pierwsze ćwiczenie', ['add-exercise', 'workout_id' => $workout->id], ['class' => 'btn btn-outline-success mt-2']) ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 60px;">#</th>
                                <th>
                                    <i class="bi bi-activity me-1"></i>Ćwiczenie
                                </th>
                                <th class="text-center">
                                    <i class="bi bi-layers me-1"></i>Serie
                                </th>
                                <th class="text-center">
                                    <i class="bi bi-arrow-repeat me-1"></i>Powtórzenia
                                </th>
                                <th class="text-center">
                                    <i class="bi bi-clock me-1"></i>Czas
                                </th>
                                <th class="text-center">
                                    <i class="bi bi-hourglass-split me-1"></i>Odpoczynek
                                </th>
                                <th class="text-center" style="width: 140px;">
                                    <i class="bi bi-check2-square me-1"></i>Ukończone
                                </th>
                                <th class="text-center" style="width: 150px;">
                                    <i class="bi bi-gear me-1"></i>Akcje
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
                                                echo $display . ' min';
                                            } else {
                                                echo $we->duration_sec . ' sek';
                                            }
                                            ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?= $we->rest_sec ? $we->rest_sec . 's' : '-' ?>
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
                                                'title' => 'Edytuj',
                                            ]) ?>
                                            <?= Html::a('<i class="bi bi-trash"></i>', ['delete-exercise', 'id' => $we->id], [
                                                'class' => 'btn btn-outline-danger',
                                                'title' => 'Usuń',
                                                'data' => [
                                                    'confirm' => 'Czy na pewno chcesz usun�
ć to ćwiczenie z treningu?',
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
        <?= Html::a('<i class="bi bi-arrow-left me-1"></i> Wróć do listy', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>
</div>

<style>
.workout-view .card {
    border-radius: 15px;
    border: none;
}

.workout-view .card,
.workout-view .card-body,
.workout-view .card-header {
    color: var(--text, #e8e8ff);
}

.workout-view .card-header.bg-white {
    background-color: rgba(255, 255, 255, 0.04) !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.12);
}

.workout-view .workout-muted {
    color: var(--text-muted, #a0a0cc) !important;
}

.workout-view .table {
    background-color: #ffffff;
}

.workout-view .table td {
    color: #2f3242;
}

.workout-view .table th {
    color: #5a5f73;
}

.workout-view .table-light th {
    background-color: #f1f3f8;
}

.workout-view .table > thead {
    border-bottom: 2px solid #dee2e6;
}
.workout-view .table th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}
.workout-view .table td {
    vertical-align: middle;
}
.workout-view .badge.rounded-pill {
    padding: 0.5em 0.8em;
}
.workout-complete-checkbox {
    width: 1.1rem;
    height: 1.1rem;
    cursor: pointer;
}
</style>


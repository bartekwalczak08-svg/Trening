<?php

/**
 * Opis: Widok odpowiedzialny za renderowanie interfejsu uytkownika.
 */


use app\assets\WorkoutIndexAsset;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Workouts[] $workouts */
/** @var array<string, app\models\Workouts[]> $groupedWorkouts */

$this->title = Yii::t('app', 'Plany treningowe');
$this->params['breadcrumbs'][] = $this->title;
WorkoutIndexAsset::register($this);
?>

<div class="workout-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><?= Html::encode($this->title) ?></h1>
            <p class="workout-subtitle mb-0"><?= Yii::t('app', 'Zarządzaj swoimi treningami') ?></p>
        </div>
        <?= Html::a('<i class="bi bi-plus-circle"></i> ' . Yii::t('app', 'Dodaj trening'), ['create'], ['class' => 'btn btn-success btn-lg workout-btn-main']) ?>
    </div>

    <?php if (empty($workouts)): ?>
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-calendar-x display-1 workout-empty-icon"></i>
                <h3 class="mt-3"><?= Yii::t('app', 'Brak treningów') ?></h3>
                <p class="workout-subtitle"><?= Yii::t('app', 'Jeszcze nie dodałeś żadnego treningu.') ?></p>
                <?= Html::a('<i class="bi bi-plus-circle"></i> ' . Yii::t('app', 'Dodaj pierwszy trening'), ['create'], ['class' => 'btn btn-primary mt-2']) ?>
            </div>
        </div>
    <?php else: ?>
        <?php foreach (\app\models\Workouts::weekdayOrder() as $weekday): ?>
            <?php $dayWorkouts = $groupedWorkouts[$weekday] ?? []; ?>
            <section class="mb-5">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h2 class="h4 mb-0"><?= Html::encode(\app\models\Workouts::weekdayOptions()[$weekday]) ?></h2>
                    <span class="badge workout-day-count"><?= Yii::t('app', '{count} treningów', ['count' => count($dayWorkouts)]) ?></span>
                </div>

                <?php if (empty($dayWorkouts)): ?>
                    <div class="card shadow-sm border-0">
                        <div class="card-body py-3">
                            <span class="workout-subtitle"><?= Yii::t('app', 'Brak treningów na ten dzień.') ?></span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($dayWorkouts as $workout): ?>
                            <?php
                                $exerciseItems = $workout->workoutExercises;
                                $exerciseTotal = is_array($exerciseItems) ? count($exerciseItems) : 0;
                                $exerciseCompleted = 0;
                                if ($exerciseTotal > 0) {
                                    foreach ($exerciseItems as $item) {
                                        if ($item->hasAttribute('is_completed') && (int) $item->getAttribute('is_completed') === 1) {
                                            $exerciseCompleted++;
                                        }
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
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100 shadow-sm workout-card">
                                    <div class="card-header bg-transparent border-0 pt-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="card-title mb-0 text-truncate"><?= Html::encode($workout->name) ?></h5>
                                            <span class="badge workout-date-badge"><?= date('d.m.Y', $workout->created_at) ?></span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text workout-description">
                                            <?php if ($workout->description): ?>
                                                <?= Html::encode($workout->description) ?>
                                            <?php else: ?>
                                                <em class="workout-subtitle"><?= Yii::t('app', 'Brak opisu') ?></em>
                                            <?php endif; ?>
                                        </p>
                                        <div class="mt-3">
                                            <small class="workout-subtitle">
                                                <i class="bi bi-list-task"></i>
                                                <?= Yii::t('app', '{count} ćwiczeń', ['count' => $exerciseTotal]) ?>
                                            </small>
                                        </div>
                                        <div class="mt-2 d-flex align-items-center justify-content-between">
                                            <span class="badge <?= $statusClass ?>"><?= Html::encode($statusLabel) ?></span>
                                            <small class="workout-subtitle"><?= Yii::t('app', '{done}/{total} ukończonych', ['done' => $exerciseCompleted, 'total' => $exerciseTotal]) ?></small>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-transparent border-0 pb-3">
                                        <div class="btn-group btn-group-sm w-100">
                                            <?= Html::a('<i class="bi bi-eye"></i> ' . Yii::t('app', 'Podgląd'), ['view', 'id' => $workout->id], ['class' => 'btn btn-outline-primary']) ?>
                                            <?= Html::a('<i class="bi bi-pencil"></i> ' . Yii::t('app', 'Edytuj'), ['update', 'id' => $workout->id], ['class' => 'btn btn-outline-secondary']) ?>
                                            <?= Html::a('<i class="bi bi-trash"></i>', ['delete', 'id' => $workout->id], [
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
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>
    <?php endif; ?>
</div>


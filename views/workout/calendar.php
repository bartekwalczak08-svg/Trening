<?php

/**
 * Opis: Widok odpowiedzialny za renderowanie interfejsu uytkownika.
 */


use app\assets\WorkoutCalendarAsset;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array<string, app\models\Workouts[]> $groupedWorkouts */

$this->title = Yii::t('app', 'Kalendarz treningów');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Plany treningowe'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
WorkoutCalendarAsset::register($this);
?>

<div class="workout-calendar">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><?= Html::encode($this->title) ?></h1>
            <p class="calendar-subtitle mb-0"><?= Yii::t('app', 'Tygodniowy rozkład treningów') ?></p>
        </div>
        <?= Html::a('<i class="bi bi-plus-circle"></i> ' . Yii::t('app', 'Dodaj trening'), ['create'], ['class' => 'btn btn-success']) ?>
    </div>

    <div class="calendar-grid">
        <?php foreach (\app\models\Workouts::weekdayOrder() as $weekday): ?>
            <?php $dayWorkouts = $groupedWorkouts[$weekday] ?? []; ?>
            <section class="calendar-day card shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="h6 mb-0"><?= Html::encode(\app\models\Workouts::weekdayOptions()[$weekday]) ?></h2>
                        <span class="badge calendar-count"><?= count($dayWorkouts) ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($dayWorkouts)): ?>
                        <p class="calendar-empty mb-0"><?= Yii::t('app', 'Brak treningów.') ?></p>
                    <?php else: ?>
                        <div class="d-grid gap-2">
                            <?php foreach ($dayWorkouts as $workout): ?>
                                <article class="calendar-item p-2 rounded">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <strong class="calendar-item-title"><?= Html::encode($workout->name) ?></strong>
                                        <small class="calendar-date"><?= date('d.m', $workout->created_at) ?></small>
                                    </div>
                                    <div class="calendar-meta small mt-1">
                                        <?= Yii::t('app', '{count} ćwiczeń', ['count' => (int) $workout->getWorkoutExercises()->count()]) ?>
                                    </div>
                                    <div class="mt-2">
                                        <?= Html::a(Yii::t('app', 'Otwórz'), ['view', 'id' => $workout->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </div>
</div>


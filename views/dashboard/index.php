<?php

/**
 * Opis: Widok odpowiedzialny za renderowanie interfejsu uytkownika.
 */


use app\assets\DashboardAsset;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Workouts|null $todayWorkout */
/** @var app\models\Workouts[] $recentWorkouts */
/** @var array $kpi */

$this->title = Yii::t('app', 'Panel');
$this->params['breadcrumbs'][] = $this->title;
DashboardAsset::register($this);

$todayExerciseCount = $todayWorkout ? $todayWorkout->getWorkoutExercises()->count() : 0;
?>

<div class="dashboard-panel">
    <section class="hero mb-4">
        <div class="hero-content">
            <div>
                <p class="eyebrow mb-1"><?= Yii::t('app', 'Dzisiaj') ?></p>
                <?php if ($todayWorkout): ?>
                    <h1 class="hero-title mb-1"><?= Html::encode($todayWorkout->name) ?></h1>
                    <p class="hero-subtitle mb-0">
                        <?= Yii::t('app', '{count} ćwiczeń w planie', ['count' => $todayExerciseCount]) ?>
                        <?php if ($todayWorkout->description): ?>
                            | <?= Html::encode($todayWorkout->description) ?>
                        <?php endif; ?>
                    </p>
                <?php else: ?>
                    <h1 class="hero-title mb-1"><?= Yii::t('app', 'Brak planu na dziś') ?></h1>
                    <p class="hero-subtitle mb-0"><?= Yii::t('app', 'Dodaj pierwszy trening i zacznij budować progres.') ?></p>
                <?php endif; ?>
            </div>
            <div class="hero-actions">
                <?php if ($todayWorkout): ?>
                    <?= Html::a('<i class="bi bi-play-circle me-1"></i> ' . Yii::t('app', 'Rozpocznij trening'), ['/workout/view', 'id' => $todayWorkout->id], ['class' => 'btn btn-success btn-lg']) ?>
                <?php else: ?>
                    <?= Html::a('<i class="bi bi-plus-circle me-1"></i> ' . Yii::t('app', 'Dodaj trening'), ['/workout/create'], ['class' => 'btn btn-primary btn-lg']) ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="kpi-label"><?= Yii::t('app', 'Treningi tydzień') ?></div>
                <div class="kpi-value"><?= $kpi['weekWorkouts'] ?></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="kpi-label"><?= Yii::t('app', 'Treningi miesiąc') ?></div>
                <div class="kpi-value"><?= $kpi['monthWorkouts'] ?></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="kpi-label"><?= Yii::t('app', 'Łączny czas planów') ?></div>
                <div class="kpi-value"><?= Html::encode($kpi['plannedDuration']) ?></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="kpi-label"><?= Yii::t('app', 'Średni odpoczynek') ?></div>
                <div class="kpi-value"><?= Yii::t('app', '{count} sek', ['count' => $kpi['avgRestSec']]) ?></div>
            </div>
        </div>
    </section>

    <section class="row g-3">
        <div class="col-lg-8">
            <div class="card panel-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="h5 mb-0"><?= Yii::t('app', 'Ostatnie aktywności') ?></h3>
                    <?= Html::a(Yii::t('app', 'Zobacz wszystkie'), ['/workout/index'], ['class' => 'btn btn-sm btn-outline-info']) ?>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recentWorkouts)): ?>
                        <div class="p-4 panel-muted"><?= Yii::t('app', 'Brak aktywności. Dodaj pierwszy trening.') ?></div>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($recentWorkouts as $workout): ?>
                                <li class="list-group-item activity-item">
                                    <div>
                                        <div class="activity-title"><?= Html::encode($workout->name) ?></div>
                                        <div class="panel-muted small">
                                            <?= date('d.m.Y H:i', $workout->created_at) ?>
                                            | <?= Yii::t('app', '{count} ćwiczeń', ['count' => $workout->getWorkoutExercises()->count()]) ?>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <?= Html::a(Yii::t('app', 'Podgląd'), ['/workout/view', 'id' => $workout->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                                        <?= Html::a(Yii::t('app', 'Edytuj'), ['/workout/update', 'id' => $workout->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card panel-card h-100">
                <div class="card-header">
                    <h3 class="h5 mb-0"><?= Yii::t('app', 'Szybkie akcje') ?></h3>
                </div>
                <div class="card-body d-grid gap-2">
                    <?= Html::a('<i class="bi bi-plus-circle me-1"></i> ' . Yii::t('app', 'Dodaj trening'), ['/workout/create'], ['class' => 'btn btn-outline-success']) ?>
                    <?= Html::a('<i class="bi bi-activity me-1"></i> ' . Yii::t('app', 'Plany treningowe'), ['/workout/index'], ['class' => 'btn btn-outline-info']) ?>
                    <?php if ($todayWorkout): ?>
                        <?= Html::a('<i class="bi bi-play-fill me-1"></i> ' . Yii::t('app', 'Trenuj teraz'), ['/workout/view', 'id' => $todayWorkout->id], ['class' => 'btn btn-outline-primary']) ?>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <span class="badge text-bg-dark"><?= Yii::t('app', 'Seria: {count} dni', ['count' => $kpi['streakDays']]) ?></span>
                </div>
            </div>
        </div>
    </section>
</div>



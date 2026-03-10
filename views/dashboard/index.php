<?php

/**
 * Opis: Widok odpowiedzialny za renderowanie interfejsu uytkownika.
 */


use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Workouts|null $todayWorkout */
/** @var app\models\Workouts[] $recentWorkouts */
/** @var array $kpi */

$this->title = 'Panel';
$this->params['breadcrumbs'][] = $this->title;

$todayExerciseCount = $todayWorkout ? $todayWorkout->getWorkoutExercises()->count() : 0;
?>

<div class="dashboard-panel">
    <section class="hero mb-4">
        <div class="hero-content">
            <div>
                <p class="eyebrow mb-1">Dzisiaj</p>
                <?php if ($todayWorkout): ?>
                    <h1 class="hero-title mb-1"><?= Html::encode($todayWorkout->name) ?></h1>
                    <p class="hero-subtitle mb-0">
                        <?= $todayExerciseCount ?> ćwiczeń w planie
                        <?php if ($todayWorkout->description): ?>
                            | <?= Html::encode($todayWorkout->description) ?>
                        <?php endif; ?>
                    </p>
                <?php else: ?>
                    <h1 class="hero-title mb-1">Brak planu na dziś</h1>
                    <p class="hero-subtitle mb-0">Dodaj pierwszy trening i zacznij budować progres.</p>
                <?php endif; ?>
            </div>
            <div class="hero-actions">
                <?php if ($todayWorkout): ?>
                    <?= Html::a('<i class="bi bi-play-circle me-1"></i> Rozpocznij trening', ['/workout/view', 'id' => $todayWorkout->id], ['class' => 'btn btn-success btn-lg']) ?>
                <?php else: ?>
                    <?= Html::a('<i class="bi bi-plus-circle me-1"></i> Dodaj trening', ['/workout/create'], ['class' => 'btn btn-primary btn-lg']) ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="kpi-label">Treningi tydzień</div>
                <div class="kpi-value"><?= $kpi['weekWorkouts'] ?></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="kpi-label">Treningi miesiąc</div>
                <div class="kpi-value"><?= $kpi['monthWorkouts'] ?></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="kpi-label">Łączny czas planów</div>
                <div class="kpi-value"><?= Html::encode($kpi['plannedDuration']) ?></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="kpi-label">Średni odpoczynek</div>
                <div class="kpi-value"><?= $kpi['avgRestSec'] ?> sek</div>
            </div>
        </div>
    </section>

    <section class="row g-3">
        <div class="col-lg-8">
            <div class="card panel-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="h5 mb-0">Ostatnie aktywności</h3>
                    <?= Html::a('Zobacz wszystkie', ['/workout/index'], ['class' => 'btn btn-sm btn-outline-info']) ?>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recentWorkouts)): ?>
                        <div class="p-4 panel-muted">Brak aktywności. Dodaj pierwszy trening.</div>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($recentWorkouts as $workout): ?>
                                <li class="list-group-item activity-item">
                                    <div>
                                        <div class="activity-title"><?= Html::encode($workout->name) ?></div>
                                        <div class="panel-muted small">
                                            <?= date('d.m.Y H:i', $workout->created_at) ?>
                                            | <?= $workout->getWorkoutExercises()->count() ?> ćwiczeń
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <?= Html::a('Podgląd', ['/workout/view', 'id' => $workout->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                                        <?= Html::a('Edytuj', ['/workout/update', 'id' => $workout->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
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
                    <h3 class="h5 mb-0">Szybkie akcje</h3>
                </div>
                <div class="card-body d-grid gap-2">
                    <?= Html::a('<i class="bi bi-plus-circle me-1"></i> Dodaj trening', ['/workout/create'], ['class' => 'btn btn-outline-success']) ?>
                    <?= Html::a('<i class="bi bi-activity me-1"></i> Plany treningowe', ['/workout/index'], ['class' => 'btn btn-outline-info']) ?>
                    <?php if ($todayWorkout): ?>
                        <?= Html::a('<i class="bi bi-play-fill me-1"></i> Trenuj teraz', ['/workout/view', 'id' => $todayWorkout->id], ['class' => 'btn btn-outline-primary']) ?>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <span class="badge text-bg-dark">Streak: <?= $kpi['streakDays'] ?> dni</span>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
.dashboard-panel .hero {
    background: linear-gradient(120deg, rgba(0, 212, 255, 0.12), rgba(57, 255, 170, 0.08));
    border: 1px solid rgba(0, 212, 255, 0.25);
    border-radius: 16px;
    padding: 1.25rem;
}

.dashboard-panel .hero-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.dashboard-panel .eyebrow {
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--text-muted, #a0a0cc);
    font-size: 0.75rem;
}

.dashboard-panel .hero-title {
    color: var(--text, #e8e8ff);
    font-weight: 700;
}

.dashboard-panel .hero-subtitle,
.dashboard-panel .panel-muted {
    color: var(--text-muted, #a0a0cc);
}

.dashboard-panel .kpi-card {
    background: var(--surface-soft, #f8fafc);
    border: 1px solid var(--border, #dbe4ef);
    border-radius: 14px;
    padding: 1rem;
}

.dashboard-panel .kpi-label {
    color: var(--text-muted, #a0a0cc);
    font-size: 0.85rem;
}

.dashboard-panel .kpi-value {
    color: var(--text, #e8e8ff);
    font-size: 1.6rem;
    font-weight: 700;
}

.dashboard-panel .panel-card {
    border-radius: 14px;
    border: 1px solid var(--border, #dbe4ef);
    background: var(--card-bg, #ffffff);
}

.dashboard-panel .panel-card .card-header,
.dashboard-panel .panel-card .card-footer,
.dashboard-panel .activity-item {
    background: transparent;
    border-color: var(--border, #dbe4ef);
}

html[data-theme='dark'] .dashboard-panel .kpi-card {
    background: rgba(15, 23, 42, 0.75);
    border-color: #334155;
}

html[data-theme='dark'] .dashboard-panel .panel-card {
    background: rgba(17, 24, 39, 0.92);
    border-color: #334155;
}

html[data-theme='dark'] .dashboard-panel .panel-card .card-header,
html[data-theme='dark'] .dashboard-panel .panel-card .card-footer,
html[data-theme='dark'] .dashboard-panel .activity-item {
    border-color: #334155;
}

.dashboard-panel .activity-title {
    color: var(--text, #e8e8ff);
    font-weight: 600;
}

@media (max-width: 768px) {
    .dashboard-panel .hero {
        padding: 1rem;
    }

    .dashboard-panel .kpi-value {
        font-size: 1.35rem;
    }
}
</style>


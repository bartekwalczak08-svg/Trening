<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\generated\Workouts[] $workouts */

$this->title = 'Plany treningowe';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="workout-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><?= Html::encode($this->title) ?></h1>
            <p class="workout-subtitle mb-0">Zarzadzaj swoimi treningami</p>
        </div>
        <?= Html::a('<i class="bi bi-plus-circle"></i> Dodaj trening', ['create'], ['class' => 'btn btn-success btn-lg workout-btn-main']) ?>
    </div>

    <?php if (empty($workouts)): ?>
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-calendar-x display-1 workout-empty-icon"></i>
                <h3 class="mt-3">Brak treningow</h3>
                <p class="workout-subtitle">Jeszcze nie dodales zadnego treningu.</p>
                <?= Html::a('<i class="bi bi-plus-circle"></i> Dodaj pierwszy trening', ['create'], ['class' => 'btn btn-primary mt-2']) ?>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($workouts as $workout): ?>
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
                                    <em class="workout-subtitle">Brak opisu</em>
                                <?php endif; ?>
                            </p>
                            <div class="mt-3">
                                <small class="workout-subtitle">
                                    <i class="bi bi-list-task"></i> 
                                    <?= $workout->getWorkoutExercises()->count() ?> cwiczen
                                </small>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 pb-3">
                            <div class="btn-group btn-group-sm w-100">
                                <?= Html::a('<i class="bi bi-eye"></i> Podglad', ['view', 'id' => $workout->id], ['class' => 'btn btn-outline-primary']) ?>
                                <?= Html::a('<i class="bi bi-pencil"></i> Edytuj', ['update', 'id' => $workout->id], ['class' => 'btn btn-outline-secondary']) ?>
                                <?= Html::a('<i class="bi bi-trash"></i>', ['delete', 'id' => $workout->id], [
                                    'class' => 'btn btn-outline-danger',
                                    'data' => [
                                        'confirm' => 'Czy na pewno chcesz usunac ten trening?',
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
</div>

<style>
.workout-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border-radius: 12px;
    border: 1px solid rgba(0,0,0,0.08);
}
.workout-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.12) !important;
}
.workout-card .card-title {
    font-weight: 600;
    color: var(--text, #e8e8ff);
}
.workout-subtitle {
    color: var(--text-muted, #a0a0cc);
}
.workout-empty-icon {
    color: var(--text-muted, #a0a0cc);
    opacity: 0.8;
}
.workout-description {
    color: var(--text, #e8e8ff);
    min-height: 48px;
}
.workout-date-badge {
    background: rgba(255, 255, 255, 0.08);
    color: var(--text, #e8e8ff);
    border: 1px solid rgba(255, 255, 255, 0.15);
}
.workout-index .badge {
    font-weight: 500;
    font-size: 0.75rem;
}
.workout-index .btn {
    border-radius: 8px;
}
.workout-index .btn-lg {
    border-radius: 10px;
}
.workout-btn-main {
    font-weight: 600;
}
</style>
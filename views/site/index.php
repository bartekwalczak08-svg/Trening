<?php

/**
 * Opis: Widok odpowiedzialny za renderowanie interfejsu uytkownika.
 */


use app\assets\SiteIndexAsset;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array<string, app\models\Workouts[]> $groupedWorkouts */
/** @var int $monthlyWorkouts */
/** @var app\models\Workouts[] $todayWorkouts */
/** @var string|null $nextWorkoutDayLabel */
/** @var int $activeDaysCount */
/** @var int $totalExercisesPlanned */
/** @var app\models\Workouts[] $recentlyUpdatedWorkouts */
/** @var string $calendarFilter */
/** @var string[] $visibleWeekdays */
/** @var int $weeklyCompletedWorkouts */
/** @var int $weeklySkippedWorkouts */
/** @var int $weeklyCompletionPercent */

$this->title = 'Plan Treningowy';
SiteIndexAsset::register($this);

$totalWorkouts = 0;
foreach ($groupedWorkouts as $items) {
    $totalWorkouts += count($items);
}
?>

<div class="flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0 neon-heading">Trenuj jak Olimpijczyk</h1>
        <?php if (!Yii::$app->user->isGuest): ?>
            <?= Html::a('+ Dodaj trening', ['/workout/create'], ['class' => 'btn btn-neon px-4']) ?>
        <?php else: ?>
            <?= Html::a('Zaloguj się', ['/site/login'], ['class' => 'btn btn-neon px-4']) ?>
        <?php endif; ?>
    </div>

    <?php if (Yii::$app->user->isGuest): ?>
        <div class="card p-4 mb-4">
            <h3 class="mb-2">Witaj w planie treningowym</h3>
            <p class="text-muted mb-3">Zaloguj się, aby dodawać i planować treningi w kalendarzu tygodnia.</p>
            <?= Html::a('Przejdź do logowania', ['/site/login'], ['class' => 'btn btn-primary']) ?>
        </div>
    <?php else: ?>
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card p-4 text-center h-100">
                    <div class="text-muted small">Treningi łącznie</div>
                    <div class="card-value"><?= $totalWorkouts ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-4 text-center h-100">
                    <div class="text-muted small">Treningi w tym miesiącu</div>
                    <div class="card-value"><?= $monthlyWorkouts ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-4 text-center h-100 d-flex justify-content-center">
                    <?= Html::a('Pełny kalendarz', ['/workout/calendar'], ['class' => 'btn btn-outline-info']) ?>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card p-4 text-center h-100">
                    <div class="text-muted small">Treningi wykonane (tydzień)</div>
                    <div class="card-value text-success"><?= $weeklyCompletedWorkouts ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-4 text-center h-100">
                    <div class="text-muted small">Treningi opuszczone (tydzień)</div>
                    <div class="card-value text-danger"><?= $weeklySkippedWorkouts ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-4 text-center h-100">
                    <div class="text-muted small">Procent wykonania (tydzień)</div>
                    <div class="card-value text-info"><?= $weeklyCompletionPercent ?>%</div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card p-4 h-100 today-card">
                    <div class="d-flex justify-content-between align-items-center mb-3 today-header">
                        <h3 class="h5 mb-0"><i class="bi bi-calendar-check me-2"></i>Dzisiaj</h3>
                        <span class="badge today-count-badge"><?= count($todayWorkouts) ?> treningów</span>
                    </div>
                    <?php if (empty($todayWorkouts)): ?>
                        <p class="text-muted mb-2 today-empty">Brak treningu przypisanego na dzisiaj.</p>
                        <?php if ($nextWorkoutDayLabel !== null): ?>
                            <p class="small text-muted mb-0">Najbliższy zaplanowany dzień: <span class="today-next-day"><?= Html::encode($nextWorkoutDayLabel) ?></span></p>
                        <?php endif; ?>
                    <?php else: ?>
                        <ul class="list-group list-group-flush today-list">
                            <?php foreach (array_slice($todayWorkouts, 0, 4) as $workout): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 today-item">
                                    <span class="today-item-name"><?= Html::encode($workout->name) ?></span>
                                    <?= Html::a('Otwórz', ['/workout/view', 'id' => $workout->id], ['class' => 'btn btn-sm today-open-btn']) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card p-4 h-100">
                    <h3 class="h5 mb-3">Szybkie akcje</h3>
                    <div class="d-grid gap-2">
                        <?= Html::a('<i class="bi bi-plus-circle me-1"></i> Nowy trening', ['/workout/create'], ['class' => 'btn btn-success']) ?>
                        <?= Html::a('<i class="bi bi-calendar-event me-1"></i> Otwórz kalendarz', ['/workout/calendar'], ['class' => 'btn btn-outline-info']) ?>
                        <?= Html::a('<i class="bi bi-activity me-1"></i> Wszystkie plany', ['/workout/index'], ['class' => 'btn btn-outline-secondary']) ?>
                    </div>
                    <hr>
                    <div class="small text-muted">
                        Aktywne dni tygodnia: <strong><?= $activeDaysCount ?>/7</strong><br>
                        Zaplanowane ćwiczenia: <strong><?= $totalExercisesPlanned ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="h5 mb-0">Ostatnio edytowane treningi</h3>
                <?= Html::a('Zarządzaj wszystkimi', ['/workout/index'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
            </div>
            <?php if (empty($recentlyUpdatedWorkouts)): ?>
                <p class="text-muted mb-0">Nie masz jeszcze edytowanych treningów.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                        <tr>
                            <th>Nazwa</th>
                            <th>Dzień</th>
                            <th>Aktualizacja</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($recentlyUpdatedWorkouts as $workout): ?>
                            <tr>
                                <td><?= Html::encode($workout->name) ?></td>
                                <td><?= Html::encode($workout->getWeekdayLabel()) ?></td>
                                <td><?= date('d.m.Y H:i', (int) $workout->updated_at) ?></td>
                                <td class="text-end"><?= Html::a('Otwórz', ['/workout/view', 'id' => $workout->id], ['class' => 'btn btn-sm btn-outline-primary']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0">Kalendarz tygodnia</h3>
                <div class="btn-group btn-group-sm" role="group" aria-label="Filtr kalendarza">
                    <?= Html::a('Wszystko', ['/site/index', 'calendarFilter' => 'all'], ['class' => 'btn ' . ($calendarFilter === 'all' ? 'btn-primary' : 'btn-outline-primary')]) ?>
                    <?= Html::a('Dzisiaj', ['/site/index', 'calendarFilter' => 'today'], ['class' => 'btn ' . ($calendarFilter === 'today' ? 'btn-primary' : 'btn-outline-primary')]) ?>
                    <?= Html::a('Weekend', ['/site/index', 'calendarFilter' => 'weekend'], ['class' => 'btn ' . ($calendarFilter === 'weekend' ? 'btn-primary' : 'btn-outline-primary')]) ?>
                </div>
            </div>

            <div class="home-calendar-grid">
                <?php foreach ($visibleWeekdays as $weekday): ?>
                    <?php $dayWorkouts = $groupedWorkouts[$weekday] ?? []; ?>
                    <section class="home-day card shadow-sm">
                        <div class="card-header bg-transparent border-0 py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong><?= Html::encode(\app\models\Workouts::weekdayOptions()[$weekday]) ?></strong>
                                <span class="badge home-count"><?= count($dayWorkouts) ?></span>
                            </div>
                        </div>
                        <div class="card-body pt-1">
                            <?php if (empty($dayWorkouts)): ?>
                                <p class="text-muted small mb-0">Brak treningów</p>
                            <?php else: ?>
                                <ul class="list-unstyled mb-0 small">
                                    <?php foreach (array_slice($dayWorkouts, 0, 3) as $workout): ?>
                                        <li class="mb-1">
                                            <?= Html::a(Html::encode($workout->name), ['/workout/view', 'id' => $workout->id], ['class' => 'link-light text-decoration-none']) ?>
                                        </li>
                                    <?php endforeach; ?>
                                    <?php if (count($dayWorkouts) > 3): ?>
                                        <li class="text-muted">+ <?= count($dayWorkouts) - 3 ?> więcej</li>
                                    <?php endif; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>


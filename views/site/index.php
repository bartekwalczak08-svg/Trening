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

$this->title = Yii::t('app', 'Plan Treningowy');
SiteIndexAsset::register($this);

$totalWorkouts = 0;
foreach ($groupedWorkouts as $items) {
    $totalWorkouts += count($items);
}
?>

<div class="flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0 neon-heading"><?= Yii::t('app', 'Trenuj jak Olimpijczyk') ?></h1>
        <?php if (!Yii::$app->user->isGuest): ?>
            <?= Html::a(Yii::t('app', '+ Dodaj trening'), ['/workout/create'], ['class' => 'btn btn-neon px-4']) ?>
        <?php else: ?>
            <?= Html::a(Yii::t('app', 'Zaloguj się'), ['/site/login'], ['class' => 'btn btn-neon px-4']) ?>
        <?php endif; ?>
    </div>

    <?php if (Yii::$app->user->isGuest): ?>
        <div class="card p-4 mb-4">
            <h3 class="mb-2"><?= Yii::t('app', 'Witaj w planie treningowym') ?></h3>
            <p class="text-muted mb-3"><?= Yii::t('app', 'Zaloguj się, aby dodawać i planować treningi w kalendarzu tygodnia.') ?></p>
            <?= Html::a(Yii::t('app', 'Przejdź do logowania'), ['/site/login'], ['class' => 'btn btn-primary']) ?>
        </div>
    <?php else: ?>
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card p-4 text-center h-100">
                    <div class="text-muted small"><?= Yii::t('app', 'Treningi łącznie') ?></div>
                    <div class="card-value"><?= $totalWorkouts ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-4 text-center h-100">
                    <div class="text-muted small"><?= Yii::t('app', 'Treningi w tym miesiącu') ?></div>
                    <div class="card-value"><?= $monthlyWorkouts ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-4 text-center h-100 d-flex justify-content-center">
                    <?= Html::a(Yii::t('app', 'Pełny kalendarz'), ['/workout/calendar'], ['class' => 'btn btn-outline-info']) ?>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card p-4 text-center h-100">
                    <div class="text-muted small"><?= Yii::t('app', 'Treningi wykonane (tydzień)') ?></div>
                    <div class="card-value text-success"><?= $weeklyCompletedWorkouts ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-4 text-center h-100">
                    <div class="text-muted small"><?= Yii::t('app', 'Treningi opuszczone (tydzień)') ?></div>
                    <div class="card-value text-danger"><?= $weeklySkippedWorkouts ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-4 text-center h-100">
                    <div class="text-muted small"><?= Yii::t('app', 'Procent wykonania (tydzień)') ?></div>
                    <div class="card-value text-info"><?= $weeklyCompletionPercent ?>%</div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card p-4 h-100 today-card">
                    <div class="d-flex justify-content-between align-items-center mb-3 today-header">
                        <h3 class="h5 mb-0"><i class="bi bi-calendar-check me-2"></i><?= Yii::t('app', 'Dzisiaj') ?></h3>
                        <span class="badge today-count-badge"><?= Yii::t('app', '{count} treningów', ['count' => count($todayWorkouts)]) ?></span>
                    </div>
                    <?php if (empty($todayWorkouts)): ?>
                        <p class="text-muted mb-2 today-empty"><?= Yii::t('app', 'Brak treningu przypisanego na dzisiaj.') ?></p>
                        <?php if ($nextWorkoutDayLabel !== null): ?>
                            <p class="small text-muted mb-0"><?= Yii::t('app', 'Najbliższy zaplanowany dzień: {day}', ['day' => '<span class="today-next-day">' . Html::encode($nextWorkoutDayLabel) . '</span>']) ?></p>
                        <?php endif; ?>
                    <?php else: ?>
                        <ul class="list-group list-group-flush today-list">
                            <?php foreach (array_slice($todayWorkouts, 0, 4) as $workout): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 today-item">
                                    <span class="today-item-name"><?= Html::encode($workout->name) ?></span>
                                    <?= Html::a(Yii::t('app', 'Otwórz'), ['/workout/view', 'id' => $workout->id], ['class' => 'btn btn-sm today-open-btn']) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card p-4 h-100">
                    <h3 class="h5 mb-3"><?= Yii::t('app', 'Szybkie akcje') ?></h3>
                    <div class="d-grid gap-2">
                        <?= Html::a('<i class="bi bi-plus-circle me-1"></i> ' . Yii::t('app', 'Nowy trening'), ['/workout/create'], ['class' => 'btn btn-success']) ?>
                        <?= Html::a('<i class="bi bi-calendar-event me-1"></i> ' . Yii::t('app', 'Otwórz kalendarz'), ['/workout/calendar'], ['class' => 'btn btn-outline-info']) ?>
                        <?= Html::a('<i class="bi bi-activity me-1"></i> ' . Yii::t('app', 'Wszystkie plany'), ['/workout/index'], ['class' => 'btn btn-outline-secondary']) ?>
                    </div>
                    <hr>
                    <div class="small text-muted">
                        <?= Yii::t('app', 'Aktywne dni tygodnia: {count}/7', ['count' => '<strong>' . $activeDaysCount . '</strong>']) ?><br>
                        <?= Yii::t('app', 'Zaplanowane ćwiczenia: {count}', ['count' => '<strong>' . $totalExercisesPlanned . '</strong>']) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="h5 mb-0"><?= Yii::t('app', 'Ostatnio edytowane treningi') ?></h3>
                <?= Html::a(Yii::t('app', 'Zarządzaj wszystkimi'), ['/workout/index'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
            </div>
            <?php if (empty($recentlyUpdatedWorkouts)): ?>
                <p class="text-muted mb-0"><?= Yii::t('app', 'Nie masz jeszcze edytowanych treningów.') ?></p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                        <tr>
                            <th><?= Yii::t('app', 'Nazwa') ?></th>
                            <th><?= Yii::t('app', 'Dzień') ?></th>
                            <th><?= Yii::t('app', 'Aktualizacja') ?></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($recentlyUpdatedWorkouts as $workout): ?>
                            <tr>
                                <td><?= Html::encode($workout->name) ?></td>
                                <td><?= Html::encode($workout->getWeekdayLabel()) ?></td>
                                <td><?= date('d.m.Y H:i', (int) $workout->updated_at) ?></td>
                                <td class="text-end"><?= Html::a(Yii::t('app', 'Otwórz'), ['/workout/view', 'id' => $workout->id], ['class' => 'btn btn-sm btn-outline-primary']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0"><?= Yii::t('app', 'Kalendarz tygodnia') ?></h3>
                <div class="btn-group btn-group-sm" role="group" aria-label="Filtr kalendarza">
                    <?= Html::a(Yii::t('app', 'Wszystko'), ['/site/index', 'calendarFilter' => 'all'], ['class' => 'btn ' . ($calendarFilter === 'all' ? 'btn-primary' : 'btn-outline-primary')]) ?>
                    <?= Html::a(Yii::t('app', 'Dzisiaj'), ['/site/index', 'calendarFilter' => 'today'], ['class' => 'btn ' . ($calendarFilter === 'today' ? 'btn-primary' : 'btn-outline-primary')]) ?>
                    <?= Html::a(Yii::t('app', 'Weekend'), ['/site/index', 'calendarFilter' => 'weekend'], ['class' => 'btn ' . ($calendarFilter === 'weekend' ? 'btn-primary' : 'btn-outline-primary')]) ?>
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
                                <p class="text-muted small mb-0"><?= Yii::t('app', 'Brak treningów') ?></p>
                            <?php else: ?>
                                <ul class="list-unstyled mb-0 small">
                                    <?php foreach (array_slice($dayWorkouts, 0, 3) as $workout): ?>
                                        <li class="mb-1">
                                            <?= Html::a(Html::encode($workout->name), ['/workout/view', 'id' => $workout->id], ['class' => 'link-light text-decoration-none']) ?>
                                        </li>
                                    <?php endforeach; ?>
                                    <?php if (count($dayWorkouts) > 3): ?>
                                        <li class="text-muted"><?= Yii::t('app', '+ {count} więcej', ['count' => count($dayWorkouts) - 3]) ?></li>
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


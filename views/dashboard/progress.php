<?php

/**
 * Opis: Widok odpowiedzialny za renderowanie interfejsu uytkownika.
 */


use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array $totals */
/** @var array $incompleteWorkouts */
/** @var string[] $weeklyLabels */
/** @var int[] $weeklyPlanned */
/** @var int[] $weeklyCompleted */
/** @var string[] $weekdayLabels */
/** @var int[] $weekdayCompletionRate */
/** @var array<int, array{title:string,text:string,type:string}> $recommendations */

$this->title = Yii::t('app', 'Progres');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="progress-page">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1"><?= Html::encode($this->title) ?></h1>
            <p class="text-muted mb-0"><?= Yii::t('app', 'Analiza wykonania treningów i skuteczności planu.') ?></p>
        </div>
        <?= Html::a('<i class="bi bi-activity me-1"></i> ' . Yii::t('app', 'Plany treningowe'), ['/workout/index'], ['class' => 'btn btn-outline-primary']) ?>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card p-3 h-100 text-center">
                <div class="text-muted small"><?= Yii::t('app', 'Ukończone') ?></div>
                <div class="card-value text-success"><?= (int) $totals['completed'] ?></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card p-3 h-100 text-center">
                <div class="text-muted small"><?= Yii::t('app', 'W trakcie') ?></div>
                <div class="card-value text-warning"><?= (int) $totals['inProgress'] ?></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card p-3 h-100 text-center">
                <div class="text-muted small"><?= Yii::t('app', 'Nierozpoczęte') ?></div>
                <div class="card-value text-danger"><?= (int) $totals['notStarted'] ?></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card p-3 h-100 text-center">
                <div class="text-muted small"><?= Yii::t('app', 'Skuteczność') ?></div>
                <div class="card-value text-info"><?= (int) $totals['completionPercent'] ?>%</div>
            </div>
        </div>
    </div>

    <div class="card p-3 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="h5 mb-0"><?= Yii::t('app', 'Rekomendacje tygodnia') ?></h3>
            <span class="badge bg-primary-subtle text-primary-emphasis border"><?= Yii::t('app', 'Co poprawić') ?></span>
        </div>
        <div class="row g-3">
            <?php foreach ($recommendations as $item): ?>
                <?php
                    $badgeClass = 'text-bg-secondary';
                    if ($item['type'] === 'success') {
                        $badgeClass = 'text-bg-success';
                    } elseif ($item['type'] === 'warning') {
                        $badgeClass = 'text-bg-warning';
                    } elseif ($item['type'] === 'danger') {
                        $badgeClass = 'text-bg-danger';
                    } elseif ($item['type'] === 'info') {
                        $badgeClass = 'text-bg-info';
                    }
                ?>
                <div class="col-md-6 col-xl-3">
                    <article class="border rounded p-3 h-100">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <strong class="small"><?= Html::encode($item['title']) ?></strong>
                            <span class="badge <?= $badgeClass ?>"><?= Yii::t('app', 'Wskazówka') ?></span>
                        </div>
                        <p class="small mb-0 text-muted"><?= Html::encode($item['text']) ?></p>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card p-3 h-100">
                <h3 class="h5 mb-3"><?= Yii::t('app', 'Trend tygodniowy') ?></h3>
                <?php if (empty($weeklyLabels)): ?>
                    <p class="text-muted mb-0"><?= Yii::t('app', 'Za mało danych do trendu tygodniowego.') ?></p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th><?= Yii::t('app', 'Tydzień') ?></th>
                                    <th><?= Yii::t('app', 'Zaplanowane') ?></th>
                                    <th><?= Yii::t('app', 'Ukończone') ?></th>
                                    <th><?= Yii::t('app', 'Procent') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($weeklyLabels as $i => $label): ?>
                                    <?php
                                        $planned = (int) $weeklyPlanned[$i];
                                        $done = (int) $weeklyCompleted[$i];
                                        $rate = $planned > 0 ? (int) round(($done / $planned) * 100) : 0;
                                    ?>
                                    <tr>
                                        <td><?= Html::encode($label) ?></td>
                                        <td><?= $planned ?></td>
                                        <td><?= $done ?></td>
                                        <td>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar" role="progressbar" style="width: <?= $rate ?>%"></div>
                                            </div>
                                            <small class="text-muted"><?= $rate ?>%</small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card p-3 h-100">
                <h3 class="h5 mb-3"><?= Yii::t('app', 'Skuteczność wg dnia') ?></h3>
                <ul class="list-group list-group-flush">
                    <?php foreach ($weekdayLabels as $i => $day): ?>
                        <?php $rate = (int) $weekdayCompletionRate[$i]; ?>
                        <li class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span><?= Html::encode($day) ?></span>
                                <span class="badge bg-secondary"><?= $rate ?>%</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar" role="progressbar" style="width: <?= $rate ?>%"></div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="card p-3 mt-4">
        <h3 class="h5 mb-3"><?= Yii::t('app', 'Do domknięcia') ?></h3>
        <?php if (empty($incompleteWorkouts)): ?>
            <p class="text-muted mb-0"><?= Yii::t('app', 'Świetna robota. Wszystkie treningi są ukończone.') ?></p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th><?= Yii::t('app', 'Trening') ?></th>
                            <th><?= Yii::t('app', 'Dzień') ?></th>
                            <th><?= Yii::t('app', 'Postęp') ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($incompleteWorkouts as $item): ?>
                            <?php
                                $total = (int) $item['total'];
                                $done = (int) $item['done'];
                                $rate = $total > 0 ? (int) round(($done / $total) * 100) : 0;
                            ?>
                            <tr>
                                <td><?= Html::encode($item['name']) ?></td>
                                <td><?= Html::encode($item['weekday']) ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 8px; min-width: 120px;">
                                            <div class="progress-bar" role="progressbar" style="width: <?= $rate ?>%"></div>
                                        </div>
                                        <small class="text-muted"><?= $done ?>/<?= $total ?></small>
                                    </div>
                                </td>
                                <td class="text-end"><?= Html::a(Yii::t('app', 'Otwórz'), ['/workout/view', 'id' => $item['id']], ['class' => 'btn btn-sm btn-outline-primary']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

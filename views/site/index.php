<?php
use yii\helpers\Html;

/** @var yii\web\View $this */

$this->title = 'Plan Treningowy';
$this->registerCssFile('@web/css/site.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
?>

<div class="flex-grow-1 p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0 neon-heading">Trenuj jak Olimpijczyk</h1>
        <button class="btn btn-neon px-4">+ Dodaj trening</button>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card p-4 text-center">
                <div class="text-muted small">Streak</div>
                <div class="card-value">14 dni</div>
                <div class="text-success small mt-1">osobisty rekord</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-4 text-center">
                <div class="text-muted small">Treningi marzec</div>
                <div class="card-value">19 / 26</div>
                <div class="text-info small mt-1">73%</div>
            </div>
        </div>
        <div class="col-md-4"></div>
    </div>

    <h3>Kalendarz Treningów</h3>
    <div class="card p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0"></h4>
            <div class="text-muted small">Tydzień · Miesiąc</div>
        </div>

        <div class="row g-2 text-center fw-bold text-muted small mb-2">
            <div class="col">Nd</div>
            <div class="col">Pn</div>
            <div class="col">Wt</div>
            <div class="col">Śr</div>
            <div class="col">Cz</div>
            <div class="col">Pt</div>
            <div class="col">So</div>
        </div>

        <div class="row g-2">
            <div class="col"><div class="day"></div></div>
            <div class="col"><div class="day"></div></div>
            <div class="col"><div class="day"></div></div>
            <div class="col"><div class="day workout-push"><br><small></small></div></div>
            <div class="col"><div class="day workout-pull"><br><small></small></div></div>
            <div class="col"><div class="day workout-legs"><br><small></small></div></div>
            <div class="col"><div class="day today"><br><small></small></div></div>

            <div class="col"><div class="day workout-push"></div></div>
            <div class="col"><div class="day"></div></div>
            <div class="col"><div class="day workout-pull"></div></div>
            <div class="col"><div class="day"></div></div>
            <div class="col"><div class="day workout-legs"></div></div>
            <div class="col"><div class="day"></div></div>
            <div class="col"><div class="day"></div></div>
        </div>
    </div>
</div>
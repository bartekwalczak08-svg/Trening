<?php

/**
 * Opis: Widok odpowiedzialny za renderowanie interfejsu użytkownika.
 */


/** @var yii\web\View $this */

use app\assets\SiteAboutAsset;
use yii\helpers\Html;

$this->title = Yii::t('app', 'O aplikacji');
$this->params['breadcrumbs'][] = $this->title;
SiteAboutAsset::register($this);
?>
<div class="site-about">
    <section class="card p-4 mb-4 about-hero">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3">
            <div>
                <h1 class="mb-2"><?= Html::encode($this->title) ?></h1>
                <p class="text-muted mb-0">
                    <?= Yii::t('app', 'Trening to aplikacja do planowania i monitorowania treningów siłowych, dzięki której łatwiej utrzymać regularność i widzieć realny progres.') ?>
                </p>
            </div>
            <span class="badge rounded-pill text-bg-info px-3 py-2"><?= Yii::t('app', 'Wersja MVP') ?></span>
        </div>
    </section>

    <section class="row g-4 mb-4">
        <div class="col-md-4">
            <article class="card h-100 p-3">
                <h3 class="h5 mb-2"><?= Yii::t('app', 'Planowanie') ?></h3>
                <p class="text-muted mb-0">
                    <?= Yii::t('app', 'Tworzysz plany treningowe, przypisujesz je do dni tygodnia i szybko wracasz do najważniejszych zadań na stronie głównej.') ?>
                </p>
            </article>
        </div>
        <div class="col-md-4">
            <article class="card h-100 p-3">
                <h3 class="h5 mb-2"><?= Yii::t('app', 'Realizacja') ?></h3>
                <p class="text-muted mb-0">
                    <?= Yii::t('app', 'Oznaczasz wykonanie ćwiczeń checkboxami, a status treningu aktualizuje się automatycznie: nierozpoczęty, w trakcie lub ukończony.') ?>
                </p>
            </article>
        </div>
        <div class="col-md-4">
            <article class="card h-100 p-3">
                <h3 class="h5 mb-2"><?= Yii::t('app', 'Analityka') ?></h3>
                <p class="text-muted mb-0">
                    <?= Yii::t('app', 'Zakładka Progres pokazuje trendy i rekomendacje tygodnia, żeby szybciej poprawiać słabe punkty planu.') ?>
                </p>
            </article>
        </div>
    </section>

    <section class="card p-4">
        <h2 class="h5 mb-3"><?= Yii::t('app', 'Dla kogo jest ta aplikacja?') ?></h2>
        <ul class="mb-0 text-muted">
            <li><?= Yii::t('app', 'dla osób trenujących regularnie i chcących mieć porządek w planie,') ?></li>
            <li><?= Yii::t('app', 'dla tych, którzy wracają do treningów i potrzebują prostego systemu,') ?></li>
            <li><?= Yii::t('app', 'dla każdego, kto chce mierzyć progres zamiast trenować "na wyczucie".') ?></li>
        </ul>
    </section>
</div>


<?php

/**
 * Opis: Widok odpowiedzialny za renderowanie interfejsu użytkownika.
 */


/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'O aplikacji';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about">
    <section class="card p-4 mb-4 about-hero">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3">
            <div>
                <h1 class="mb-2"><?= Html::encode($this->title) ?></h1>
                <p class="text-muted mb-0">
                    Trening to aplikacja do planowania i monitorowania treningów siłowych,
                    dzięki której łatwiej utrzymać regularność i widzieć realny progres.
                </p>
            </div>
            <span class="badge rounded-pill text-bg-info px-3 py-2">Wersja MVP</span>
        </div>
    </section>

    <section class="row g-4 mb-4">
        <div class="col-md-4">
            <article class="card h-100 p-3">
                <h3 class="h5 mb-2">Planowanie</h3>
                <p class="text-muted mb-0">
                    Tworzysz plany treningowe, przypisujesz je do dni tygodnia i szybko
                    wracasz do najważniejszych zadań na stronie głównej.
                </p>
            </article>
        </div>
        <div class="col-md-4">
            <article class="card h-100 p-3">
                <h3 class="h5 mb-2">Realizacja</h3>
                <p class="text-muted mb-0">
                    Oznaczasz wykonanie ćwiczeń checkboxami, a status treningu aktualizuje
                    się automatycznie: nierozpoczęty, w trakcie lub ukończony.
                </p>
            </article>
        </div>
        <div class="col-md-4">
            <article class="card h-100 p-3">
                <h3 class="h5 mb-2">Analityka</h3>
                <p class="text-muted mb-0">
                    Zakładka Progres pokazuje trendy i rekomendacje tygodnia,
                    żeby szybciej poprawiać słabe punkty planu.
                </p>
            </article>
        </div>
    </section>

    <section class="card p-4">
        <h2 class="h5 mb-3">Dla kogo jest ta aplikacja?</h2>
        <ul class="mb-0 text-muted">
            <li>dla osób trenujących regularnie i chcących mieć porządek w planie,</li>
            <li>dla tych, którzy wracają do treningów i potrzebują prostego systemu,</li>
            <li>dla każdego, kto chce mierzyć progres zamiast trenować "na wyczucie".</li>
        </ul>
    </section>
</div>

<style>
.about-hero {
    border: 1px solid var(--border, #dbe4ef);
}
</style>

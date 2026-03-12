<?php

/**
 * Opis: Widok odpowiedzialny za renderowanie interfejsu uytkownika.
 */


/** @var yii\web\View $this */
/** @var string $content */

use app\assets\ThemeAsset;
use app\assets\LayoutAsset;
use app\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\helpers\Url;

ThemeAsset::register($this);
LayoutAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <title><?= Html::encode($this->title) ?></title>
    <script>
        (function () {
            try {
                var stored = localStorage.getItem('theme');
                document.documentElement.setAttribute('data-theme', stored === 'dark' ? 'dark' : 'light');
            } catch (e) {
                document.documentElement.setAttribute('data-theme', 'light');
            }
        })();
    </script>
    <?php $this->head() ?>
    <!-- bootstrap icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="d-flex flex-column min-vh-100">
<?php $this->beginBody() ?>

<header id="header">
    <?php
    NavBar::begin([
        'brandLabel' => '',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => ['class' => 'navbar-expand-md fixed-top app-navbar']
    ]);
    $currentLanguage = (string) Yii::$app->language;
    $switchToLanguage = $currentLanguage === 'en-US' ? 'pl-PL' : 'en-US';
    $languageButtonLabel = $currentLanguage === 'en-US' ? 'PL' : 'EN';
    $languageSwitchUrl = Url::current(['lang' => $switchToLanguage]);

    echo '<div class="ms-auto d-flex align-items-center gap-2">';
    echo Html::button('<i class="bi bi-list"></i> <span>' . Yii::t('app', 'Menu') . '</span>', [
        'class' => 'btn btn-sm btn-outline-light mobile-menu-btn d-md-none',
        'type' => 'button',
        'aria-controls' => 'mobile-sidebar',
        'aria-expanded' => 'false',
        'aria-label' => Yii::t('app', 'Pokaż lub ukryj menu'),
    ]);
    echo Html::a('<i class="bi bi-translate"></i> <span>' . $languageButtonLabel . '</span>', $languageSwitchUrl, [
        'class' => 'btn btn-sm btn-outline-light language-toggle-btn',
        'aria-label' => Yii::t('app', 'Przełącz język na {lang}', ['lang' => $switchToLanguage]),
    ]);
    echo Html::button('<i class="bi bi-moon-stars"></i> <span id="theme-toggle-label">' . Yii::t('app', 'Ciemny') . '</span>', [
        'id' => 'theme-toggle',
        'class' => 'btn btn-sm btn-outline-light theme-toggle-btn',
        'type' => 'button',
        'data-light-label' => Yii::t('app', 'Jasny'),
        'data-dark-label' => Yii::t('app', 'Ciemny'),
    ]);
    echo '</div>';
    // only show brand, menu in sidebar
    NavBar::end();
    ?>
</header>

<?= Html::button('<i class="bi bi-list"></i>', [
    'id' => 'mobile-menu-fab',
    'class' => 'btn btn-primary mobile-menu-fab d-md-none',
    'type' => 'button',
    'aria-controls' => 'mobile-sidebar',
    'aria-expanded' => 'false',
    'aria-label' => Yii::t('app', 'Otwórz menu'),
]) ?>

<div class="d-flex padding-navbar">
    <div class="d-flex flex-column flex-shrink-0 p-3 sidebar">
        <div id="mobile-sidebar" class="mobile-sidebar-panel d-md-block">
            <?php
            $commonItems = [
                ['label' => '<i class="bi bi-house me-1"></i>' . Yii::t('app', 'Strona główna'), 'url' => ['/site/index'], 'encode' => false],
                ['label' => '<i class="bi bi-info-circle me-1"></i>' . Yii::t('app', 'O aplikacji'), 'url' => ['/site/about'], 'encode' => false],
                ['label' => '<i class="bi bi-envelope me-1"></i>' . Yii::t('app', 'Kontakt'), 'url' => ['/site/contact'], 'encode' => false],
                ['label' => '<i class="bi bi-inbox me-1"></i>' . Yii::t('app', 'Zgłoszenia'), 'url' => ['/site/contact-messages'], 'encode' => false],
            ];

            $guestItems = [
                ['label' => '<i class="bi bi-box-arrow-in-right me-1"></i>' . Yii::t('app', 'Logowanie'), 'url' => ['/site/login'], 'encode' => false],
                ['label' => '<i class="bi bi-person-plus me-1"></i>' . Yii::t('app', 'Rejestracja'), 'url' => ['/site/signup'], 'encode' => false],
            ];

            $identity = Yii::$app->user->identity;
            $logoutUsername = $identity && isset($identity->username) && $identity->username !== ''
                ? (string) $identity->username
                : Yii::t('app', 'użytkownik');

            $authItems = [
                ['label' => '<i class="bi bi-person me-1"></i>' . Yii::t('app', 'Profil'), 'url' => ['/site/profile'], 'encode' => false],
                '<li><hr class="text-secondary"></li>',
                '<li><span class="text-secondary small">' . Yii::t('app', 'Planowanie') . '</span></li>',
                ['label' => '&nbsp;<i class="bi bi-speedometer2"></i> ' . Yii::t('app', 'Panel'), 'url' => ['/dashboard/index'], 'encode' => false],
                ['label' => '&nbsp;<i class="bi bi-calendar-event"></i> ' . Yii::t('app', 'Kalendarz'), 'url' => ['/workout/calendar'], 'encode' => false],
                ['label' => '&nbsp;<i class="bi bi-activity"></i> ' . Yii::t('app', 'Plany treningowe'), 'url' => ['/workout/index'], 'encode' => false],
                ['label' => '&nbsp;<i class="bi bi-graph-up"></i> ' . Yii::t('app', 'Progres'), 'url' => ['/dashboard/progress'], 'encode' => false],
                '<li><hr class="text-secondary"></li>',
                '<li class="nav-item">'
                    . Html::beginForm(['/site/logout'])
                    . Html::submitButton(
                        '<i class="bi bi-box-arrow-right me-1"></i>' . Yii::t('app', 'Wyloguj') . ' (' . Html::encode($logoutUsername) . ')',
                        ['class' => 'nav-link btn btn-link logout']
                    )
                    . Html::endForm()
                    . '</li>',
            ];

            echo Nav::widget([
                'options' => ['class' => 'nav nav-pills flex-column mb-auto'],
                'items' => array_filter(array_merge(
                    $commonItems,
                    Yii::$app->user->isGuest ? $guestItems : $authItems
                )),
            ]);
            ?>
        </div>
    </div>
    <main id="main" class="flex-grow-1" role="main">
        <div class="container">
            <?php if (!empty($this->params['breadcrumbs'])): ?>
                <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?>
            <?php endif ?>
            <?= Alert::widget() ?>
            <?= $content ?>
        </div>
    </main>
</div>

<footer id="footer" class="app-footer mt-5">
    <div class="container py-2 py-lg-2">
        <div class="row g-2 align-items-start">
            <div class="col-lg-5">
                <div class="app-footer-brand"><?= Yii::t('app', 'Plan Treningowy') ?></div>
                <p class="app-footer-text mb-0">
                    <?= Yii::t('app', 'Trenuj regularnie i śledź progres.') ?>
                </p>
            </div>

            <div class="col-sm-6 col-lg-4">
                <h6 class="app-footer-heading"><?= Yii::t('app', 'Skróty') ?></h6>
                <ul class="app-footer-links list-unstyled mb-0">
                    <li><?= Html::a(Yii::t('app', 'Panel'), ['/dashboard/index']) ?></li>
                    <li><?= Html::a(Yii::t('app', 'Kalendarz'), ['/workout/calendar']) ?></li>
                    <li><?= Html::a(Yii::t('app', 'Plany treningowe'), ['/workout/index']) ?></li>
                    <li><?= Html::a(Yii::t('app', 'Kontakt'), ['/site/contact']) ?></li>
                </ul>
            </div>

            <div class="col-sm-6 col-lg-3">
                <h6 class="app-footer-heading"><?= Yii::t('app', 'Status') ?></h6>
                <div class="app-footer-pill">
                    <i class="bi bi-shield-check me-1"></i>
                    <?= Yii::t('app', '{appName} aktywny', ['appName' => Html::encode(Yii::$app->name)]) ?>
                </div>
            </div>
        </div>

        <div class="app-footer-bottom mt-2 pt-1 d-flex flex-column flex-md-row justify-content-between gap-1">
            <span>&copy; <?= date('Y') ?> <?= Html::encode(Yii::$app->name) ?>. <?= Yii::t('app', 'Wszelkie prawa zastrzeżone.') ?></span>
        </div>
    </div>
</footer>

<?php $this->endBody() ?>
<script>
    (function () {
        var mobileMenuBtn = document.querySelector('.mobile-menu-btn');
        var mobileFabBtn = document.getElementById('mobile-menu-fab');
        var mobileSidebar = document.getElementById('mobile-sidebar');

        // Framework-independent mobile menu toggle for consistent behavior.
        function syncMenuAria(expanded) {
            if (mobileMenuBtn) {
                mobileMenuBtn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            }
            if (mobileFabBtn) {
                mobileFabBtn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            }
        }

        function toggleMobileMenu() {
            if (!mobileSidebar) {
                return;
            }

            var isExpanded = mobileSidebar.classList.contains('show');
            mobileSidebar.classList.toggle('show');
            syncMenuAria(!isExpanded);
        }

        if (mobileSidebar) {
            if (mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', toggleMobileMenu);
            }
            if (mobileFabBtn) {
                mobileFabBtn.addEventListener('click', toggleMobileMenu);
            }
        }

        // Close collapsed mobile sidebar after tapping any navigation link.
        if (mobileSidebar) {
            mobileSidebar.querySelectorAll('a.nav-link').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (window.innerWidth >= 768) {
                        return;
                    }

                    mobileSidebar.classList.remove('show');

                    syncMenuAria(false);
                });
            });
        }

        window.addEventListener('resize', function () {
            if (!mobileSidebar) {
                return;
            }

            if (window.innerWidth >= 768) {
                mobileSidebar.classList.remove('show');
                syncMenuAria(false);
            }
        });
    })();
</script>
</body>
</html>
<?php $this->endPage() ?>

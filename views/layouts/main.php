<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;

AppAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <style>
        
        .padding-navbar { padding-top: var(--bs-navbar-height, 56px); }
        .sidebar { width: 240px; }
    </style>
</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<header id="header">
    <?php
    NavBar::begin([
        'brandLabel' => Yii::$app->name,
        'brandUrl' => Yii::$app->homeUrl,
        'options' => ['class' => 'navbar-expand-md navbar-dark bg-dark fixed-top']
    ]);
    // only show brand, menu in sidebar
    NavBar::end();
    ?>
</header>

<div class="d-flex padding-navbar">
    <div class="d-flex flex-column flex-shrink-0 p-3 bg-dark sidebar">
        <?php
        echo Nav::widget([
            'options' => ['class' => 'nav nav-pills flex-column mb-auto'],
            'items' => array_filter(array_merge(
                [
                    ['label' => 'About', 'url' => ['/site/about']],
                    ['label' => 'Contact', 'url' => ['/site/contact']],
                ],
                Yii::$app->user->isGuest ?
                    [['label' => 'Login', 'url' => ['/site/login']], ['label' => 'Sign up', 'url' => ['/site/signup']]] :
                    [
                        ['label' => 'Profile', 'url' => ['/site/profile']],
                        '<li><hr class="text-secondary"></li>',
                        '<li><span class="text-secondary small">Planowanie</span></li>',
                        ['label' => '&nbsp;Panel', 'url' => ['dashboard/index'], 'encode' => false],
                        ['label' => '&nbsp;Kalendarz', 'url' => ['#'], 'encode' => false],
                        ['label' => '&nbsp;Plany treningowe', 'url' => ['#'], 'encode' => false],
                        ['label' => '&nbsp;Progres', 'url' => ['#'], 'encode' => false],
                        '<li><hr class="text-secondary"></li>',
                        '<li class="nav-item">'
                            . Html::beginForm(['/site/logout'])
                            . Html::submitButton(
                                'Logout (' . Yii::$app->user->identity->username . ')',
                                ['class' => 'nav-link btn btn-link logout']
                            )
                            . Html::endForm()
                            . '</li>',
                    ]
            ))
        ]);
        ?>
    </div>
    <main id="main" class="flex-grow-1" role="main">
        <div class="container">

<main id="main" class="flex-shrink-0" role="main">
    <div class="container">
        <?php if (!empty($this->params['breadcrumbs'])): ?>
            <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?>
        <?php endif ?>
        <?= Alert::widget() ?>
        <?= $content ?>
        </div>
    </main>
</div>

<footer id="footer" class="mt-auto py-3 bg-light">
    <div class="container">
        <div class="row text-muted">
            <div class="col-md-6 text-center text-md-start">&copy; KOKSOWNIA SP.Z O.O.  <?= date('Y') ?></div>
        </div>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>

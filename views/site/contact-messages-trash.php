<?php

/**
 * Opis: Widok odpowiedzialny za renderowanie interfejsu uzytkownika.
 */

/** @var yii\web\View $this */
/** @var app\models\ContactMessage[] $messages */
/** @var yii\data\Pagination $pagination */

use yii\bootstrap5\LinkPager;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Kosz zgłoszeń');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Kontakt'), 'url' => ['contact']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Zgłoszenia kontaktowe'), 'url' => ['contact-messages']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-contact-messages-trash">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0"><?= Html::encode($this->title) ?></h1>
        <?= Html::a(Yii::t('app', 'Powrót do zgłoszeń'), ['/site/contact-messages'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <div class="card p-3">
        <?php if (empty($messages)): ?>
            <p class="text-muted mb-0"><?= Yii::t('app', 'Kosz jest pusty.') ?></p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?= Yii::t('app', 'Data usunięcia') ?></th>
                            <th><?= Yii::t('app', 'Nadawca') ?></th>
                            <th><?= Yii::t('app', 'Temat') ?></th>
                            <th><?= Yii::t('app', 'Status') ?></th>
                            <th><?= Yii::t('app', 'Akcje') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $item): ?>
                            <tr>
                                <td><?= (int) $item->id ?></td>
                                <td><?= $item->deleted_at ? date('d.m.Y H:i', (int) $item->deleted_at) : '-' ?></td>
                                <td>
                                    <strong><?= Html::encode($item->name) ?></strong><br>
                                    <small class="text-muted"><?= Html::encode($item->email) ?></small>
                                </td>
                                <td><?= Html::encode($item->subject) ?></td>
                                <td><span class="badge text-bg-secondary"><?= Html::encode($item->getStatusLabel()) ?></span></td>
                                <td class="text-nowrap">
                                    <?= Html::a(Yii::t('app', 'Przywróć'), ['restore-contact-message', 'id' => $item->id], [
                                        'class' => 'btn btn-sm btn-outline-success',
                                        'data' => [
                                            'method' => 'post',
                                        ],
                                    ]) ?>
                                    <?= Html::a(Yii::t('app', 'Usuń trwale'), ['purge-contact-message', 'id' => $item->id], [
                                        'class' => 'btn btn-sm btn-outline-danger',
                                        'data' => [
                                            'confirm' => Yii::t('app', 'Na pewno trwale usunąć to zgłoszenie?'),
                                            'method' => 'post',
                                        ],
                                    ]) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($pagination->getPageCount() > 1): ?>
                <div class="mt-3">
                    <?= LinkPager::widget([
                        'pagination' => $pagination,
                    ]) ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

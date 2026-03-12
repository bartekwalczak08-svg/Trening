<?php

/**
 * Opis: Widok odpowiedzialny za renderowanie interfejsu użytkownika.
 */

/** @var yii\web\View $this */
/** @var app\models\ContactMessage[] $messages */
/** @var yii\data\Pagination $pagination */
/** @var int $trashCount */

use yii\helpers\Html;
use yii\bootstrap5\LinkPager;

$this->title = Yii::t('app', 'Zgłoszenia kontaktowe');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Kontakt'), 'url' => ['contact']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-contact-messages">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0"><?= Html::encode($this->title) ?></h1>
        <div class="d-flex gap-2">
            <?= Html::a(Yii::t('app', 'Nowa wiadomość'), ['/site/contact'], ['class' => 'btn btn-primary']) ?>
            <?php if (!Yii::$app->user->isGuest): ?>
                <?= Html::a(Yii::t('app', 'Kosz ({count})', ['count' => (int) $trashCount]), ['/site/contact-messages-trash'], ['class' => 'btn btn-outline-secondary']) ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="card p-3">
        <?php if (empty($messages)): ?>
            <p class="text-muted mb-0"><?= Yii::t('app', 'Brak zgłoszeń.') ?></p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?= Yii::t('app', 'Data') ?></th>
                            <th><?= Yii::t('app', 'Nadawca') ?></th>
                            <th><?= Yii::t('app', 'Temat') ?></th>
                            <th><?= Yii::t('app', 'Status') ?></th>
                            <th><?= Yii::t('app', 'Treść') ?></th>
                            <?php if (!Yii::$app->user->isGuest): ?>
                                <th><?= Yii::t('app', 'Akcje') ?></th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $item): ?>
                            <tr>
                                <td><?= (int) $item->id ?></td>
                                <td><?= date('d.m.Y H:i', (int) $item->created_at) ?></td>
                                <td>
                                    <strong><?= Html::encode($item->name) ?></strong><br>
                                    <small class="text-muted"><?= Html::encode($item->email) ?></small>
                                </td>
                                <td><?= Html::encode($item->subject) ?></td>
                                <td>
                                    <?php
                                    // Map DB status to a visible badge color.
                                    $class = 'text-bg-secondary';
                                    if ($item->status === \app\models\ContactMessage::STATUS_NEW) {
                                        $class = 'text-bg-danger';
                                    } elseif ($item->status === \app\models\ContactMessage::STATUS_IN_PROGRESS) {
                                        $class = 'text-bg-warning';
                                    } elseif ($item->status === \app\models\ContactMessage::STATUS_CLOSED) {
                                        $class = 'text-bg-success';
                                    }
                                    ?>
                                    <span class="badge <?= $class ?>"><?= Html::encode($item->getStatusLabel()) ?></span>
                                </td>
                                <td style="max-width: 420px;">
                                    <div class="small"><?= nl2br(Html::encode($item->body)) ?></div>
                                </td>
                                <?php if (!Yii::$app->user->isGuest): ?>
                                    <td class="text-nowrap">
                                        <?= Html::a(Yii::t('app', 'Usuń'), ['delete-contact-message', 'id' => $item->id], [
                                            'class' => 'btn btn-sm btn-outline-danger',
                                            'data' => [
                                                'confirm' => Yii::t('app', 'Przenieść zgłoszenie do kosza?'),
                                                'method' => 'post',
                                            ],
                                        ]) ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (isset($pagination) && $pagination->getPageCount() > 1): ?>
                <div class="mt-3">
                    <?= LinkPager::widget([
                        'pagination' => $pagination,
                    ]) ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php

namespace tests\unit\models;

use app\models\ContactForm;
use yii\mail\MessageInterface;

class ContactFormTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    public $tester;

    public function testEmailIsSentOnContact()
    {
        $model = new ContactForm();

        $model->attributes = [
            'name' => 'Tester',
            'email' => 'tester@example.com',
            'subject' => 'very important letter subject',
            'body' => 'body of current message',
            'verifyCode' => 'testme',
        ];

        verify($model->contact('admin@example.com'))->notEmpty();

        // using Yii2 module actions to check email was sent
        $this->tester->seeEmailIsSent();

        /** @var MessageInterface $emailMessage */
        $emailMessage = $this->tester->grabLastSentEmail();
        verify($emailMessage)->instanceOf('yii\mail\MessageInterface');
        verify($emailMessage->getTo())->arrayHasKey('admin@example.com');
        verify($emailMessage->getFrom())->arrayHasKey('noreply@example.com');
        verify($emailMessage->getReplyTo())->arrayHasKey('tester@example.com');
        verify($emailMessage->getSubject())->equals('very important letter subject');
        verify($emailMessage->toString())->stringContainsString('body of current message');
    }

    public function testBlacklistAllowsInnocentKurkaPhrase()
    {
        $original = \Yii::$app->params['contactBlacklistWords'] ?? [];
        \Yii::$app->params['contactBlacklistWords'] = ['kurwa'];

        try {
            $model = new ContactForm();
            $model->body = 'wody galon kurka wodna';

            verify($model->validate(['body']))->true();
            verify($model->getErrors('body'))->empty();
        } finally {
            \Yii::$app->params['contactBlacklistWords'] = $original;
        }
    }

    public function testBlacklistBlocksGreekHomoglyphVariant()
    {
        $original = \Yii::$app->params['contactBlacklistWords'] ?? [];
        \Yii::$app->params['contactBlacklistWords'] = ['kurwa'];

        try {
            $model = new ContactForm();
            $model->body = "K\u{03C5}rwa";

            verify($model->validate(['body']))->false();
            verify($model->getErrors('body'))->notEmpty();
        } finally {
            \Yii::$app->params['contactBlacklistWords'] = $original;
        }
    }

    public function testBlacklistBlocksDigitAndSymbolObfuscation()
    {
        $original = \Yii::$app->params['contactBlacklistWords'] ?? [];
        \Yii::$app->params['contactBlacklistWords'] = ['kurwa'];

        try {
            $model = new ContactForm();
            $model->body = 'k-u-r-123-w-a';

            verify($model->validate(['body']))->false();
            verify($model->getErrors('body'))->notEmpty();
        } finally {
            \Yii::$app->params['contactBlacklistWords'] = $original;
        }
    }

    public function testBlacklistBlocksYebAtCVariant()
    {
        $original = \Yii::$app->params['contactBlacklistWords'] ?? [];
        \Yii::$app->params['contactBlacklistWords'] = ['jebac'];

        try {
            $model = new ContactForm();
            $model->body = 'yeb@ć';

            verify($model->validate(['body']))->false();
            verify($model->getErrors('body'))->notEmpty();
        } finally {
            \Yii::$app->params['contactBlacklistWords'] = $original;
        }
    }

    public function testBlacklistBlocksQurwaVariant()
    {
        $original = \Yii::$app->params['contactBlacklistWords'] ?? [];
        \Yii::$app->params['contactBlacklistWords'] = ['kurwa'];

        try {
            $model = new ContactForm();
            $model->body = 'qurwa';

            verify($model->validate(['body']))->false();
            verify($model->getErrors('body'))->notEmpty();
        } finally {
            \Yii::$app->params['contactBlacklistWords'] = $original;
        }
    }

    public function testBlacklistBlocksCurwaVariant()
    {
        $original = \Yii::$app->params['contactBlacklistWords'] ?? [];
        \Yii::$app->params['contactBlacklistWords'] = ['kurwa'];

        try {
            $model = new ContactForm();
            $model->body = 'curwa';

            verify($model->validate(['body']))->false();
            verify($model->getErrors('body'))->notEmpty();
        } finally {
            \Yii::$app->params['contactBlacklistWords'] = $original;
        }
    }
}

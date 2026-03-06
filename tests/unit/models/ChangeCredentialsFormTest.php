<?php

namespace tests\unit\models;

use app\models\ChangeCredentialsForm;
use app\models\User;

class ChangeCredentialsFormTest extends \Codeception\Test\Unit
{
    protected function _before()
    {
        User::deleteAll();
    }

    public function testValidateCurrentPassword()
    {
        $user = new User();
        $user->username = 'tester';
        $user->email = 'tester@example.com';
        $user->setPassword('secret');
        $user->generateAuthKey();
        $user->save(false);

        $model = new ChangeCredentialsForm();
        $model->username = 'tester';
        $model->currentPassword = 'wrong';
        $model->newPassword = 'newpass';
        $model->newPasswordRepeat = 'newpass';

        verify($model->validate())->false();
        verify($model->errors)->arrayHasKey('currentPassword');
    }

    public function testUsernameUnique()
    {
        $user1 = new User();
        $user1->username = 'user1';
        $user1->email = 'user1@example.com';
        $user1->setPassword('pass');
        $user1->generateAuthKey();
        $user1->save(false);

        $user2 = new User();
        $user2->username = 'user2';
        $user2->email = 'user2@example.com';
        $user2->setPassword('pass2');
        $user2->generateAuthKey();
        $user2->save(false);

        \Yii::$app->user->login($user2);

        $model = new ChangeCredentialsForm();
        $model->username = 'user1';
        $model->currentPassword = 'pass2';

        verify($model->validate())->false();
        verify($model->errors)->arrayHasKey('username');
    }

    public function testEmailUnique()
    {
        $user1 = new User();
        $user1->username = 'a';
        $user1->email = 'a@example.com';
        $user1->setPassword('pass');
        $user1->generateAuthKey();
        $user1->save(false);

        $user2 = new User();
        $user2->username = 'b';
        $user2->email = 'b@example.com';
        $user2->setPassword('pass');
        $user2->generateAuthKey();
        $user2->save(false);

        \Yii::$app->user->login($user2);

        $model = new ChangeCredentialsForm();
        $model->username = 'b';
        $model->email = 'a@example.com';
        $model->currentPassword = 'pass';

        verify($model->validate())->false();
        verify($model->errors)->arrayHasKey('email');
    }

    public function testUpdateSuccess()
    {
        $user = new User();
        $user->username = 'foo';
        $user->email = 'foo@example.com';
        $user->setPassword('foo123');
        $user->generateAuthKey();
        $user->save(false);

        \Yii::$app->user->login($user);

        $model = new ChangeCredentialsForm();
        $model->username = 'foo2';
        $model->email = 'foo2@example.com';
        $model->currentPassword = 'foo123';
        $model->newPassword = 'bar456';
        $model->newPasswordRepeat = 'bar456';

        verify($model->update())->true();
        $user->refresh();
        verify($user->username)->equals('foo2');
        verify($user->validatePassword('bar456'))->true();
    }
}

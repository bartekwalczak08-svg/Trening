<?php

namespace tests\unit\models;

use app\models\SignupForm;
use app\models\User;

class SignupFormTest extends \Codeception\Test\Unit
{
    protected function _before()
    {
        User::deleteAll();
    }

    public function testSignupSuccess()
    {
        $model = new SignupForm([
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'password',
            'passwordRepeat' => 'password',
        ]);

        $user = $model->signup();
        verify($user)->notNull();
        verify($user->username)->equals('newuser');
        verify($user->validatePassword('password'))->true();
    }

    public function testSignupUsernameTaken()
    {
        $existing = new User();
        $existing->username = 'taken';
        $existing->email = 'taken@example.com';
        $existing->setPassword('123456');
        $existing->generateAuthKey();
        $existing->save(false);

        $model = new SignupForm([
            'username' => 'taken',
            'email' => 'other@example.com',
            'password' => 'password',
            'passwordRepeat' => 'password',
        ]);

        verify($model->signup())->null();
        verify($model->errors)->arrayHasKey('username');
    }

    public function testSignupEmailTaken()
    {
        $existing = new User();
        $existing->username = 'user1';
        $existing->email = 'taken@example.com';
        $existing->setPassword('123456');
        $existing->generateAuthKey();
        $existing->save(false);

        $model = new SignupForm([
            'username' => 'newuser',
            'email' => 'taken@example.com',
            'password' => 'password',
            'passwordRepeat' => 'password',
        ]);

        verify($model->signup())->null();
        verify($model->errors)->arrayHasKey('email');
    }

    public function testSignupPasswordMismatch()
    {
        $model = new SignupForm([
            'username' => 'user2',
            'email' => 'user2@example.com',
            'password' => 'password',
            'passwordRepeat' => 'different',
        ]);
        verify($model->signup())->null();
        verify($model->errors)->arrayHasKey('passwordRepeat');
    }
}

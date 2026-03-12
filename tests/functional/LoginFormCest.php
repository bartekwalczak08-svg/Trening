<?php

class LoginFormCest
{
    public function _before(\FunctionalTester $I)
    {
        $I->amOnRoute('site/login');
    }

    public function openLoginPage(\FunctionalTester $I)
    {
        $I->see('Login', 'h1');
    }

    // demonstrates `amLoggedInAs` method
    public function internalLoginById(\FunctionalTester $I)
    {
        $I->amLoggedInAs(100);
        $I->amOnPage('/');
        $I->see('Logout (admin)');
    }

    // demonstrates `amLoggedInAs` method
    public function internalLoginByInstance(\FunctionalTester $I)
    {
        $I->amLoggedInAs(\app\models\User::findByUsername('admin'));
        $I->amOnPage('/');
        $I->see('Logout (admin)');
    }

    public function loginWithEmptyCredentials(\FunctionalTester $I)
    {
        $I->submitForm('#login-form', []);
        $I->expectTo('see validations errors');
        $I->see('Username cannot be blank.');
        $I->see('Password cannot be blank.');
    }

    public function loginWithWrongCredentials(\FunctionalTester $I)
    {
        $I->submitForm('#login-form', [
            'LoginForm[username]' => 'admin',
            'LoginForm[password]' => 'wrong',
        ]);
        $I->expectTo('see validations errors');
        $I->see('Incorrect username or password.');
    }

    public function loginWithEmail(\FunctionalTester $I)
    {
        // ensure admin has an email in DB
        $I->haveInDatabase('user', [
            'id' => 100,
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password_hash' => Yii::$app->security->generatePasswordHash('admin123'),
            'auth_key' => Yii::$app->security->generateRandomString(),
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $I->submitForm('#login-form', [
            'LoginForm[username]' => 'admin@example.com',
            'LoginForm[password]' => 'admin123',
        ]);
        $I->see('Logout (admin)');
    }

    public function loginSuccessfully(\FunctionalTester $I)
    {
        $I->submitForm('#login-form', [
            'LoginForm[username]' => 'admin',
            'LoginForm[password]' => 'admin123',
        ]);
        $I->see('Logout (admin)');
        $I->dontSeeElement('form#login-form');
    }

    public function pendingDeleteLoginRedirectsToProfileWithoutStatusChange(\FunctionalTester $I)
    {
        $user = \app\models\User::findOne(101);
        if ($user === null) {
            $user = new \app\models\User();
            $user->id = 101;
            $user->created_at = time();
        }

        $user->username = 'pendinguser';
        $user->email = 'pending@example.com';
        $user->password_hash = Yii::$app->security->generatePasswordHash('pending123');
        $user->auth_key = Yii::$app->security->generateRandomString();
        $user->status = 'pending_delete';
        $user->delete_requested_at = time() - 3600;
        $user->updated_at = time();
        $user->save(false);

        $I->submitForm('#login-form', [
            'LoginForm[username]' => 'pendinguser',
            'LoginForm[password]' => 'pending123',
        ]);

        $I->seeCurrentRouteIs('site/profile');
        $reloaded = \app\models\User::findOne(101);
        \PHPUnit\Framework\Assert::assertNotNull($reloaded);
        \PHPUnit\Framework\Assert::assertSame('pending_delete', (string) $reloaded->status);
    }
}

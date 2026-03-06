<?php

use yii\helpers\Url;

class LoginCest
{
    public function ensureThatLoginWorks(AcceptanceTester $I)
    {
        $I->amOnPage(Url::toRoute('/site/login'));
        $I->see('Login', 'h1');

        $I->amGoingTo('try to login with correct credentials');
        $I->fillField('input[name="LoginForm[username]"]', 'admin');
        $I->fillField('input[name="LoginForm[password]"]', 'admin123');
        $I->click('login-button');
        $I->wait(2); // wait for button to be clicked

        $I->expectTo('see user info');
        $I->see('Logout');
    }

    public function loginWithEmail(AcceptanceTester $I)
    {
        // ensure admin user has email set in the database
        $I->haveInDatabase('user', [
            'id' => 100,
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password_hash' => Yii\$app->security->generatePasswordHash('admin123'),
            'auth_key' => Yii\$app->security->generateRandomString(),
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $I->amOnPage(Url::toRoute('/site/login'));
        $I->see('Login', 'h1');

        // use email instead of username
        $I->fillField('input[name="LoginForm[username]"]', 'admin@example.com');
        $I->fillField('input[name="LoginForm[password]"]', 'admin123');
        $I->click('login-button');
        $I->wait(1);
        $I->see('Logout');
    }

    public function signupViaForm(AcceptanceTester $I)
    {
        $I->amOnPage(Url::toRoute('/site/signup'));
        $I->see('Sign up', 'h1');
        $I->submitForm('#signup-form', [
            'SignupForm[username]' => 'foo123',
            'SignupForm[email]' => 'foo123@example.com',
            'SignupForm[password]' => 'pass123',
            'SignupForm[passwordRepeat]' => 'pass123',
        ]);
        $I->see('Logout (foo123)');
        $I->see('foo123');
    }

    public function seeProfileLinkWhenLoggedIn(AcceptanceTester $I)
    {
        $I->amOnPage(Url::toRoute('/site/login'));
        $I->fillField('input[name="LoginForm[username]"]', 'admin');
        $I->fillField('input[name="LoginForm[password]"]', 'admin123');
        $I->click('login-button');
        $I->wait(1);
        $I->see('Profile');
        $I->click('Profile');
        $I->see('Profile', 'h1');
        $I->seeElement('input[name="ChangeCredentialsForm[email]"]');
    }

    public function updateCredentialsFromProfile(AcceptanceTester $I)
    {
        $I->amOnPage(Url::toRoute('/site/login'));
        $I->fillField('input[name="LoginForm[username]"]', 'admin');
        $I->fillField('input[name="LoginForm[password]"]', 'admin123');
        $I->click('login-button');
        $I->wait(1);
        $I->click('Profile');
        $I->fillField('input[name="ChangeCredentialsForm[currentPassword]"]', 'admin123');
        $I->fillField('input[name="ChangeCredentialsForm[newPassword]"]', 'password1');
        $I->fillField('input[name="ChangeCredentialsForm[newPasswordRepeat]"]', 'password1');
        $I->click('Save changes');
        $I->see('Your account details have been updated.');
    }
}

<?php

class ProfileCest
{
    public function _before(FunctionalTester $I)
    {
        // ensure there is an admin user with id 100 (migration created it) and has an email
        $I->haveInDatabase('user', [
            'id' => 100,
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password_hash' => Yii\$app->security->generatePasswordHash('admin123'),
            'auth_key' => Yii\$app->security->generateRandomString(),
            'created_at' => time(),
            'updated_at' => time(),
        ]);
    }

    public function openProfilePage(FunctionalTester $I)
    {
        $I->amLoggedInAs(100);
        $I->amOnRoute('site/profile');
        $I->see('Profile', 'h1');
        $I->seeElement('form#profile-form');
    }

    public function changeUsername(FunctionalTester $I)
    {
        $I->amLoggedInAs(100);
        $I->amOnRoute('site/profile');
        $I->submitForm('#profile-form', [
            'ChangeCredentialsForm[username]' => 'admin2',
            'ChangeCredentialsForm[email]' => 'admin2@example.com',
            'ChangeCredentialsForm[currentPassword]' => 'admin123',
            'ChangeCredentialsForm[newPassword]' => '',
            'ChangeCredentialsForm[newPasswordRepeat]' => '',
        ]);
        $I->see('Your account details have been updated.');
        $I->seeInField('input[name="ChangeCredentialsForm[username]"]', 'admin2');
        $I->seeInField('input[name="ChangeCredentialsForm[email]"]', 'admin2@example.com');
    }

    public function changeEmailConflict(FunctionalTester $I)
    {
        // create another user with email conflict
        $I->haveInDatabase('user', [
            'username' => 'other',
            'email' => 'conflict@example.com',
            'password_hash' => Yii\$app->security->generatePasswordHash('pass'),
            'auth_key' => Yii\$app->security->generateRandomString(),
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $I->amLoggedInAs(100);
        $I->amOnRoute('site/profile');
        $I->submitForm('#profile-form', [
            'ChangeCredentialsForm[username]' => 'admin',
            'ChangeCredentialsForm[email]' => 'conflict@example.com',
            'ChangeCredentialsForm[currentPassword]' => 'admin123',
            'ChangeCredentialsForm[newPassword]' => '',
            'ChangeCredentialsForm[newPasswordRepeat]' => '',
        ]);
        $I->see('This email has already been taken.');
    }

    public function changePassword(FunctionalTester $I)
    {
        $I->amLoggedInAs(100);
        $I->amOnRoute('site/profile');
        $I->submitForm('#profile-form', [
            'ChangeCredentialsForm[username]' => 'admin',
            'ChangeCredentialsForm[email]' => 'admin@example.com',
            'ChangeCredentialsForm[currentPassword]' => 'admin123',
            'ChangeCredentialsForm[newPassword]' => 'newpass',
            'ChangeCredentialsForm[newPasswordRepeat]' => 'newpass',
        ]);
        $I->see('Your account details have been updated.');
        // log out and try to log in with new password
        $I->amOnRoute('site/logout');
        $I->amOnRoute('site/login');
        $I->fillField('input[name="LoginForm[username]"]', 'admin');
        $I->fillField('input[name="LoginForm[password]"]', 'newpass');
        $I->click('login-button');
        $I->see('Logout');
    }
}

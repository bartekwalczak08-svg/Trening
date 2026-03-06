<?php

class SignupCest
{
    public function openSignupPage(FunctionalTester $I)
    {
        $I->amOnRoute('site/signup');
        $I->see('Sign up', 'h1');
    }

    public function signupNewUser(FunctionalTester $I)
    {
        $I->amOnRoute('site/signup');
        $I->submitForm('#signup-form', [
            'SignupForm[username]' => 'newuser',
            'SignupForm[email]' => 'newuser@example.com',
            'SignupForm[password]' => 'password',
            'SignupForm[passwordRepeat]' => 'password',
        ]);
        // after signup, user is logged in automatically
        $I->see('Logout (newuser)');
    }

    public function signupWithExistingUsername(FunctionalTester $I)
    {
        $I->haveInDatabase('user', [
            'username' => 'existing',
            'email' => 'existing@example.com',
            'password_hash' => Yii\$app->security->generatePasswordHash('whatever'),
            'auth_key' => Yii\$app->security->generateRandomString(),
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $I->amOnRoute('site/signup');
        $I->submitForm('#signup-form', [
            'SignupForm[username]' => 'existing',
            'SignupForm[email]' => 'existing@example.com',
            'SignupForm[password]' => 'pass123',
            'SignupForm[passwordRepeat]' => 'pass123',
        ]);
        $I->see('This username has already been taken.');

        // now attempt with new username but same email
        $I->amOnRoute('site/signup');
        $I->submitForm('#signup-form', [
            'SignupForm[username]' => 'other',
            'SignupForm[email]' => 'existing@example.com',
            'SignupForm[password]' => 'pass123',
            'SignupForm[passwordRepeat]' => 'pass123',
        ]);
        $I->see('This email address has already been taken.');
    }
}

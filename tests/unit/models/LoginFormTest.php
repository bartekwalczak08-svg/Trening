<?php

namespace tests\unit\models;

use app\models\LoginForm;

class LoginFormTest extends \Codeception\Test\Unit
{
    private $model;

    /**
     * create and persist a user with given credentials
     * used by tests since User is now ActiveRecord.
     *
     * @param string $username
     * @param string $password
     * @return \app\models\User
     */
    /**
     * create and persist a user with given credentials
     * used by tests since User is now ActiveRecord.
     *
     * @param string $username
     * @param string $password
     * @param string|null $email
     * @return \app\models\User
     */
    protected function createUser($username, $password, $email = null)
    {
        $user = new \app\models\User();
        $user->username = $username;
        if ($email !== null) {
            $user->email = $email;
        }
        $user->setPassword($password);
        $user->generateAuthKey();
        $user->save(false);
        return $user;
    }

    protected function _before()
    {
        // clear any users created by previous test
        \app\models\User::deleteAll();
    }

    protected function _after()
    {
        \Yii::$app->user->logout();
    }

    public function testLoginNoUser()
    {
        $this->model = new LoginForm([
            'username' => 'not_existing_username',
            'password' => 'not_existing_password',
        ]);

        verify($this->model->login())->false();
        verify(\Yii::$app->user->isGuest)->true();
    }

    public function testUsernameTooShort()
    {
        $this->model = new LoginForm([
            'username' => 'ab',
            'password' => 'whatever',
        ]);

        verify($this->model->validate())->false();
        verify($this->model->errors)->arrayHasKey('username');
    }

    public function testUsernameInvalidCharacters()
    {
        $this->model = new LoginForm([
            'username' => 'invalid user!',
            'password' => 'whatever',
        ]);

        verify($this->model->validate())->false();
        verify($this->model->errors)->arrayHasKey('username');
    }

    public function testPasswordTooShort()
    {
        $this->model = new LoginForm([
            'username' => 'demo',
            'password' => '123',
        ]);

        verify($this->model->validate())->false();
        verify($this->model->errors)->arrayHasKey('password');
    }

    public function testLoginWrongPassword()
    {
        // ensure there is a user in the database
        $this->createUser('demo', 'demo');

        $this->model = new LoginForm([
            'username' => 'demo',
            'password' => 'wrong_password',
        ]);

        verify($this->model->login())->false();
        verify(\Yii::$app->user->isGuest)->true();
        verify($this->model->errors)->arrayHasKey('password');
    }

    public function testLoginCorrect()
    {
        // demo user with matching password
        $this->createUser('demo', 'demo', 'demo@example.com');

        $this->model = new LoginForm([
            'username' => 'demo',
            'password' => 'demo',
        ]);

        verify($this->model->login())->true();
        verify(\Yii::$app->user->isGuest)->false();
        verify($this->model->errors)->arrayHasNotKey('password');
    }

    public function testLoginByEmail()
    {
        $this->createUser('user1', 'pass123', 'user1@example.com');

        $this->model = new LoginForm([
            'username' => 'user1@example.com',
            'password' => 'pass123',
        ]);

        verify($this->model->login())->true();
        verify(\Yii::$app->user->isGuest)->false();
    }

    public function testLoginByEmailWrongPassword()
    {
        $this->createUser('user2', 'secret', 'user2@example.com');

        $this->model = new LoginForm([
            'username' => 'user2@example.com',
            'password' => 'wrong',
        ]);

        verify($this->model->login())->false();
        verify($this->model->errors)->arrayHasKey('password');
    }
}

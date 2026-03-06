<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * Signup form collects user information for registration.
 */
class SignupForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $passwordRepeat;

    public function rules()
    {
        return [
            [['username', 'email', 'password', 'passwordRepeat'], 'required'],
            ['username', 'string', 'min' => 3, 'max' => 255],
            ['username', 'match', 'pattern' => '/^[a-zA-Z0-9_-]+$/',
                'message' => 'Only letters, numbers, dashes and underscores are allowed.'],
            ['username', 'validateUsernameUnique'],

            ['password', 'string', 'min' => 6],
            ['passwordRepeat', 'compare', 'compareAttribute' => 'password',
                'message' => 'Passwords do not match.'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\\app\\models\\User', 'message' => 'This email address has already been taken.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'passwordRepeat' => 'Repeat Password',
            'email' => 'Email',
        ];
    }

    public function validateUsernameUnique($attribute, $params)
    {
        if (User::find()->where(['username' => $this->username])->exists()) {
            $this->addError($attribute, 'This username has already been taken.');
        }
    }

    /**
     * Creates new user if validation passes
     * @return User|null
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User();
        $user->username = $this->username;
        $user->email = $this->email;
        $user->setPassword($this->password);
        $user->generateAuthKey();
        if ($user->save()) {
            return $user;
        }
        return null;
    }
}

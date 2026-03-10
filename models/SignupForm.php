<?php

/**
 * Opis: Model domenowy uywany w aplikacji.
 */


namespace app\models;

use Yii;
use yii\base\Model;

/**
 * Signup form collects user information for registration.
 */
// Klasa SignupForm.
class SignupForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $passwordRepeat;

    // Metoda rules.
    public function rules()
    {
        return [
            [['username', 'email', 'password', 'passwordRepeat'], 'required'],
            [['username', 'email'], 'trim'],
            ['username', 'string', 'min' => 3, 'max' => 255],
            ['username', 'match', 'pattern' => '/^[a-zA-Z0-9_\-ąćęłńóśżźĄĆĘŁŃÓŚŻŹ]+$/u',
                'message' => 'Only letters , numbers, dashes and underscores are allowed.'],
            ['username', 'validateUsernameUnique'],

            ['password', 'string', 'min' => 6],
            ['password', 'match', 'pattern' => '/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/',
                'message' => 'Password must contain at least one uppercase letter, one digit and one special character.'],
            ['password', 'validatePasswordDoesNotContainUsername'],
            ['passwordRepeat', 'compare', 'compareAttribute' => 'password',
                'message' => 'Passwords do not match.'],

            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\\app\\models\\User', 'message' => 'This email address has already been taken.'],
        ];
    }

    // Metoda attributeLabels.
    public function attributeLabels()
    {
        return [
            'username' => 'Username',
            'password' => 'Password',
            'passwordRepeat' => 'Repeat Password',
            'email' => 'Email',
        ];
    }

    // Metoda validateUsernameUnique.
    public function validateUsernameUnique($attribute, $params)
    {
        if (User::find()->where(['username' => $this->$attribute])->exists()) {
            $this->addError($attribute, 'This username has already been taken.');
        }
    }

    // Metoda validatePasswordDoesNotContainUsername.
    public function validatePasswordDoesNotContainUsername($attribute, $params)
    {
        if (strpos($this->$attribute, $this->username) !== false) {
            $this->addError($attribute, 'Password cannot contain your username.');
        }
    }

    /**
     * Creates new user if validation passes
     * @return User|null
     */
    // Metoda signup.
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

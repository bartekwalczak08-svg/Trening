<?php

/**
 * Opis: Model domenowy uywany w aplikacji.
 */


namespace app\models;

use Yii;
use yii\base\Model;

/**
 * Model formularza rejestracji nowego użytkownika.
 */
class SignupForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $passwordRepeat;

    /**
     * Reguły walidacji danych wymaganych przy rejestracji.
     */
    public function rules()
    {
        return [
            [['username', 'email', 'password', 'passwordRepeat'], 'required'],
            [['username', 'email'], 'trim'],
            ['username', 'string', 'min' => 3, 'max' => 255],
            ['username', 'match', 'pattern' => '/^[a-zA-Z0-9_\-ąćęłńóśżźĄĆĘŁŃÓŚŻŹ]+$/u',
                'message' => 'Dozwolone są tylko litery, cyfry, myślniki i podkreślenia.'],
            ['username', 'validateUsernameUnique'],

            ['password', 'string', 'min' => 6],
            ['password', 'match', 'pattern' => '/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/',
                'message' => 'Hasło musi zawierać co najmniej jedną wielką literę, jedną cyfrę i jeden znak specjalny.'],
            ['password', 'validatePasswordDoesNotContainUsername'],
            ['passwordRepeat', 'compare', 'compareAttribute' => 'password',
                'message' => 'Hasła nie są takie same.'],

            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\\app\\models\\User', 'message' => 'Ten adres e-mail jest już zajęty.'],
        ];
    }

    /**
     * Etykiety pól formularza.
     */
    public function attributeLabels()
    {
        return [
            'username' => 'Nazwa użytkownika',
            'password' => 'Hasło',
            'passwordRepeat' => 'Powtórz hasło',
            'email' => 'E-mail',
        ];
    }

    /**
     * Sprawdza unikalność nazwy użytkownika.
     */
    public function validateUsernameUnique($attribute, $params)
    {
        if (User::find()->where(['username' => $this->$attribute])->exists()) {
            $this->addError($attribute, 'Ta nazwa użytkownika jest już zajęta.');
        }
    }

    /**
     * Blokuje hasła zawierające nazwę użytkownika.
     */
    public function validatePasswordDoesNotContainUsername($attribute, $params)
    {
        if (strpos($this->$attribute, $this->username) !== false) {
            $this->addError($attribute, 'Hasło nie może zawierać nazwy użytkownika.');
        }
    }

    /**
     * Tworzy konto użytkownika po pomyślnej walidacji danych.
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User();
        $user->username = $this->username;
        $user->email = $this->email;
        $now = time();
        $user->created_at = $now;
        $user->updated_at = $now;
        $user->setPassword($this->password);
        $user->generateAuthKey();
        if ($user->save()) {
            return $user;
        }

        // Surface underlying User model errors on the signup form.
        foreach ($user->getErrors() as $attribute => $messages) {
            foreach ((array) $messages as $message) {
                if (in_array($attribute, ['username', 'email', 'password'], true)) {
                    $target = $attribute === 'password' ? 'password' : $attribute;
                    $this->addError($target, (string) $message);
                } else {
                    $this->addError('username', (string) $message);
                }
            }
        }

        if (!$this->hasErrors()) {
            $this->addError('username', 'Nie udało się utworzyć konta. Spróbuj ponownie.');
        }

        return null;
    }
}

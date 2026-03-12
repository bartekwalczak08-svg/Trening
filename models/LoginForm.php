<?php

/**
 * Opis: Model domenowy uywany w aplikacji.
 */


namespace app\models;

use Yii;
use yii\base\Model;

/**
 * Model formularza logowania.
 *
 * Umożliwia logowanie zarówno nazwą użytkownika, jak i adresem e-mail.
 *
 * @property-read User|null $user
 */
class LoginForm extends Model
{
    public $username; // username or email
    public $password;
    public $rememberMe = true;

    /** @var User|null */
    private $_user = null;

    /**
     * Reguły walidacji dla formularza logowania.
     */
    public function rules()
    {
        return [
            // identifier and password are both required
            [['username', 'password'], 'required'],
            // identifier length
            ['username', 'string', 'min' => 3, 'max' => 255],
            // if it looks like an email, validate format; otherwise restrict to username characters
            ['username', 'validateLogin'],
            // password must have a minimum length for security
            ['password', 'string', 'min' => 6],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Sprawdza, czy podane hasło pasuje do znalezionego użytkownika.
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, Yii::t('app', 'Nieprawidłowa nazwa użytkownika, e-mail lub hasło.'));
            }
        }
    }

    /**
     * Waliduje pole loginu: akceptuje nazwę użytkownika lub poprawny e-mail.
     */
    public function validateLogin($attribute, $params)
    {
        if (strpos($this->$attribute, '@') !== false) {
            if (!filter_var($this->$attribute, FILTER_VALIDATE_EMAIL)) {
                $this->addError($attribute, Yii::t('app', 'Nieprawidłowy adres e-mail.'));
            }
        } else {
            if (!preg_match('/^[a-zA-Z0-9_-]+$/', $this->$attribute)) {
                $this->addError($attribute, Yii::t('app', 'Dozwolone są tylko litery, cyfry, myślniki i podkreślenia.'));
            }
        }
    }

    /**
     * Loguje użytkownika po pozytywnej walidacji formularza.
     */
    public function login()
    {
        if ($this->validate()) {
            $user = $this->getUser();
            if ($user === null) {
                return false;
            }

            if ($user->isPendingDeleteExpired(30)) {
                $this->addError('username', Yii::t('app', 'Okres reaktywacji konta minął. Konto oczekuje na usunięcie.'));
                return false;
            }

            if ($user->isDeactivated() || $user->isPendingDelete()) {
                $user->activateAccount();
            }

            return Yii::$app->user->login($user, $this->rememberMe ? 3600 * 24 * 30 : 0);
        }
        return false;
    }

    /**
     * Etykiety pól formularza.
     */
    public function attributeLabels()
    {
        return [
            'username' => Yii::t('app', 'Nazwa użytkownika lub e-mail'),
        ];
    }

    /**
     * Wyszukuje użytkownika po nazwie lub e-mailu i cache'uje wynik.
     */
    public function getUser()
    {
        if ($this->_user === null) {
            // allow login by username or email
            $this->_user = User::find()
                ->where(['username' => $this->username])
                ->orWhere(['email' => $this->username])
                ->one();
        }

        return $this->_user;
    }
}

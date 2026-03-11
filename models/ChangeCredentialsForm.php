<?php

/**
 * Opis: Model domenowy uywany w aplikacji.
 */


namespace app\models;

use Yii;
use yii\base\Model;

/**
 * Model formularza zmiany danych konta użytkownika.
 *
 * Pozwala zmienić login, e-mail oraz opcjonalnie hasło po potwierdzeniu
 * aktualnego hasła.
 */
class ChangeCredentialsForm extends Model
{
    public $username;
    public $email;
    public $currentPassword;
    public $newPassword;
    public $newPasswordRepeat;

    /** @var User */
    private $_user;

    /**
     * Reguły walidacji dla zmiany danych konta.
     */
    public function rules()
    {
        return [
            [['username', 'email', 'currentPassword'], 'required'],
            [['username', 'email'], 'trim'],
            ['username', 'string', 'min' => 3, 'max' => 255],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'validateEmailUnique'],
            ['username', 'match', 'pattern' => '/^[a-zA-Z0-9_-]+$/',
                'message' => 'Only letters, numbers, dashes and underscores are allowed.'],
            ['username', 'validateUsernameUnique'],

            ['currentPassword', 'validateCurrentPassword'],

            ['newPassword', 'string', 'min' => 6, 'skipOnEmpty' => true],
            ['newPassword', 'match', 'pattern' => '/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/',
                'message' => 'Password must contain at least one uppercase letter, one digit and one special character.',
                'skipOnEmpty' => true],
            ['newPassword', 'validateNewPasswordDoesNotContainUsername'],
            ['newPasswordRepeat', 'compare', 'compareAttribute' => 'newPassword',
                'message' => 'Passwords do not match.',
                'skipOnEmpty' => true],
        ];
    }

    /**
     * Etykiety pól formularza.
     */
    public function attributeLabels()
    {
        return [
            'email' => 'Email',
            'currentPassword' => 'Current Password',
            'newPassword' => 'New Password',
            'newPasswordRepeat' => 'Repeat New Password',
        ];
    }

    /**
     * Inicjalizuje formularz i ustawia aktualnie zalogowanego użytkownika.
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->_user = Yii::$app->user->identity;
    }

    /**
     * Weryfikuje, czy podane aktualne hasło jest poprawne.
     */
    public function validateCurrentPassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if (!$this->_user || !$this->_user->validatePassword($this->currentPassword)) {
                $this->addError($attribute, 'Incorrect current password.');
            }
        }
    }

    /**
     * Sprawdza, czy nowy login nie jest zajęty przez innego użytkownika.
     */
    public function validateUsernameUnique($attribute, $params)
    {
        $existing = User::find()->where(['username' => $this->username])->andWhere(['<>', 'id', $this->_user->id])->one();
        if ($existing) {
            $this->addError($attribute, 'This username has already been taken.');
        }
    }

    /**
     * Sprawdza, czy nowy e-mail nie jest zajęty przez innego użytkownika.
     */
    public function validateEmailUnique($attribute, $params)
    {
        $existing = User::find()->where(['email' => $this->email])->andWhere(['<>', 'id', $this->_user->id])->one();
        if ($existing) {
            $this->addError($attribute, 'This email has already been taken.');
        }
    }

    /**
     * Blokuje ustawienie nowego hasła zawierającego login.
     */
    public function validateNewPasswordDoesNotContainUsername($attribute, $params)
    {
        if ($this->$attribute && strpos($this->$attribute, $this->username) !== false) {
            $this->addError($attribute, 'Password cannot contain your username.');
        }
    }

    /**
     * Zapisuje zmiany danych użytkownika po pełnej walidacji formularza.
     */
    public function update()
    {
        if (!$this->validate()) {
            return false;
        }
        $user = $this->_user;
        $user->username = $this->username;
        $user->email = $this->email;
        if ($this->newPassword !== null && $this->newPassword !== '') {
            $user->setPassword($this->newPassword);
            $user->generateAuthKey();
        }
        return $user->save();
    }
}

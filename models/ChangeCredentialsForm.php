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
                'message' => 'Dozwolone są tylko litery, cyfry, myślniki i podkreślenia.'],
            ['username', 'validateUsernameUnique'],

            ['currentPassword', 'validateCurrentPassword'],

            ['newPassword', 'string', 'min' => 6, 'skipOnEmpty' => true],
            ['newPassword', 'match', 'pattern' => '/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/',
                'message' => 'Hasło musi zawierać co najmniej jedną wielką literę, jedną cyfrę i jeden znak specjalny.',
                'skipOnEmpty' => true],
            ['newPassword', 'validateNewPasswordDoesNotContainUsername'],
            ['newPasswordRepeat', 'compare', 'compareAttribute' => 'newPassword',
                'message' => 'Hasła nie są takie same.',
                'skipOnEmpty' => true],
        ];
    }

    /**
     * Etykiety pól formularza.
     */
    public function attributeLabels()
    {
        return [
            'email' => 'E-mail',
            'currentPassword' => 'Aktualne hasło',
            'newPassword' => 'Nowe hasło',
            'newPasswordRepeat' => 'Powtórz nowe hasło',
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
                $this->addError($attribute, 'Aktualne hasło jest nieprawidłowe.');
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
            $this->addError($attribute, 'Ta nazwa użytkownika jest już zajęta.');
        }
    }

    /**
     * Sprawdza, czy nowy e-mail nie jest zajęty przez innego użytkownika.
     */
    public function validateEmailUnique($attribute, $params)
    {
        $existing = User::find()->where(['email' => $this->email])->andWhere(['<>', 'id', $this->_user->id])->one();
        if ($existing) {
            $this->addError($attribute, 'Ten adres e-mail jest już zajęty.');
        }
    }

    /**
     * Blokuje ustawienie nowego hasła zawierającego login.
     */
    public function validateNewPasswordDoesNotContainUsername($attribute, $params)
    {
        if ($this->$attribute && strpos($this->$attribute, $this->username) !== false) {
            $this->addError($attribute, 'Hasło nie może zawierać nazwy użytkownika.');
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

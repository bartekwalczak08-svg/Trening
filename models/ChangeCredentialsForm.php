<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * Form used by users to update their username/password.
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

    public function rules()
    {
        return [
            [['username', 'email', 'currentPassword'], 'required'],
            ['username', 'string', 'min' => 3, 'max' => 255],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'validateEmailUnique'],
            ['username', 'match', 'pattern' => '/^[a-zA-Z0-9_-]+$/',
                'message' => 'Only letters, numbers, dashes and underscores are allowed.'],
            ['username', 'validateUsernameUnique'],

            ['currentPassword', 'validateCurrentPassword'],

            ['newPassword', 'string', 'min' => 6, 'skipOnEmpty' => true],
            ['newPasswordRepeat', 'compare', 'compareAttribute' => 'newPassword',
                'message' => 'Passwords do not match.',
                'skipOnEmpty' => true],
        ];
    }

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
     * @inheritdoc
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->_user = Yii::$app->user->identity;
    }

    /**
     * Checks that the provided currentPassword matches the user's password.
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
     * Validates that username is not already taken by another user.
     */
    public function validateUsernameUnique($attribute, $params)
    {
        $existing = User::find()->where(['username' => $this->username])->andWhere(['<>', 'id', $this->_user->id])->one();
        if ($existing) {
            $this->addError($attribute, 'This username has already been taken.');
        }
    }

    public function validateEmailUnique($attribute, $params)
    {
        $existing = User::find()->where(['email' => $this->email])->andWhere(['<>', 'id', $this->_user->id])->one();
        if ($existing) {
            $this->addError($attribute, 'This email has already been taken.');
        }
    }

    /**
     * Applies changes to the current user and saves.
     * @return bool whether save was successful
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

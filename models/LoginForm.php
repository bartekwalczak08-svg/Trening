<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property-read User|null $user
 *
 */
class LoginForm extends Model
{
    public $username; // username or email
    public $password;
    public $rememberMe = true;

    private $_user = false;


    /**
     * @return array the validation rules.
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
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    /**
     * Custom validator for the login field. It accepts either a valid
     * username (alphanumeric, underscores, dashes) or a properly formatted email.
     */
    public function validateLogin($attribute, $params)
    {
        if (strpos($this->$attribute, '@') !== false) {
            if (!filter_var($this->$attribute, FILTER_VALIDATE_EMAIL)) {
                $this->addError($attribute, 'Invalid email address.');
            }
        } else {
            if (!preg_match('/^[a-zA-Z0-9_-]+$/', $this->$attribute)) {
                $this->addError($attribute, 'Only letters, numbers, dashes and underscores are allowed.');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'username' => 'Username or Email',
        ];
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            // allow login by username or email
            $this->_user = User::find()
                ->where(['username' => $this->username])
                ->orWhere(['email' => $this->username])
                ->one();
        }

        return $this->_user;
    }
}

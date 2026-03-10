<?php

/**
 * Opis: Rozszerzony model aplikacji z logik biznesow.
 */


namespace app\models\extended;

use Yii;
use yii\behaviors\TimestampBehavior;

// Klasa User.
class User extends \app\models\generated\User implements \yii\web\IdentityInterface
{
    /**
     * {@inheritdoc}
     */
    // Metoda behaviors.
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * Sets password hash from plain-text password.
     *
     * @param string $password
     */
    // Metoda setPassword.
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" cookie hash.
     */
    // Metoda generateAuthKey.
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * {@inheritdoc}
     */
    // Metoda findIdentity.
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * {@inheritdoc}
     */
    // Metoda findIdentityByAccessToken.
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    // Metoda findByUsername.
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    /**
     * {@inheritdoc}
     */
    // Metoda getId.
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    // Metoda getAuthKey.
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    // Metoda validateAuthKey.
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    // Metoda validatePassword.
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }
}

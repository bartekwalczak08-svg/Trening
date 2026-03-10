<?php

/**
 * Opis: Model wygenerowany automatycznie na podstawie schematu bazy danych.
 */


namespace app\models\generated;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $username
 * @property string $password_hash
 * @property string $auth_key
 * @property string|null $access_token
 * @property int $created_at
 * @property int $updated_at
 * @property string $email
 */
// Klasa User.
class User extends \app\models\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    // Metoda tableName.
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    // Metoda rules.
    public function rules()
    {
        return [
            [['access_token'], 'default', 'value' => null],
            [['username', 'password_hash', 'auth_key', 'created_at', 'updated_at', 'email'], 'required'],
            [['created_at', 'updated_at'], 'integer'],
            [['username', 'password_hash', 'access_token', 'email'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['username'], 'unique'],
            [['email'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    // Metoda attributeLabels.
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'password_hash' => 'Password Hash',
            'auth_key' => 'Auth Key',
            'access_token' => 'Access Token',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'email' => 'Email',
        ];
    }

}

<?php

/**
 * Auto-generated model based on database schema.
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
class GeneratedUser extends \app\models\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
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
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Nazwa użytkownika',
            'password_hash' => 'Hash hasła',
            'auth_key' => 'Klucz autoryzacji',
            'access_token' => 'Token dostępu',
            'created_at' => 'Data utworzenia',
            'updated_at' => 'Data aktualizacji',
            'email' => 'E-mail',
        ];
    }
}
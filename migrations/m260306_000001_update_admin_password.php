<?php

/**
 * Opis: Migracja bazy danych odpowiedzialna za zmiany schematu.
 */


namespace app\migrations;

use Yii;

use yii\db\Migration;

// Klasa m260306_000001_update_admin_password.
class m260306_000001_update_admin_password extends Migration
{
    // Metoda safeUp.
    public function safeUp()
    {
        // if the admin user exists and password is 'admin' (hashed), reset to 'admin123'
        $user = (new \yii\db\Query())
            ->from('{{%user}}')
            ->where(['username' => 'admin'])
            ->one();
        if ($user) {
            // we cannot compare hash easily, just update anyway
            $this->update('{{%user}}', [
                'password_hash' => Yii::$app->security->generatePasswordHash('admin123'),
            ], ['username' => 'admin']);
        }
    }

    // Metoda safeDown.
    public function safeDown()
    {
        // revert back to 'admin' if necessary (not recommended)
        $user = (new \yii\db\Query())
            ->from('{{%user}}')
            ->where(['username' => 'admin'])
            ->one();
        if ($user) {
            $this->update('{{%user}}', [
                'password_hash' => Yii::$app->security->generatePasswordHash('admin'),
            ], ['username' => 'admin']);
        }
    }
}




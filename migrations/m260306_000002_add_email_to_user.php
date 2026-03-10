<?php

/**
 * Opis: Migracja bazy danych odpowiedzialna za zmiany schematu.
 */


namespace app\migrations;

use yii\db\Migration;

// Klasa m260306_000002_add_email_to_user.
class m260306_000002_add_email_to_user extends Migration
{
    // Metoda safeUp.
    public function safeUp()
    {
        $schema = $this->db->schema->getTableSchema('{{%user}}', true);
        if ($schema !== null && $schema->getColumn('email') === null) {
            // add column allowing null temporarily so we can populate existing rows
            $this->addColumn('{{%user}}', 'email', $this->string()->unique());
            // give admin a default email if present
            $this->update('{{%user}}', ['email' => 'admin@example.com'], ['username' => 'admin']);
            // for any other rows without email fill with placeholder
            $this->execute("UPDATE {{%user}} SET email=CONCAT(username, '@example.com') WHERE email IS NULL");
            // now make column NOT NULL
            $this->alterColumn('{{%user}}', 'email', $this->string()->notNull()->unique());
        }
    }

    // Metoda safeDown.
    public function safeDown()
    {
        if ($this->db->schema->getTableSchema('{{%user}}', true) !== null) {
            $this->dropColumn('{{%user}}', 'email');
        }
    }
}




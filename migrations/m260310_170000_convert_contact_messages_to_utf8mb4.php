<?php

/**
 * Opis: Migracja bazy danych odpowiedzialna za zmiany schematu.
 */

namespace app\migrations;

use yii\db\Migration;

// Klasa m260310_170000_convert_contact_messages_to_utf8mb4.
class m260310_170000_convert_contact_messages_to_utf8mb4 extends Migration
{
    // Metoda safeUp.
    public function safeUp()
    {
        if ($this->db->driverName !== 'mysql') {
            return;
        }

        $table = $this->db->schema->getTableSchema('{{%contact_messages}}', true);
        if ($table === null) {
            return;
        }

        $this->execute('ALTER TABLE {{%contact_messages}} CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    }

    // Metoda safeDown.
    public function safeDown()
    {
        if ($this->db->driverName !== 'mysql') {
            return;
        }

        $table = $this->db->schema->getTableSchema('{{%contact_messages}}', true);
        if ($table === null) {
            return;
        }

        $this->execute('ALTER TABLE {{%contact_messages}} CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci');
    }
}

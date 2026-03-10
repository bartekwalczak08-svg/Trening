<?php

/**
 * Opis: Migracja bazy danych odpowiedzialna za zmiany schematu.
 */

namespace app\migrations;

use yii\db\Migration;

// Klasa m260310_160000_add_deleted_at_to_contact_messages.
class m260310_160000_add_deleted_at_to_contact_messages extends Migration
{
    // Metoda safeUp.
    public function safeUp()
    {
        $table = $this->db->schema->getTableSchema('{{%contact_messages}}', true);
        if ($table === null) {
            return;
        }

        if (!isset($table->columns['deleted_at'])) {
            $this->addColumn('{{%contact_messages}}', 'deleted_at', $this->integer()->null()->after('updated_at'));
            $this->createIndex('idx-contact_messages-deleted_at', '{{%contact_messages}}', 'deleted_at');
        }
    }

    // Metoda safeDown.
    public function safeDown()
    {
        $table = $this->db->schema->getTableSchema('{{%contact_messages}}', true);
        if ($table === null) {
            return;
        }

        if (isset($table->columns['deleted_at'])) {
            $this->dropIndex('idx-contact_messages-deleted_at', '{{%contact_messages}}');
            $this->dropColumn('{{%contact_messages}}', 'deleted_at');
        }
    }
}

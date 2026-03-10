<?php

/**
 * Opis: Migracja bazy danych odpowiedzialna za zmiany schematu.
 */

namespace app\migrations;

use yii\db\Migration;

// Klasa m260310_150000_create_contact_messages_table.
class m260310_150000_create_contact_messages_table extends Migration
{
    // Metoda safeUp.
    public function safeUp()
    {
        // Idempotent guard for repeated migration runs.
        if ($this->db->schema->getTableSchema('{{%contact_messages}}', true) !== null) {
            return;
        }

        $this->createTable('{{%contact_messages}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'email' => $this->string(255)->notNull(),
            'subject' => $this->string(255)->notNull(),
            'body' => $this->text()->notNull(),
            'status' => $this->string(32)->notNull()->defaultValue('new'),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        // Indexes for faster filtering/sorting in message list.
        $this->createIndex('idx-contact_messages-status', '{{%contact_messages}}', 'status');
        $this->createIndex('idx-contact_messages-created_at', '{{%contact_messages}}', 'created_at');
    }

    // Metoda safeDown.
    public function safeDown()
    {
        // Safe rollback when table is already absent.
        if ($this->db->schema->getTableSchema('{{%contact_messages}}', true) === null) {
            return;
        }

        $this->dropIndex('idx-contact_messages-created_at', '{{%contact_messages}}');
        $this->dropIndex('idx-contact_messages-status', '{{%contact_messages}}');
        $this->dropTable('{{%contact_messages}}');
    }
}

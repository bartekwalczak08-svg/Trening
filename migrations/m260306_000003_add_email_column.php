<?php

use yii\db\Migration;

class m260306_000003_add_email_column extends Migration
{
    public function safeUp()
    {
        // add email column to user table
        if ($this->db->schema->getTableSchema('{{%user}}', true) !== null) {
            $schema = $this->db->schema->getTableSchema('{{%user}}');
            if (!isset($schema->columns['email'])) {
                $this->addColumn('{{%user}}', 'email', $this->string()->notNull()->defaultValue(''));
                $this->createIndex('idx-user-email', '{{%user}}', 'email', true);

                // set a sensible email for admin if exists
                $this->update('{{%user}}', ['email' => 'admin@example.com'], ['id' => 100]);
            }
        }
    }

    public function safeDown()
    {
        if ($this->db->schema->getTableSchema('{{%user}}', true) !== null) {
            $this->dropIndex('idx-user-email', '{{%user}}');
            $this->dropColumn('{{%user}}', 'email');
        }
    }
}

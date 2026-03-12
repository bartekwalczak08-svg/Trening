<?php

/**
 * Adds account lifecycle columns to user table.
 */

namespace app\migrations;

use yii\db\Migration;

class m260312_100000_add_account_status_to_user extends Migration
{
    public function safeUp()
    {
        $table = $this->db->schema->getTableSchema('{{%user}}', true);
        if ($table === null) {
            return;
        }

        if (!isset($table->columns['status'])) {
            $this->addColumn('{{%user}}', 'status', $this->string(32)->notNull()->defaultValue('active')->after('access_token'));
        }

        if (!isset($table->columns['delete_requested_at'])) {
            $this->addColumn('{{%user}}', 'delete_requested_at', $this->integer()->null()->after('status'));
        }

        $this->createIndex('idx_user_status', '{{%user}}', 'status');
        $this->createIndex('idx_user_delete_requested_at', '{{%user}}', 'delete_requested_at');

        $this->update('{{%user}}', ['status' => 'active'], ['or', ['status' => null], ['status' => '']]);
    }

    public function safeDown()
    {
        $table = $this->db->schema->getTableSchema('{{%user}}', true);
        if ($table === null) {
            return;
        }

        if (isset($table->columns['delete_requested_at'])) {
            $this->dropIndex('idx_user_delete_requested_at', '{{%user}}');
            $this->dropColumn('{{%user}}', 'delete_requested_at');
        }

        if (isset($table->columns['status'])) {
            $this->dropIndex('idx_user_status', '{{%user}}');
            $this->dropColumn('{{%user}}', 'status');
        }
    }
}

<?php

/**
 * Adds deactivation timestamp to user table.
 */

namespace app\migrations;

use yii\db\Migration;

class m260312_120000_add_deactivated_at_to_user extends Migration
{
    public function safeUp()
    {
        $table = $this->db->schema->getTableSchema('{{%user}}', true);
        if ($table === null) {
            return;
        }

        if (!isset($table->columns['deactivated_at'])) {
            $this->addColumn('{{%user}}', 'deactivated_at', $this->integer()->null()->after('status'));
            $this->createIndex('idx_user_deactivated_at', '{{%user}}', 'deactivated_at');
        }
    }

    public function safeDown()
    {
        $table = $this->db->schema->getTableSchema('{{%user}}', true);
        if ($table === null || !isset($table->columns['deactivated_at'])) {
            return;
        }

        $this->dropIndex('idx_user_deactivated_at', '{{%user}}');
        $this->dropColumn('{{%user}}', 'deactivated_at');
    }
}

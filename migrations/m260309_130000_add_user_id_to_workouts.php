<?php

use yii\db\Migration;
use yii\db\Query;

class m260309_130000_add_user_id_to_workouts extends Migration
{
    public function safeUp()
    {
        $workoutsTable = $this->db->schema->getTableSchema('{{%workouts}}', true);
        $usersTable = $this->db->schema->getTableSchema('{{%user}}', true);

        if ($workoutsTable === null || $usersTable === null) {
            return;
        }

        if (!isset($workoutsTable->columns['user_id'])) {
            $this->addColumn('{{%workouts}}', 'user_id', $this->integer()->null()->after('id'));
            $this->createIndex('idx-workouts-user_id', '{{%workouts}}', 'user_id');
            $this->addForeignKey(
                'fk_workouts_user_id',
                '{{%workouts}}',
                'user_id',
                '{{%user}}',
                'id',
                'CASCADE',
                'CASCADE'
            );
        }

        $ownerId = (new Query())
            ->from('{{%user}}')
            ->select('id')
            ->orderBy(['id' => SORT_ASC])
            ->scalar();

        if ($ownerId !== false && $ownerId !== null) {
            $this->update('{{%workouts}}', ['user_id' => (int) $ownerId], ['user_id' => null]);
        }
    }

    public function safeDown()
    {
        $workoutsTable = $this->db->schema->getTableSchema('{{%workouts}}', true);
        if ($workoutsTable === null || !isset($workoutsTable->columns['user_id'])) {
            return;
        }

        $this->dropForeignKey('fk_workouts_user_id', '{{%workouts}}');
        $this->dropIndex('idx-workouts-user_id', '{{%workouts}}');
        $this->dropColumn('{{%workouts}}', 'user_id');
    }
}

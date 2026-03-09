<?php

use yii\db\Migration;

class m260309_120000_add_duration_unit_to_workout_exercises extends Migration
{
    public function safeUp()
    {
        $table = $this->db->schema->getTableSchema('{{%workout_exercises}}', true);
        if ($table === null) {
            return;
        }

        if (!isset($table->columns['duration_unit'])) {
            $this->addColumn(
                '{{%workout_exercises}}',
                'duration_unit',
                $this->string(3)->notNull()->defaultValue('sec')->after('duration_sec')
            );

            // Existing rows were stored in seconds, so mark them as seconds.
            $this->update('{{%workout_exercises}}', ['duration_unit' => 'sec']);
        }
    }

    public function safeDown()
    {
        $table = $this->db->schema->getTableSchema('{{%workout_exercises}}', true);
        if ($table !== null && isset($table->columns['duration_unit'])) {
            $this->dropColumn('{{%workout_exercises}}', 'duration_unit');
        }
    }
}

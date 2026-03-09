<?php

use yii\db\Migration;

/**
 * Class m260307_000001_update_reps_to_varchar
 */
class m260307_000001_update_reps_to_varchar extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%workout_exercises}}', 'reps', $this->string(50)->defaultValue(null));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('{{%workout_exercises}}', 'reps', $this->integer()->defaultValue(null));
    }
}

<?php

/**
 * Opis: Migracja bazy danych odpowiedzialna za zmiany schematu.
 */


namespace app\migrations;

use yii\db\Migration;

/**
 * Class m260307_000001_update_reps_to_varchar
 */
// Klasa m260307_000001_update_reps_to_varchar.
class m260307_000001_update_reps_to_varchar extends Migration
{
    /**
     * {@inheritdoc}
     */
    // Metoda safeUp.
    public function safeUp()
    {
        $this->alterColumn('{{%workout_exercises}}', 'reps', $this->string(50)->defaultValue(null));
    }

    /**
     * {@inheritdoc}
     */
    // Metoda safeDown.
    public function safeDown()
    {
        $this->alterColumn('{{%workout_exercises}}', 'reps', $this->integer()->defaultValue(null));
    }
}




<?php

/**
 * Opis: Migracja bazy danych odpowiedzialna za zmiany schematu.
 */


namespace app\migrations;

use yii\db\Migration;

// Klasa m260310_130000_add_completion_flags_to_workouts.
class m260310_130000_add_completion_flags_to_workouts extends Migration
{
    // Metoda safeUp.
    public function safeUp()
    {
        $workoutsTable = $this->db->schema->getTableSchema('{{%workouts}}', true);
        if ($workoutsTable !== null && !isset($workoutsTable->columns['is_completed'])) {
            $this->addColumn('{{%workouts}}', 'is_completed', $this->boolean()->notNull()->defaultValue(false)->after('weekday'));
        }

        $workoutExercisesTable = $this->db->schema->getTableSchema('{{%workout_exercises}}', true);
        if ($workoutExercisesTable !== null && !isset($workoutExercisesTable->columns['is_completed'])) {
            $this->addColumn('{{%workout_exercises}}', 'is_completed', $this->boolean()->notNull()->defaultValue(false)->after('position'));
        }
    }

    // Metoda safeDown.
    public function safeDown()
    {
        $workoutExercisesTable = $this->db->schema->getTableSchema('{{%workout_exercises}}', true);
        if ($workoutExercisesTable !== null && isset($workoutExercisesTable->columns['is_completed'])) {
            $this->dropColumn('{{%workout_exercises}}', 'is_completed');
        }

        $workoutsTable = $this->db->schema->getTableSchema('{{%workouts}}', true);
        if ($workoutsTable !== null && isset($workoutsTable->columns['is_completed'])) {
            $this->dropColumn('{{%workouts}}', 'is_completed');
        }
    }
}

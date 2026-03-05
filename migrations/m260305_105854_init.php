<?php

use yii\db\Migration;

class m260305_105854_init extends Migration
{
    public function safeUp()
    {
        // Tabela treningów
        if ($this->db->schema->getTableSchema('{{%workouts}}', true) === null) {
            $this->createTable('{{%workouts}}', [
                'id' => $this->primaryKey(),
                'name' => $this->string(255)->notNull(),
                'description' => $this->text()->defaultValue(null),
                'created_at' => $this->integer()->notNull(),
                'updated_at' => $this->integer()->notNull(),
            ]);
        }

        // Tabela powiązań ćwiczenia -> trening
        if ($this->db->schema->getTableSchema('{{%workout_exercises}}', true) === null) {
            $this->createTable('{{%workout_exercises}}', [
                'id' => $this->primaryKey(),
                'workout_id' => $this->integer()->notNull(),
                'exercise_id' => $this->integer()->notNull(),
                'sets' => $this->integer()->defaultValue(null),
                'reps' => $this->integer()->defaultValue(null),
                'duration_sec' => $this->integer()->defaultValue(null),
                'rest_sec' => $this->integer()->defaultValue(60),
                'position' => $this->integer()->defaultValue(0),
            ]);

            // Indeksy
            $this->createIndex('idx-workout_exercises-workout_id', '{{%workout_exercises}}', 'workout_id');
            $this->createIndex('idx-workout_exercises-exercise_id', '{{%workout_exercises}}', 'exercise_id');

            // Klucze obce
            $this->addForeignKey(
                'fk_workout_exercises_workout',
                '{{%workout_exercises}}',
                'workout_id',
                '{{%workouts}}',
                'id',
                'CASCADE'
            );
            $this->addForeignKey(
                'fk_workout_exercises_exercise',
                '{{%workout_exercises}}',
                'exercise_id',
                '{{%exercises}}',
                'id',
                'CASCADE'
            );
        }
    }

    public function safeDown()
    {
        if ($this->db->schema->getTableSchema('{{%workout_exercises}}', true) !== null) {
            $this->dropForeignKey('fk_workout_exercises_workout', '{{%workout_exercises}}');
            $this->dropForeignKey('fk_workout_exercises_exercise', '{{%workout_exercises}}');
            $this->dropIndex('idx-workout_exercises-workout_id', '{{%workout_exercises}}');
            $this->dropIndex('idx-workout_exercises-exercise_id', '{{%workout_exercises}}');
            $this->dropTable('{{%workout_exercises}}');
        }

        if ($this->db->schema->getTableSchema('{{%workouts}}', true) !== null) {
            $this->dropTable('{{%workouts}}');
        }
    }
}
<?php

/**
 * Opis: Migracja bazy danych odpowiedzialna za zmiany schematu.
 */


namespace app\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%exercises}}`.
 */
// Klasa m260305_113103_create_exercises_table.
class m260305_113103_create_exercises_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    // Metoda safeUp.
    public function safeUp()
    {
        if ($this->db->schema->getTableSchema('{{%exercises}}', true) === null) {
            $this->createTable('{{%exercises}}', [
                'id' => $this->primaryKey(),
                'name' => $this->string(255)->notNull(),
                'type' => $this->string(50)->notNull(), // 
                'sets' => $this->integer()->defaultValue(null),
                'reps' => $this->integer()->defaultValue(null),
                'duration_sec' => $this->integer()->defaultValue(null), 
                'rest_sec' => $this->integer()->defaultValue(60),
                'created_at' => $this->integer()->notNull(),
                'updated_at' => $this->integer()->notNull(),
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    // Metoda safeDown.
    public function safeDown()
    {
        if ($this->db->schema->getTableSchema('{{%exercises}}', true) !== null) {
            $this->dropTable('{{%exercises}}');
        }
    }
}




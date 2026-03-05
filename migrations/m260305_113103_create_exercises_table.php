<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%exercises}}`.
 */
class m260305_113103_create_exercises_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
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

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
         $this->dropTable('{{%exercises}}');
    }
}

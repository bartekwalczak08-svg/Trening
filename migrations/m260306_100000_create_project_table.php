<?php

use yii\db\Migration;

class m260306_100000_create_project_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        if ($this->db->schema->getTableSchema('{{%project}}', true) === null) {
            $this->createTable('{{%project}}', [
                'id' => $this->primaryKey(),
                'title' => $this->string()->notNull(),
                'description' => $this->text(),
                'technologies' => $this->string(),
                'image_url' => $this->string(),
                'link' => $this->string(),
                'created_at' => $this->integer()->notNull(),
                'updated_at' => $this->integer()->notNull(),
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        if ($this->db->schema->getTableSchema('{{%project}}', true) !== null) {
            $this->dropTable('{{%project}}');
        }

       
    }
}


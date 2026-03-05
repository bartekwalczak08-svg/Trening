<?php

use yii\db\Migration;

class m260305_094647_init extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
$this->createTable('user', [
            'id' => $this->primaryKey(),
            'username' => $this->string()->notNull(),
            'email' => $this->string()->notNull(),
            'password_hash' => $this->string()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('user');

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260305_094647_tren cannot be reverted.\n";

        return false;
    }
    */
}

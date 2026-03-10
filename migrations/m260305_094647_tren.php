<?php

/**
 * Opis: Migracja bazy danych odpowiedzialna za zmiany schematu.
 */


namespace app\migrations;

use yii\db\Migration;

// Klasa m260305_094647_tren.
class m260305_094647_tren extends Migration
{
    /**
     * {@inheritdoc}
     */
    // Metoda safeUp.
    public function safeUp()
    {
        if ($this->db->schema->getTableSchema('{{%user}}', true) === null) {
            $this->createTable('{{%user}}', [
                'id' => $this->primaryKey(),
                'username' => $this->string()->notNull(),
                'email' => $this->string()->notNull(),
                'password_hash' => $this->string()->notNull(),
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
        if ($this->db->schema->getTableSchema('{{%user}}', true) !== null) {
            $this->dropTable('{{%user}}');
        }

   
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




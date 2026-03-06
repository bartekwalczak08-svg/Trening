<?php

use yii\db\Migration;

class m260306_000002_add_email_to_user extends Migration
{
    public function safeUp()
    {
        if ($this->db->schema->getTableSchema('{{%user}}', true) !== null) {
            // add column allowing null temporarily so we can populate existing rows
            $this->addColumn('{{%user}}', 'email', $this->string()->unique());
            // give admin a default email if present
            $this->update('{{%user}}', ['email' => 'admin@example.com'], ['username' => 'admin']);
            // for any other rows without email fill with placeholder
            $this->execute("UPDATE {{%user}} SET email=CONCAT(username, '@example.com') WHERE email IS NULL");
            // now make column NOT NULL
            $this->alterColumn('{{%user}}', 'email', $this->string()->notNull()->unique());
        }
    }

    public function safeDown()
    {
        if ($this->db->schema->getTableSchema('{{%user}}', true) !== null) {
            $this->dropColumn('{{%user}}', 'email');
        }
    }
}

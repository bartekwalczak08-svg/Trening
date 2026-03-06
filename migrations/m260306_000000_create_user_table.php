<?php
use yii\db\Migration;

class m260306_000000_create_user_table extends Migration
{
    public function safeUp()
    {
        if ($this->db->schema->getTableSchema('{{%user}}', true) === null) {
            $this->createTable('{{%user}}', ['id'=>$this->primaryKey(),'username'=>$this->string()->notNull()->unique(),'email'=>$this->string()->notNull()->unique(),'password_hash'=>$this->string()->notNull(),'auth_key'=>$this->string(32)->notNull(),'access_token'=>$this->string(255)->null(),'created_at'=>$this->integer()->notNull(),'updated_at'=>$this->integer()->notNull()]);
            $this->insert('{{%user}}',['id'=>100,'username'=>'admin','email'=>'admin@example.com','password_hash'=>Yii::$app->security->generatePasswordHash('admin123'),'auth_key'=>Yii::$app->security->generateRandomString(),'access_token'=>null,'created_at'=>time(),'updated_at'=>time()]);
        }
    }

    public function safeDown()
    {
        if ($this->db->schema->getTableSchema('{{%user}}', true) !== null) {
            $this->dropTable('{{%user}}');
        }
    }
}

<?php

namespace ant\token\migrations\db;

use yii\db\Migration;

class M170427055856_create_token extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%token}}', [
            'id' => $this->primaryKey()->unsigned(),
            'type' => $this->string(255)->notNull(),
            'token' => $this->string(40)->notNull(),
            'expire_at' => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->defaultValue(NULL),
            'updated_at' => $this->timestamp()->defaultValue(NULL),
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%token}}');
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}

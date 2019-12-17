<?php

namespace ant\token\migrations\db;

use yii\db\Migration;

/**
 * Class M190328092211_alter_token
 */
class M190328092211_alter_token extends Migration
{
	protected $tableName = '{{%token}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->renameColumn($this->tableName, 'expire_at', 'expire_at_old');
		$this->addColumn($this->tableName, 'expire_at', $this->timestamp()->null());
		
		$this->update($this->tableName, [
			'expire_at' => new \yii\db\Expression('FROM_UNIXTIME(expire_at_old)'),
		]);
		
		$this->dropColumn($this->tableName, 'expire_at_old');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->addColumn($this->tableName, 'expire_at_old', $this->integer()->notNull());
		
		$this->update($this->tableName, [
			'expire_at_old' => new \yii\db\Expression('UNIX_TIMESTAMP(expire_at)'),
		]);
		
		$this->dropColumn($this->tableName, 'expire_at');
		$this->renameColumn($this->tableName, 'expire_at_old', 'expire_at');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M190328092211_alter_token cannot be reverted.\n";

        return false;
    }
    */
}

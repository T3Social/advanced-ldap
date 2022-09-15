<?php

use yii\db\Migration;

/**
 * Class m210927_122602_description
 */
class m210927_122602_description extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('enterprise_ldap_space', 'description', $this->text()->null());
        $this->addColumn('enterprise_ldap_group', 'description', $this->text()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210927_122602_description cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210927_122602_description cannot be reverted.\n";

        return false;
    }
    */
}

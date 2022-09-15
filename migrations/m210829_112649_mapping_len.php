<?php

use yii\db\Migration;

/**
 * Class m210829_112649_mapping_len
 */
class m210829_112649_mapping_len extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('enterprise_ldap_space', 'dn', $this->text());
        $this->alterColumn('enterprise_ldap_group', 'dn', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210829_112649_mapping_len cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210829_112649_mapping_len cannot be reverted.\n";

        return false;
    }
    */
}

<?php

use yii\db\Migration;
use yii\db\Expression;
/**
 * Class m200731_153242_authclientids
 */
class m200731_153242_authclientids extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        try {

            $this->addColumn('space_membership', 'authclient_id', $this->string(20)->defaultValue(new Expression('NULL')));
            $this->addColumn('group_user', 'authclient_id', $this->string(20)->defaultValue(new Expression('NULL')));

            $this->update('space_membership', ['authclient_id' => 'ldap'], ['added_by_ldap' => 1]);
        } catch (\Exception $ex) {

        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200731_153242_authclientids cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200731_153242_authclientids cannot be reverted.\n";

        return false;
    }
    */
}

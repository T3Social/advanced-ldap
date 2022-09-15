<?php

use yii\db\Expression;
use yii\db\Migration;
use yii\db\Schema;

/**
 * Class m191113_153409_initial
 */
class m191113_153409_initial extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        try {
            $this->createTable('enterprise_ldap_space', [
                'id' => $this->primaryKey(),
                'space_id' => $this->integer()->notNull(),
                'dn' => $this->string(255)->notNull()
            ]);

            $this->createTable('enterprise_ldap_group', [
                'id' => $this->primaryKey(),
                'group_id' => $this->integer()->notNull(),
                'dn' => $this->string(255)->notNull()
            ]);

            $this->addForeignKey('fk-ldapgroup-group', 'enterprise_ldap_group', 'group_id', 'group', 'id', 'CASCADE');
            $this->addForeignKey('fk-ldapspace-space', 'enterprise_ldap_space', 'space_id', '`space`', 'id', 'CASCADE');

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
        echo "m191113_153409_initial cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191113_153409_initial cannot be reverted.\n";

        return false;
    }
    */
}

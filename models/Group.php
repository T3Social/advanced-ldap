<?php

namespace humhub\modules\advancedLdap\models;

use humhub\modules\user\models\Group as BaseGroup;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "enterprise_ldap_group".
 *
 * @property integer $id
 * @property integer $group_id
 * @property string $dn
 * @property string $description
 */
class Group extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'enterprise_ldap_group';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['group_id', 'dn'], 'required'],
            [['group_id'], 'integer'],
            [['dn', 'description'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('AdvancedLdapModule.base', 'ID'),
            'group_id' => Yii::t('AdvancedLdapModule.base', 'Group'),
            'dn' => Yii::t('AdvancedLdapModule.base', 'LDAP Mapping'),
            'description' => Yii::t('AdvancedLdapModule.base', 'Description'),
        ];
    }

    public function getGroup()
    {
        return $this->hasOne(BaseGroup::class, ['id' => 'group_id']);
    }

}
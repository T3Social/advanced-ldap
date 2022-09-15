<?php

namespace humhub\modules\advancedLdap\models;

use humhub\modules\space\models\Space as SpaceModel;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "enterprise_ldap_space".
 *
 * @property integer $id
 * @property integer $space_id
 * @property string $dn
 * @property string $description
 *
 * @property SpaceModel $space
 */
class Space extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'enterprise_ldap_space';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['space_id', 'dn'], 'required'],
            [['space_id'], 'integer'],
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
            'space_id' => Yii::t('AdvancedLdapModule.base', 'Space ID'),
            'dn' => Yii::t('AdvancedLdapModule.base', 'LDAP Mapping'),
            'description' => Yii::t('AdvancedLdapModule.base', 'Description'),
        ];
    }

    public function getSpace()
    {
        return $this->hasOne(SpaceModel::class, ['id' => 'space_id']);
    }

}

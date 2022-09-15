<?php

use humhub\modules\advancedLdap\LdapHelper;
use yii\helpers\Url;
use yii\helpers\Html;
use humhub\widgets\GridView;
?>

<?php $this->beginContent('@admin/views/group/_manageLayout.php', ['group' => $group]) ?>
<div class="panel-body">
    <p />        
    <p class="pull-right">
        <?= Html::a(Yii::t('AdvancedLdapModule.base', "Create new mapping"), Url::to(['edit', 'groupId' => $group->id]), ['class' => 'btn btn-success', 'data-ui-loader' => '']); ?>
    </p>
    <div class="help-block">
        <?= Yii::t('AdvancedLdapModule.base', 'Assign LDAP users which becomes automatically member of this group.'); ?><br>
    </div>
    <br>
    <?php
    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-hover'],
        'columns' => [
            [
                'attribute' => 'dn',
                'label' => Yii::t('AdvancedLdapModule.base', 'Mapping'),
                'format' => 'raw',
                'value' => function ($model) {
                    if (!empty($model->description)) {
                        return '<span class="tt" aria-hidden="true" data-toggle="tooltip" data-placement="right" title="' . Html::encode($model->description) . '">' .
                            Html::encode($model->dn) . ' <i class="fa fa-info-circle colorInfo"></i>' .
                            '</span>';
                    }
                    return Html::encode($model->dn);
                }
            ],
            [
                'label' => Yii::t('AdvancedLdapModule.base', 'Type'),
                'format' => 'raw',
                'value' => function ($model) {
                    switch (LdapHelper::getMappingType($model->dn)) {
                        case LdapHelper::MAPPING_QUERY:
                            return Yii::t('AdvancedLdapModule.base', 'Query') .
                                ' <span class="badge badge-primary">' . LdapHelper::getQueryStatus($model->dn) . '</span>';
                        case LdapHelper::MAPPING_ATTRIBUTE:
                            return Yii::t('AdvancedLdapModule.base', 'Attribute');
                        case LdapHelper::MAPPING_MEMBER_OF:
                            return Yii::t('AdvancedLdapModule.base', 'Member Of');
                        case LdapHelper::MAPPING_DN:
                            return Yii::t('AdvancedLdapModule.base', 'DN');
                    }
                }
            ],
            [
                'header' => Yii::t('AdvancedLdapModule.base', 'Actions'),
                'class' => 'yii\grid\ActionColumn',
                'options' => ['width' => '80px'],
                'buttons' => [
                    'update' => function($url, $model) {
                        return Html::a('<i class="fa fa-pencil"></i>', Url::to(['edit', 'id' => $model->id, 'groupId' => $model->group_id]), ['class' => 'btn btn-primary btn-xs tt']);
                    },
                    'view' => function() {
                        return;
                    },
                    'delete' => function($url, $model) {
                        return Html::a('<i class="fa fa-remove"></i>', Url::to(['delete', 'id' => $model->id, 'groupId' => $model->group_id]), ['class' => 'btn btn-danger btn-xs tt']);
                    },
                ],
            ],
    ]]);
    ?>        
</div>
<?php $this->endContent(); ?>
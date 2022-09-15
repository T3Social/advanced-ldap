<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use humhub\modules\space\modules\manage\widgets\MemberMenu;
?>

<br />
<div class="panel panel-default">
    <?= MemberMenu::widget(['space' => $space]); ?>
    <div class="panel-heading">
        <?php if (!$model->isNewRecord) : ?>
            <?= Yii::t('AdvancedLdapModule.base', '<strong>Edit</strong> LDAP mapping'); ?>
        <?php else: ?>
            <?= Yii::t('AdvancedLdapModule.base', '<strong>Create</strong> new LDAP mapping'); ?>
        <?php endif; ?>
    </div>
    <div class="panel-body">
        <p class="pull-right">
            <?= Html::a(Yii::t('AdvancedLdapModule.base', "Back to overview"), $space->createUrl('index'), ['class' => 'btn btn-default', 'data-ui-loader' => true]); ?>
        </p>
        <?= $this->render('@advanced-ldap/views/_mappinghelp.php'); ?>
        <br />
        <?php $form = ActiveForm::begin([]) ?>
        <?= $form->field($model, 'dn')->textarea(['rows' => 3]) ?>
        <?= $form->field($model, 'description')->textarea(['rows' => 1]) ?>

        <br />
        <?= Html::submitButton(Yii::t('base', 'Save'), ['class' => 'btn btn-success', 'data-ui-loader' => true]) ?>

        <?php if (!$model->isNewRecord): ?>
            <?= Html::a(Yii::t('base', 'Delete'), $space->createUrl('delete', ['id' => $model->id]), array('class' => 'btn btn-danger pull-right', 'data-ui-loader' => true, 'data-method' => 'POST')); ?>
        <?php endif; ?>

        <?php ActiveForm::end() ?>

    </div>
</div>
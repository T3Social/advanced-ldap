<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
?>

<?php $this->beginContent('@admin/views/group/_manageLayout.php', ['group' => $group]) ?>
<div class="panel-body">
    <?php if (!$model->isNewRecord) : ?>
        <h1><?php echo Yii::t('AdvancedLdapModule.base', '<strong>Edit</strong> LDAP mapping'); ?></h1>
    <?php else: ?>
        <h1><?php echo Yii::t('AdvancedLdapModule.base', '<strong>Create</strong> new LDAP mapping'); ?></h1>
    <?php endif; ?>
    <br />
    <?= $this->render('@advanced-ldap/views/_mappinghelp.php'); ?>

    <br />

    <?php $form = ActiveForm::begin([]) ?>
    <?= $form->field($model, 'dn')->textarea(['rows' => 3]) ?>
    <?= $form->field($model, 'description')->textarea(['rows' => 1]) ?>

    <br />
    
    <?= Html::submitButton(Yii::t('base', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => '']) ?>

    <?php if (!$model->isNewRecord): ?>
        <?= Html::a(Yii::t('base', 'Delete'), Url::to(['delete', 'id' => $model->id]), array('class' => 'btn btn-danger', 'data-method' => 'POST')); ?>
    <?php endif; ?>

    <?php ActiveForm::end() ?>

</div>
<?php $this->endContent(); ?>
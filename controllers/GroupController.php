<?php

namespace humhub\modules\advancedLdap\controllers;

use humhub\modules\user\models\Group;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use humhub\modules\admin\components\Controller;
use yii\web\HttpException;

/**
 * Description of GroupController
 *
 * @author luke
 */
class GroupController extends Controller
{

    /**
     * @var Group
     */
    public $group;

    /**
     * {@inheritDoc}
     * @throws HttpException
     */
    public function init()
    {
        parent::init();

        $this->group = Group::findOne(['id' => (int) Yii::$app->request->get('groupId')]);

        if ($this->group === null) {
            throw new HttpException(404, 'Could not load group!');
        }

        $this->subLayout = '@admin/views/layouts/user';
    }

    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => \humhub\modules\advancedLdap\models\Group::find()->where(['group_id' => $this->group->id]),
            'pagination' => ['pageSize' => 50],
        ]);


        $groups = ArrayHelper::map(Group::find()->all(), 'id', 'name');
        return $this->render('index', [
                    'dataProvider' => $dataProvider,
                    'groups' => $groups,
                    'group' => $this->group
        ]);
    }

    public function actionEdit()
    {
        $id = Yii::$app->request->get('id');

        $model = null;
        if ($id != '') {
            $model = \humhub\modules\advancedLdap\models\Group::findOne(['id' => $id]);
        }

        if ($model === null) {
            $model = new \humhub\modules\advancedLdap\models\Group;
        }

        $model->group_id = $this->group->id;

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect(Url::to(['index', 'groupId' => $this->group->id]));
        }

        $groups = ArrayHelper::map(Group::find()->all(), 'id', 'name');
        return $this->render('edit', [
                    'model' => $model,
                    'groups' => $groups,
                    'group' => $this->group,
        ]);
    }

    public function actionDelete()
    {
        $this->forcePostRequest();

        $id = Yii::$app->request->get('id');

        $model = \humhub\modules\advancedLdap\models\Group::findOne(['id' => $id]);

        if ($model !== null) {
            $model->delete();
        }

        $this->redirect(['index', 'groupId' => $this->group->id]);
    }

}

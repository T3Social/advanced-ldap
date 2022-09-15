<?php

namespace humhub\modules\advancedLdap\controllers;

use humhub\modules\advancedLdap\models\Space;
use Yii;
use yii\data\ActiveDataProvider;
use humhub\modules\space\modules\manage\components\Controller;
use yii\web\HttpException;

/**
 * Space Controller
 *
 * @author luke
 */
class SpaceController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!Yii::$app->user->isAdmin()) {
            throw new HttpException(400, 'Access denied!');
        }

        return parent::init();
    }

    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Space::find()->where(['space_id' => $this->contentContainer->id]),
            'pagination' => ['pageSize' => 50],
        ]);


        return $this->render('index', [
            'space' => $this->contentContainer,
            'dataProvider' => $dataProvider
        ]);
    }

    public function actionEdit()
    {
        $id = Yii::$app->request->get('id');

        $model = null;
        if ($id != '') {
            $model = Space::findOne([
                'id' => $id,
                'space_id' => $this->contentContainer->id
            ]);
        }

        if ($model === null) {
            $model = new Space;
            $model->space_id = $this->contentContainer->id;
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect($this->contentContainer->createUrl('index'));
        }

        return $this->render('edit', ['model' => $model, 'space' => $this->contentContainer]);
    }

    public function actionDelete()
    {
        $this->forcePostRequest();

        $id = Yii::$app->request->get('id');

        $model = Space::findOne([
            'id' => $id,
            'space_id' => $this->contentContainer->id
        ]);

        if ($model !== null) {
            $model->delete();
        }

        $this->redirect($this->contentContainer->createUrl('index'));
    }

}
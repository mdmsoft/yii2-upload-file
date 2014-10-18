<?php

namespace mdm\upload;

use Yii;
use yii\web\NotFoundHttpException;

/**
 * FileController
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class FileController extends \yii\web\Controller
{
    public $defaultAction = 'show';

    /**
     * Show file
     * @param type $id
     */
    public function actionShow($id)
    {
        $model = $this->findModel($id);
        $response = Yii::$app->getResponse();
        $response->format = \yii\web\Response::FORMAT_RAW;
        $response->getHeaders()->add('content-type', $model->type);
        return file_get_contents($model->filename);
    }

    /**
     * Show file
     * @param type $id
     */
    public function actionDownload($id)
    {
        $model = $this->findModel($id);
        $response = Yii::$app->getResponse();
        $response->format = \yii\web\Response::FORMAT_RAW;
        $response->setDownloadHeaders($model->name, $model->type, false, $model->size);
        return file_get_contents($model->filename);
    }

    /**
     * Get model
     * @param integer $id
     * @return FileModel
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = FileModel::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
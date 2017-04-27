<?php

namespace mdm\upload;

use Yii;
use yii\web\NotFoundHttpException;
use yii\imagine\Image;
use yii\helpers\FileHelper;

/**
 * Use to show or download uploaded file. Add configuration to your application
 * 
 * Then you can show your file in url `Url::to(['/image', 'id' => $file_id, 'width' => 50])`
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class ImageController extends \yii\web\Controller
{
    public $defaultAction = 'show';
    public $savePath = '@runtime/thumbnails';

    /**
     * Show file
     * @param integer $id
     */
    public function actionShow($id, $width = null, $height = null)
    {
        $model = $this->findModel($id);
        $response = Yii::$app->getResponse();
        if ($width === null && $height === null) {
            return $response->sendFile($model->filename, $model->name, [
                    'mimeType' => $model->type,
                    'fileSize' => $model->size,
                    'inline' => true
            ]);
        } else {
            $dir = '';
            if ($width !== null) {
                $dir .= 'w' . (int) $width;
            }
            if ($height !== null) {
                $dir .= 'h' . (int) $height;
            }
            $filename = sprintf('%s/%s/%x/%d_%s', Yii::getAlias($this->savePath), $dir, $id % 255, $id, $model->name);
            if (!file_exists($filename)) {
                FileHelper::createDirectory(dirname($filename));
                $image = Image::thumbnail($model->filename, $width, $height);
                $image->save($filename);
            }
            return $response->sendFile($filename, $model->name, [
                    'mimeType' => $model->type,
                    'inline' => true
            ]);
        }
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

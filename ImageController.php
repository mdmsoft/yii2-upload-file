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
    public function actionShow($id, $width = null, $height = null, $ratio = null)
    {
        $model = $this->findModel($id);
        $response = Yii::$app->getResponse();
        if ($width === null && $height === null && $ratio === null) {
            return $response->sendFile($model->filename, $model->name, [
                    'mimeType' => $model->type,
                    'fileSize' => $model->size,
                    'inline' => true
            ]);
        } elseif ($width !== null || $height !== null) {
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
        } else {
            $ratio = round($ratio, 2);
            list($w, $h) = getimagesize($model->filename);
            if ($w > $ratio * $h) {
                $w = $ratio * $h;
            } else {
                $h = $w / $ratio;
            }
            $filename = sprintf('%s/c%d/%x/%d_%s', Yii::getAlias($this->savePath), $ratio * 100, $id % 255, $id, $model->name);
            if (!file_exists($filename)) {
                FileHelper::createDirectory(dirname($filename));
                $image = Image::crop($model->filename, $w, $h);
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

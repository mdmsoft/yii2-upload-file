<?php

namespace mdm\upload;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;
use yii\validators\ImageValidator;
use yii\imagine\Image;

/**
 * Description of UploadImage
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class UploadImage
{

    /**
     * @param string $name
     * @param array $options
     * @return boolean
     */
    public static function store($name, $options = [])
    {
        $multiple = ArrayHelper::remove($options, 'multiple', false);
        $crop = ArrayHelper::remove($options, 'crop');
        $resize = ArrayHelper::remove($options, 'resize');
        $rules = ArrayHelper::remove($options, 'rules', []);

        $validator = new ImageValidator();
        if ($crop) {
            $options['saveCallback'] = function ($model) use($crop, $rules) {
                return static::cropImage($model, $crop, $rules);
            };
        } elseif ($resize) {
            $options['saveCallback'] = function ($model) use($resize) {
                return static::resizeImage($model, $resize);
            };
        } elseif (!empty($rules)) {
            Yii::configure($validator, $rules);
        }
        if ($multiple) {
            $files = UploadedFile::getInstancesByName($name);
            foreach ($files as $file) {
                if (!$validator->validate($file)) {
                    return false;
                }
            }
            $result = [];
            foreach ($files as $file) {
                if (false !== ($model = FileModel::saveAs($file, $options))) {
                    $result[] = $model->id;
                } else {
                    return false;
                }
            }
            return $result;
        } else {
            $file = UploadedFile::getInstanceByName($name);
            if ($validator->validate($file) && ($model = FileModel::saveAs($file, $options)) !== false) {
                return $model->id;
            }
        }
        return false;
    }

    /**
     *
     * @param FileModel $model
     * @param integer $width
     * @param integer $height
     */
    public static function resizeImage($model, $resize)
    {
        $width = ArrayHelper::getValue($resize, 'width');
        $height = ArrayHelper::getValue($resize, 'height');
        $image = Image::thumbnail($model->file->tempName, $width, $height);
        $image->save($model->filename);
        $model->size = filesize($model->filename);
        return true;
    }

    /**
     *
     * @param FileModel $model
     * @param array $crop
     */
    public static function cropImage($model, $crop, $rules = [])
    {
        $rasio = ArrayHelper::getValue($crop, 'er', 1);
        foreach (['w', 'h', 'x', 'y'] as $prop) {
            $crop[$prop] = $rasio * $crop[$prop];
        }
        $inValid = isset($rules['minWidth']) && $crop['w'] < $rules['minWidth'] || isset($rules['minHeight']) && $crop['h']
            < $rules['minHeight'] || isset($rules['maxWidth']) && $crop['w'] > $rules['maxWidth'] || isset($rules['maxHeight'])
            && $crop['h'] > $rules['maxHeight'];
        if ($inValid) {
            Yii::$app->session->setFlash('error', 'Invalid image size');
            return false;
        }
        $image = Image::crop($model->file->tempName, $crop['w'], $crop['h'], [$crop['x'], $crop['y']]);
        $image->save($model->filename);
        $model->size = filesize($model->filename);
        return true;
    }
}

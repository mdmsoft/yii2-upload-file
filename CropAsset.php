<?php

namespace mdm\upload;

/**
 * Description of CropAsset
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class CropAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@mdm/upload/assets';
    public $js = [
        'http://jcrop-cdn.tapmodo.com/v0.9.12/js/jquery.Jcrop.min.js',
        'js/dcropbox.js'
    ];
    public $css = [
        'http://jcrop-cdn.tapmodo.com/v0.9.12/css/jquery.Jcrop.min.css'
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}

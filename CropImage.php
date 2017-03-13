<?php

namespace mdm\upload;

use yii\helpers\Html;
use yii\helpers\Json;

/**
 * Description of CropImage
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class CropImage extends \yii\widgets\InputWidget
{
    /**
     * @var array the HTML attributes for the input tag.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $imgOptions = [];

    /**
     *
     * @var array
     */
    public $clientOptions = [];
    /**
     *
     * @var string
     */
    public $cropParam = 'crop';

    /**
     * @inheritdoc
     */
    public function init()
    {

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $id = $this->options['id'] = $this->getId();

        Html::addCssClass($this->options, 'dcorpbox');
        Html::addCssClass($this->imgOptions, 'content');
        CropAsset::register($this->getView());

        $clientOptions = $this->clientOptions;
        $this->imgOptions['id'] = $id . '-img';
        $clientOptions['imgTemplate'] = Html::tag('img', '',$this->imgOptions);
        $opts = Json::encode($clientOptions);
        $js = "jQuery('#{$id}').dCropBox($opts);";
        $this->getView()->registerJs($js);
        $inputOptions = ['style' => 'visibility:hidden;', 'class' => 'file-input', 'id' => $id . '-file'];
        if ($this->hasModel()) {
            $fileInput = Html::activeFileInput($this->model, $this->attribute, $inputOptions);
        } else {
            $fileInput = Html::fileInput($this->name, $this->value, $inputOptions);
        }
        $content = <<<HTML
<div class="container"></div>
<div style="display: none;">
    <input type="hidden" name="{$this->cropParam}[x]" data-attr="x">
    <input type="hidden" name="{$this->cropParam}[y]" data-attr="y">
    <input type="hidden" name="{$this->cropParam}[w]" data-attr="w">
    <input type="hidden" name="{$this->cropParam}[h]" data-attr="h">
</div>
$fileInput
HTML;
        return Html::tag('div', $content, $this->options);
    }
}

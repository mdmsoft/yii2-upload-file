<?php

namespace mdm\upload;

use Yii;
use yii\web\UploadedFile;
use yii\db\ActiveRecord;
use yii\helpers\FileHelper;

/**
 * UploadBehavior save uploaded file into [[$uploadPath]] and store information in database.
 * 
 * Usage at [[\yii\base\Model::behaviors()]] add the following code
 *
 * ~~~
 * return [
 *     ...
 *     [
 *         'class' => 'mdm\upload\UploadBehavior',
 *         'uploadPath' => '@common/upload', // default to '@runtime/upload'
 *         'attribute' => 'file', // attribute use to receive from FileField
 *         'savedAttribute' => 'file_id', // attribute use to receive id of file
 *     ],
 * ];
 * ~~~
 * 
 * @property \yii\base\Model $owner
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class UploadBehavior extends \yii\base\Behavior
{
    /**
     * @var string the directory to store uploaded files. You may use path alias here.
     * If not set, it will use the "upload" subdirectory under the application runtime path.
     */
    public $uploadPath = '@runtime/upload';

    /**
     * @var string  the attribute that will receive the uploaded file
     */
    public $attribute = 'file';

    /**
     * @var string the attribute that will receive the file id
     */
    public $savedAttribute;

    /**
     * @var integer the level of sub-directories to store uploaded files. Defaults to 1.
     * If the system has huge number of uploaded files (e.g. one million), you may use a bigger value
     * (usually no bigger than 3). Using sub-directories is mainly to ensure the file system
     * is not over burdened with a single directory having too many files.
     */
    public $directoryLevel = 1;

    /**
     * @var string the MIME-type of the uploaded file (such as "image/gif").
     * If type is not set, it will use [[\yii\helpers\FileHelper::getMimeType()]] to determine the exact MIME type.
     */
    public $type;

    /**
     * @var UploadedFile 
     */
    private $_file;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->uploadPath = Yii::getAlias($this->uploadPath);
    }

    /**
     * @inheritdoc
     */
    public function events()
    {
        return[
            ActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave'
        ];
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        if ($name === $this->attribute) {
            if ($this->_file === null) {
                $this->_file = UploadedFile::getInstance($this->owner, $this->attribute);
            }
            return $this->_file;
        } else {
            return parent::__get($name);
        }
    }

    /**
     * @inheritdoc
     */
    public function __set($name, $value)
    {
        if ($name === $this->attribute) {
            if ($value instanceof UploadedFile) {
                $this->_file = $value;
            }
        } else {
            parent::__set($name, $value);
        }
    }

    /**
     * @inheritdoc
     */
    public function canSetProperty($name, $checkVars = true)
    {
        return $name === $this->attribute || parent::canSetProperty($name, $checkVars);
    }

    /**
     * @inheritdoc
     */
    public function canGetProperty($name, $checkVars = true)
    {
        return $name === $this->attribute || parent::canGetProperty($name, $checkVars);
    }

    /**
     * Handler event
     * @param \yii\base\ModelEvent $event
     */
    public function beforeSave($event)
    {
        /* @var $file UploadedFile */
        $file = $this->{$this->attribute};
        if ($file !== null) {
            $name = $file->baseName;
            $fileName = $this->generateFilename($name);
            $size = $file->size;
            FileHelper::createDirectory(dirname($fileName));
            if ($file->saveAs($fileName, false)) {
                $model = new FileModel([
                    'basename' => $name,
                    'filename' => $fileName,
                    'filesize' => $size,
                    'content_type' => $this->type? : FileHelper::getMimeType($fileName),
                ]);
                if ($model->save()) {
                    if ($this->savedAttribute !== null) {
                        $this->owner->{$this->savedAttribute} = $model->id;
                    }
                } else {
                    $event->isValid = false;
                    $this->owner->addError($this->attribute);
                }
            } else {
                $event->isValid = false;
                $this->owner->addError($this->attribute, 'Cannot save uploaded file');
            }
        }
    }

    /**
     * Generate filename
     * @return string Filename
     */
    protected function generateFilename($name)
    {
        $key = md5(time() . $name);
        $base = $this->uploadPath;
        if ($this->directoryLevel > 0) {
            for ($i = 0; $i < $this->directoryLevel; ++$i) {
                if (($prefix = substr($key, $i + $i, 2)) !== false) {
                    $base .= DIRECTORY_SEPARATOR . $prefix;
                }
            }
        }
        return $base . DIRECTORY_SEPARATOR . "{$key}_{$name}";
    }
}
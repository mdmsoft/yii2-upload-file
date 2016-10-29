<?php

namespace mdm\upload;

use Yii;
use yii\web\UploadedFile;
use yii\db\BaseActiveRecord;

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
 * @property FileModel $savedFile
 * 
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
     * @var boolean when true `saveUploadedFile()` will be called on event 'beforeSave'
     */
    public $autoSave = true;

    /**
     * @var boolean when true then related file will be deleted on event 'beforeDelete'
     */
    public $autoDelete = false;

    /**
     * @var boolean 
     */
    public $deleteOldFile = false;

    /**
     * @var \Closure|string
     */
    public $saveCallback;

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
        $event = [];
        if ($this->autoSave) {
            $event[BaseActiveRecord::EVENT_BEFORE_INSERT] = 'beforeSave';
            $event[BaseActiveRecord::EVENT_BEFORE_UPDATE] = 'beforeSave';
        }
        if ($this->autoDelete && $this->savedAttribute !== null) {
            $event[BaseActiveRecord::EVENT_BEFORE_DELETE] = 'beforeDelete';
        }
        return $event;
    }

    /**
     * Get saved file
     * @return FileModel
     */
    public function getSavedFile()
    {
        if($this->savedAttribute && $this->owner->{$this->savedAttribute}){
            return FileModel::findOne($this->owner->{$this->savedAttribute});
        }
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
            if ($value instanceof UploadedFile || $value === null) {
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
     * Save uploaded file into [[$uploadPath]]
     * @param boolean $deleteOldFile If true and file exists, file will be deleted.
     * @return boolean|null if success return true, fault return false.
     * Return null mean no uploaded file.
     */
    public function saveUploadedFile($deleteOldFile = null)
    {
        /* @var $file UploadedFile */
        $file = $this->{$this->attribute};
        if ($file !== null) {
            $callback = $this->saveCallback;
            if ($callback !== null && is_string($callback)) {
                $callback = [$this->owner, $callback];
            }
            $model = FileModel::saveAs($file, [
                'uploadPath' => $this->uploadPath,
                'directoryLevel' => $this->directoryLevel,
                'saveCallback' => $callback,
            ]);
            if ($model) {
                if ($this->savedAttribute !== null) {
                    if ($deleteOldFile === null) {
                        $deleteOldFile = $this->deleteOldFile;
                    }
                    $oldId = $this->owner->{$this->savedAttribute};
                    $this->owner->{$this->savedAttribute} = $model->id;
                    if ($deleteOldFile && ($oldModel = FileModel::findOne($oldId)) !== null) {
                        return $oldModel->delete();
                    }
                }
                return true;
            }
            return false;
        }
    }

    /**
     * Event handler for beforeSave
     * @param \yii\base\ModelEvent $event
     */
    public function beforeSave($event)
    {
        if ($this->saveUploadedFile() === false) {
            $event->isValid = false;
        }
    }

    /**
     * Event handler for beforeDelete
     * @param \yii\base\ModelEvent $event
     */
    public function beforeDelete($event)
    {
        $oldId = $this->owner->{$this->savedAttribute};
        if (($oldModel = FileModel::findOne($oldId)) !== null) {
            $event->isValid = $event->isValid && $oldModel->delete();
        }
    }
}
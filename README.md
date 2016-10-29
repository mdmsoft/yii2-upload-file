Yii2 Upload File 
================

Yii2 tools for upload file

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require mdmsoft/yii2-upload-file "~2.0"
```

for dev-master

```
php composer.phar require mdmsoft/yii2-upload-file "dev-master"
```

or add

```
"mdmsoft/yii2-upload-file": "~2.0"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed.
Prepare required table by execute yii migrate.

```
yii migrate --migrationPath=@mdm/upload/migrations
```

if wantn't use db migration. you can create required table manually.

```sql
CREATE TABLE uploaded_file (
    "id" INT NOT NULL AUTO_INCREMENT,
    "name" VARCHAR(64),
    "filename" VARCHAR(256),
    "size" INT,
    "type" VARCHAR(32),
    PRIMARY KEY (id)
);
```

Modify your application configuration as follows:

```php
return [
    ...
    'controllerMap' => [
        'file' => 'mdm\\upload\\FileController', // use to show or download file
    ],
];
```

Then simply modify your Model class:

```php
public function behaviors()
{
	return [
        ...
		[
			'class' => 'mdm\upload\UploadBehavior',
			'attribute' => 'file', // required, use to receive input file
			'savedAttribute' => 'file_id', // optional, use to link model with saved file.
			'uploadPath' => '@common/upload', // saved directory. default to '@runtime/upload'
            'autoSave' => true, // when true then uploaded file will be save before ActiveRecord::save()
            'autoDelete' => true, // when true then uploaded file will deleted before ActiveRecord::delete()
		],
	];
}
```

You dont need add extra attribute `file` to model class. In controller

```php
public function actionCreate()
{
    if($model->load(Yii::$app->request->post()) && $model->save()){
        ...
    }
    ...
}
```

If you set `mdm\upload\UploadBehavior::$autoSave` to `false` you must call `saveUploadedFile()`.

```php
public function actionCreate()
{
    if($model->load(Yii::$app->request->post()) && $model->validate()){
        if($model->saveUploadedFile() !== false){
            $model->save(false);
            ....
        }
        ...
    }
    ...
}
```

In view file

```php
// in create or update view
<?= $form->field($model,'file')->fileInput(); ?>


// in view
<?= Html::img(['/file','id'=>$model->file_id]) ?>
<!-- assume the uploaded file is image ->
```

Using Without Attach Behavior
-----------------------------
Instead of using as behavior, you can also directly save file using `FileModel`.

```php
public function actionCreate()
{
    ...
    if($model->load(Yii::$app->request->post()) && $model->validate()){
        $file = UploadedFile::getInstance($model, 'file');
        if($fileModel = FileModel::saveAs($file,['uploadPath' => '@common/upload'])){
            $model->fil_id = $fileModel->id;
            $model->save();
            ....
        }
        ...
    }
    
}
```

But, you need to add attribute `file` to Model.

```php
class MyModel extend ...
{
    public $file; // add this to your model class

```

- See [my blog](http://mdmunir.wordpress.com/2014/10/19/yii2-simple-way-to-upload-and-save-file/)
Yii2 Upload File 
================

Yii2 tools for upload file

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require mdmsoft/yii2-upload-file "*"
```

for dev-master

```
php composer.phar require mdmsoft/yii2-upload-file "dev-master"
```

or add

```
"mdmsoft/yii2-upload-file": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed.
Prepare required table by execute yii migrate.

```
yii migrate --migrationPath=@mdm/upload/migrations
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
			'saveAttribute' => 'file_id', // optional, use to link model with saved file.
			'uploadPath' => '@common/upload', // saved directory. default to '@runtime/upload'
		],
	];
}
```

You dont need add extra attribute `file` to model class. In controller

```php
public function actionCreate()
{
    ...
    if($model->load(Yii::$app->request->post()) && $model->validate()){
        if($model->saveUploadedFile() !== false){
            $model->save();
            ....
        }
        ...
    }
    
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
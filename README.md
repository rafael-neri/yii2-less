yii2-less (fork singrana/yii2-less)
=========

Yii2 less support (Latest Less Versions).

Compatibility with singrana/yii2-less.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist ext4yii2/yii2-less "*"
```

or add

```
"ext4yii2/yii2-less": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Add or modify your Yii2 application config


```php
'components' =>
[
	'assetManager' =>
	[
		'converter' =>
		[
			'class' => 'ext4yii2\assets\Converter',
		],
	...
	],
	...
];
```

after this, you can usage in you bundles, for example:

```php

	public $css =
	[
		'css/style.less',
	];
```
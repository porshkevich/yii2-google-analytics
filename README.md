Google Analytics for Yii2
=========================
Google Analytics extension for the Yii2 framework

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist porshkevich/yii2-google-analytics "*"
```

or add

```
"porshkevich/yii2-google-analytics": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  :

```php
'ga'=>[
	'class'=>'porshkevich\googleanalytics\GoogleAnalyticsAPI',
	'defaultTrackingId' => 'UA-XXXXXXXX-X',
]
```
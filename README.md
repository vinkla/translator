Laravel Translator
==================

![laravel-translator](https://cloud.githubusercontent.com/assets/499192/7440607/b7c867cc-f0bf-11e4-9d13-0ce90882ae14.png)

This package gives you an easy way to translate Eloquent models into multiple languages.

```php
// Display the default title for an Eloquent object.
echo $foo->title;

// Change the current language to Swedish.
App::setLocale('sv');

// Display the translated title in Swedish.
echo $foo->title;
```
Read more about how this package was created and why it exists [in this blog post](http://vinkla.com/2014/11/laravel-translator/).

[![Build Status](https://img.shields.io/travis/vinkla/translator/master.svg?style=flat)](https://travis-ci.org/vinkla/translator)
[![StyleCI](https://styleci.io/repos/24419399/shield?style=flat)](https://styleci.io/repos/24419399)
[![Latest Version](https://img.shields.io/github/release/vinkla/translator.svg?style=flat)](https://github.com/vinkla/translator/releases)
[![License](https://img.shields.io/packagist/l/vinkla/translator.svg?style=flat)](https://packagist.org/packages/vinkla/translator)

## Installation

Require this package, with [Composer](https://getcomposer.org/), in the root directory of your project.

```bash
composer require vinkla/translator
```

Add the service provider to `config/app.php` in the providers array.

```bash
'Vinkla\Translator\TranslatorServiceProvider'
```

## Configuration

Laravel Translator requires configuration. To get started, you'll need to publish all vendor assets:
```bash
php artisan vendor:publish
```

This will create a `config/translator.php` file in your app that you can modify to set your configuration. Also, make sure you check for changes to the original config file in this package between releases.

This also creates a default locales migration in your `database/migrations` directory.

#### Locale Eloquent Model
This option `locale` is your full namespaced path for the `Locale` Eloquent object.

#### Locale Identifier Column
This option `column` is the column in your `locales` table which you want to compare the current set locale in your application. This column is compared with the `App::getLocale()` method to fetch the translations.

#### Fallback Support
This option `fallback` check whether you want to use the fallback translations if the current translation doesn't exist.

## Documentation

Below we have examples of [migrations](#migrations), [models](#models), [seeds](#seeds) and [templates](#templates). There also is an [example](#example) application that you can use as reference.

## Migrations

Here's an example of the localisations migration.

```php
Schema::create('locales', function(Blueprint $table)
{
	$table->increments('id');
	$table->string('language', 2); // en, sv, da, no, etc.
	$table->timestamps();
});
```

This example migration comes out of the box with this package. When you run `endor:publish` a default locales migration will be added to you `database/migrations` directory.

Add the Laravel migration for the base table which you want to translate.

```php
Schema::create('articles', function(Blueprint $table)
{
	$table->increments('id');
	$table->string('thumbnail');
	$table->timestamps();
});
```

Add the Laravel migration for the translatable relation table.

```php
Schema::create('article_translations', function(Blueprint $table)
{
	$table->increments('id');

	// Translatable attributes
	$table->string('title');
	$table->string('content');
	// Translatable attributes

	$table->integer('article_id')->unsigned()->index();
	$table->foreign('article_id')->references('id')->on('articles')->onDelete('cascade');

	$table->integer('locale_id')->unsigned()->index();
	$table->foreign('locale_id')->references('id')->on('locales')->onDelete('cascade');

	$table->unique(['article_id', 'locale_id']);

	$table->timestamps();
});
```

## Models
Firstly you'll need to setup the `Locale` Eloquent model. Then add the `Locale` model path to the configuration file.

```php
<?php

namespace Acme\Locales;

use Illuminate\Database\Eloquent\Model;

class Locale extends Model
{
	/**
	 * @var array
	 */
	protected $fillable = ['id'];
}
```

Here's an example of a translatable Laravel Eloquent model. Remember to fill the `$fillable` array the translatable attributes.


```php
<?php

namespace Acme\Articles;

use Illuminate\Database\Eloquent\Model;
use Vinkla\Translator\TranslatableTrait;
use Vinkla\Translator\TranslatableInterface;

class Article extends Model implements TranslatableInterface
{
	use TranslatableTrait;

	/**
	 * @var array
	 */
	protected $fillable = ['title', 'content', 'thumbnail'];

	/**
	 * @var string
	 */
	protected $translator = 'Acme\Articles\ArticleTranslation';

	/**
	 * @var array
	 */
	protected $translatedAttributes = ['title', 'content'];
}
```

The `ArticleTranslation` basically is an empty Eloquent object.

```php
<?php

namespace Acme\Articles;

use Illuminate\Database\Eloquent\Model;

class ArticleTranslation extends Model {}
```

## Seed
Before you start to populate your database with translations you'll need to add languages to the locales table that you want to support. Below is an example seeder.

```php
<?php

use Acme\Locales\Locale;
use Illuminate\Database\Seeder;

class LocaleTableSeeder extends Seeder
{
	public function run()
	{
		$languages = ['en', 'sv', 'no'];

		foreach ($languages as $language)
		{
			Locale::create(compact('language'));
		}
	}
}
```

### Templates

That's it! You're done. Now you can do:
```php
<h1>{{ $article->title }}</h1>
<img src="{{ $article->thumbnail }}">
<p>{{ $article->content }}</p>
```

If you want to fetch a specific translation that isn't the current one you can specify it in the translate method as in the example below.
```php
<h1>{{ $article->translate('sv')->title }}</h1>
<img src="{{ $article->thumbnail }}">
<p>{{ $article->translate('sv')->content }}</p>
```

## Example
If you want a working example you can visit the [example respository](https://github.com/vinkla/translator-example) for this package. It's a Laravel 5 application that utilies the translator package.

## License

Laravel Translator is licensed under [The MIT License (MIT)](LICENSE).

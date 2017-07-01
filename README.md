# Laravel Translator

![laravel translator](https://cloud.githubusercontent.com/assets/499192/13553952/98b2db00-e39a-11e5-9e82-aca4df0961be.jpg)

> An easy-to-use Eloquent translator for Laravel.

```php
// Fetch an Eloquent object
$article = Article::find(1);

// Display title in default language
echo $article->title;

// Change the current locale to Swedish
App::setLocale('sv');

// Display title in Swedish
echo $article->title;
```

[![Build Status](https://img.shields.io/travis/vinkla/laravel-translator/master.svg?style=flat)](https://travis-ci.org/vinkla/laravel-translator)
[![StyleCI](https://styleci.io/repos/24419399/shield?style=flat)](https://styleci.io/repos/24419399)
[![Coverage Status](https://img.shields.io/codecov/c/github/vinkla/laravel-translator.svg?style=flat)](https://codecov.io/github/vinkla/laravel-translator)
[![Latest Version](https://img.shields.io/github/release/vinkla/translator.svg?style=flat)](https://github.com/vinkla/translator/releases)
[![License](https://img.shields.io/packagist/l/vinkla/translator.svg?style=flat)](https://packagist.org/packages/vinkla/translator)

## Installation

Require this package, with [Composer](https://getcomposer.org/), in the root directory of your project.

```bash
$ composer require vinkla/translator
```

> That's it! No need for config files or loading service providers.


## Setup

- In this example we have *articles*, the *titles* of which should be translated.

- You should already have the `articles` table, but keep the `title` column in a separate table:

- Create an `article_translations` table, with `article_id` and `locale` columns (make each combination unique), and of course the `title` column.

```php
Schema::create('article_translations', function (Blueprint $table) {
    $table->increments('id');
    $table->integer('article_id')->unsigned()->index();
    $table->string('locale')->index();
    $table->string('title'); // Translated column.
    $table->timestamps();

    $table->unique(['article_id', 'locale']);

    $table->foreign('article_id')
        ->references('id')
        ->on('articles')
        ->onDelete('cascade');
});
```

- Add the `Translatable` trait to the `Article` Eloquent model, and fill the `$translatable` array with the translatable attributes.

```php
class Article extends Model
{
    use Translatable;

    protected $translatable = ['title'];
}
```

- Finally, we need a way to setup the relation:

    - If you leave it as-is, we will use the defaults, that is, the translations
    will be searched in a table named `{lower case model name}_translations`
    (in this example, `article_translations`), and the translation objects will
    behave like default Eloquent objects (no fillable attributes, using timestamps, etc.)

    - If you need to change this, you can define the translations model overriding
    the `getTranslationsClass` method:

        ```php
        class Article extends Model
        {
            use Translatable;

            protected $translatable = ['title'];

            public function getTranslationsClass()
            {
                return new class() extends Model {
                    protected $table = 'article_translations';
                    protected $fillable = ['title'];
                    public $timestamps = false;
                };
            }
        }
        ```

    - Instead of an anonymous class, you can create a dedicated class for the translations:

        ```php
        class Article extends Model
        {
            use Translatable;

            protected $translatable = ['title'];

            public function getTranslationsClass()
            {
                return ArticleTranslation::class;
            }
        }

        //

        class ArticleTranslation extends Model
        {
            protected $table = 'art_translations';
            protected $fillable = ['title'];
            public $timestamps = false;
        }
        ```

    - Finally, you can define a `translations` relation as you would any other Eloquent relation.
    This allows most detailed configuration (for instance, if you need to use non-default
    foreign / primary keys column names) and will also require a separate class for the translations:

        ```php
        class Article extends Model
        {
            use Translatable;

            protected $translatable = ['title'];

            public function translations()
            {
                return $this->hasMany(ArticleTranslation::class, 'IdArticle', 'Id');
            }
        }
        ```

Now you're ready to start translating your Eloquent models!


## Usage

Fetch pre-filled translated attributes.

```php
$article->title;
```

Fetch translated attributes with the `translate()` method.

```php
$article->translate()->title;
```

Fetch translated attributes for a specific locale with the `translate()` method.

```php
$article->translate('sv')->title;
```

Fetch translated attributes without fallback support.

```php
$article->translate('de', false)->title;
```

Load all translations eagerly.

```php
$articles = Article::with('translations')->get();
```

Eager load the translations for current locale.

```php
$articles = Article::withTranslations()->get();
```

Eager load the translations for a single locale.

```php
$articles = Article::withTranslations('en')->get();
```

Create instance with translated attributes.

```php
Article::create(['title' => 'Use the force Harry']);
```

> Note that this package will automatically find translated attributes based on items from the `$translatable` array in the Eloquent model.

Create instance with translated attributes for a specific locale.

```php
App::setLocale('sv');

Article::create(['title' => 'Använd kraften Harry']);
```

Update translated attributes.

```php
$article->update(['title' => 'Whoa. This is heavy.']);
```

Update translated attributes for a specific locale.

```php
App::setLocale('sv');

$article->update(['title' => 'Whoa. Detta är tung.']);
```

Delete an article with translations.

```php
$article->delete();
```

Delete translations.

```php
$article->translations()->delete();
```

Want more? Then you should definitely check out [the tests](tests). They showcase how to setup a basic project and are quite readable. Happy hacking!

## License

[MIT](LICENSE) © [Vincent Klaiber](https://vinkla.com)

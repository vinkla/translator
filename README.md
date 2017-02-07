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

Create a new migration for the translations. In our case we want to translate the `articles` table.

```bash
$ php artisan make:migration create_article_translations_table
```

Make sure you add the `article_id` and `locale` columns. Also, make them unique.

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * This is the article translations table seeder class.
 *
 * @author Vincent Klaiber <hello@vinkla.com>
 */
class CreateArticleTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article_translations', function (Blueprint $table) {
            $table->increments('id');

            $table->string('title'); // Translated column.

            $table->integer('article_id')->unsigned()->index();
            $table->foreign('article_id')
                ->references('id')
                ->on('articles')
                ->onDelete('cascade');

            $table->string('locale')->index();

            $table->unique(['article_id', 'locale']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('article_translations');
    }
}
```

Create a new empty `ArticleTranslation` Eloquent model.

```php
use Illuminate\Database\Eloquent\Model;

/**
 * This is the article translation eloquent model class.
 *
 * @author Vincent Klaiber <hello@vinkla.com>
 */
class ArticleTranslation extends Model
{
    /**
     * A list of methods protected from mass assignment.
     *
     * @var string[]
     */
    protected $guarded = ['_token', '_method'];
}

```

Add the `Translatable` trait and the `IsTranslatable` interface to the `Article` Eloquent model. Add the has-many `translations()` relation method and fill the `$translatable` array with translatable attributes.

```php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Vinkla\Translator\IsTranslatable;
use Vinkla\Translator\Translatable;

/**
 * This is the article eloquent model class.
 *
 * @author Vincent Klaiber <hello@vinkla.com>
 */
class Article extends Model implements IsTranslatable
{
    use Translatable;

    /**
     * A list of methods protected from mass assignment.
     *
     * @var string[]
     */
    protected $guarded = ['_token', '_method'];

    /**
     * List of translated attributes.
     *
     * @var string[]
     */
    protected $translatable = ['title'];

    /**
     * Get the translations relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations(): HasMany
    {
        return $this->hasMany(ArticleTranslation::class);
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

Fetch resources and eager load the translations.

```php
$articles = Article::with('translations')->get();
```

Fetch resources and eager load the translations for a single locale.

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

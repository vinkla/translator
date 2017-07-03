<?php

/*
 * This file is part of Laravel Translator.
 *
 * (c) Vincent Klaiber <hello@vinkla.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vinkla\Tests\Translator\Models;

use Illuminate\Database\Eloquent\Model;
use Vinkla\Translator\Translatable;

/**
 * This is the article eloquent model class.
 *
 * @author Vincent Klaiber <hello@vinkla.com>
 */
class ArticleTranslationsWithClassName extends Model
{
    use Translatable;

    protected $table = 'articles';

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
     * Get the default foreign key name for the model.
     *
     * @return string
     */
    public function getForeignKey()
    {
        return 'article_id';
    }

    /**
     * Get the class name or an anonymous class of the translations model.
     *
     * @return string|\Illuminate\Database\Eloquent\Model
     */
    public function getTranslationsClass()
    {
        return ArticleTranslation::class;
    }
}

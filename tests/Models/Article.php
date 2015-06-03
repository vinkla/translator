<?php

/*
 * This file is part of Laravel AbstractTranslator.
 *
 * (c) Vincent Klaiber <hello@vinkla.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vinkla\Tests\Translator;

use Illuminate\Database\Eloquent\Model;
use Vinkla\Translator\TranslatableTrait;
use Vinkla\Translator\TranslatableInterface;

class Article extends Model implements TranslatableInterface
{
    use TranslatableTrait;

    /**
     * The translations model.
     *
     * @var string
     */
    protected $translator = 'Vinkla\Tests\Translator\ArticleTranslation';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title', 'content', 'image'];

    /**
     * Setup a one-to-many relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany($this->translator);
    }
}

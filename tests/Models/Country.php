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
 * This is the country eloquent model class.
 *
 * @author Alejandro Peinó <alepeino@gmail.com>
 */
class Country extends Model
{
    use Translatable;

    protected $primaryKey = 'code';

    public $incrementing = false;

    public $timestamps = false;

    /**
     * List of translated attributes.
     *
     * @var string[]
     */
    protected $translatable = ['name'];
}

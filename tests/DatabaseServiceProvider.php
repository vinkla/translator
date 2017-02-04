<?php

/*
 * This file is part of Laravel Translator.
 *
 * (c) Vincent Klaiber <hello@vinkla.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vinkla\Tests\Translator;

use Illuminate\Support\ServiceProvider;

/**
 * This is the service provider with configurations for testing this package.
 *
 * @author Alejandro Peinó <alepeino@gmail.com>
 */
class DatabaseServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(realpath(__DIR__.'/database/migrations'));
    }
}

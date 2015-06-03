<?php

/*
 * This file is part of Laravel AbstractTranslator.
 *
 * (c) Vincent Klaiber <hello@vinkla.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vinkla\Translator;

use Illuminate\Support\ServiceProvider;

/**
 * This is the translator service provider class.
 *
 * @author Vincent Klaiber <hello@vinkla.com>
 */
class TranslatorServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->setupConfig();
        $this->setupMigrations();
    }

    /**
     * Setup the config.
     *
     * @return void
     */
    protected function setupConfig()
    {
        $source = realpath(__DIR__.'/../config/translator.php');

        $this->publishes([$source => config_path('translator.php')]);

        $this->mergeConfigFrom($source, 'translator');
    }

    /**
     * Setup the migrations.
     *
     * @return void
     */
    protected function setupMigrations()
    {
        $source = realpath(__DIR__.'/../database/migrations/');

        $this->publishes([$source => base_path('/database/migrations')], 'migrations');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}

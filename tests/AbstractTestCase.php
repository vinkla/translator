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

use ArticleTableSeeder;
use GrahamCampbell\TestBench\AbstractPackageTestCase;
use Illuminate\Support\Facades\DB;
use TranslationTableSeeder;

/**
 * This is the abstract test case class.
 *
 * @author Vincent Klaiber <hello@vinkla.com>
 */
abstract class AbstractTestCase extends AbstractPackageTestCase
{
    protected function getPackageProviders($app)
    {
        return [DatabaseServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app->config->set('app.locale', 'sv');
        $app->config->set('app.fallback', 'en');
    }

    /**
     * @before
     */
    public function runDatabaseMigrations()
    {
        DB::statement(DB::raw('PRAGMA foreign_keys=1'));

        $this->artisan('migrate');

        $this->beforeApplicationDestroyed(function () {
            $this->artisan('migrate:rollback');
        });
    }

    /**
     * @before
     */
    public function seedDatabase()
    {
        $this->seed(ArticleTableSeeder::class);
        $this->seed(TranslationTableSeeder::class);
    }
}

<?php

/*
 * This file is part of Laravel Translator.
 *
 * (c) Vincent Klaiber <hello@vinkla.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Database\Seeder;
use Vinkla\Tests\Translator\Models\ArticleTranslation;

/**
 * This is the translation table seeder class.
 *
 * @author Vincent Klaiber <hello@vinkla.com>
 */
final class TranslationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ArticleTranslation::create(['title' => 'Use the force Harry', 'article_id' => 1, 'locale' => 'en']);
        ArticleTranslation::create(['title' => 'Använd kraften Harry', 'article_id' => 1, 'locale' => 'sv']);
    }
}

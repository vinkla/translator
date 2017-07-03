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
use Illuminate\Support\Facades\DB;

/**
 * This is the country translations table seeder class.
 *
 * @author Alejandro Peinó <alepeino@gmail.com>
 */
final class CountryTranslationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('country_translations')->insert([
            ['country_code' => 'SE', 'locale' => 'sv', 'name' => 'Sverige'],
            ['country_code' => 'SE', 'locale' => 'en', 'name' => 'Sweden'],
        ]);
    }
}

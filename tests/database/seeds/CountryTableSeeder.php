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
use Vinkla\Tests\Translator\Models\Country;

/**
 * This is the countries table seeder class.
 *
 * @author Alejandro Peinó <alepeino@gmail.com>
 */
final class CountryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Country::create(['code' => 'SE']);
    }
}

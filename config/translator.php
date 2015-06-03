<?php

/*
 * This file is part of Laravel AbstractTranslator.
 *
 * (c) Vincent Klaiber <hello@vinkla.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Driver
    |--------------------------------------------------------------------------
    |
    | Select the driver to use to fetch locales with. Either set it to 'file'
    | or 'database'. Both drivers requires more configuration below.
    |
    */

    'driver' => 'database',

    /*
    |--------------------------------------------------------------------------
    | Default Locales
    |--------------------------------------------------------------------------
    |
    | This is the array of supported locales to translate you eloquent models
    | in. Please note that this requires the 'file' driver.
    |
    */

    'locales' => ['en'],

    /*
    |--------------------------------------------------------------------------
    | Locale Eloquent Model
    |--------------------------------------------------------------------------
    |
    | This is the full namespaced path to the Eloquent model that handles the
    | languages you support within your project. Please note that this requires
    | the 'database' driver.
    |
    */

    'model' => 'Acme\Locales\Locale',

    /*
    |--------------------------------------------------------------------------
    | Fallback Support
    |--------------------------------------------------------------------------
    |
    | Set this to true if you want to fetch the default translation if the
    | current locale doesn't have any translated attributes yet. The default
    | fallback is fetched from app/config/app.php
    |
    */

    'fallback' => false,

];

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

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;
use Vinkla\Tests\Translator\Models\Country;

/**
 * This is the test class for translations configuration from defaults.
 *
 * @author Alejandro Peinó <alepeino@gmail.com>
 */
class ConfigTranslationsFromDefaultsTest extends AbstractTestCase
{
    public function testGetTranslationsTable()
    {
        $country = new Country();

        $this->assertSame('country_translations', $country->getTranslationsTable());
    }

    public function testGetTranslationsClass()
    {
        $country = new Country();

        $this->assertInstanceOf(Model::class, $country->getTranslationsClass());
    }

    public function testHasMany()
    {
        $country = Country::first();

        $this->assertCount(2, $country->translations);
    }

    public function testTranslate()
    {
        $country = Country::first();

        $this->assertSame($country->translate('en')->name, 'Sweden');
        $this->assertSame($country->translate('sv')->name, 'Sverige');
    }

    public function testEmptyTranslation()
    {
        $country = Country::unguarded(function () {
            return Country::create(['code' => 'AU']);
        });

        $this->assertNull($country->name);
    }

    public function testLocale()
    {
        $country = Country::first();

        $locale = $this->getProtectedMethod($country, 'getLocale');
        $this->assertSame('sv', $locale);

        $fallback = $this->getProtectedMethod($country, 'getFallback');
        $this->assertSame('en', $fallback);
    }

    public function testFallback()
    {
        $country = Country::first();

        $this->assertSame($country->translate('de')->name, 'Sweden');
        $this->assertSame($country->translate('de', true)->name, 'Sweden');

        App::setLocale('sv');

        $this->assertSame($country->translate('sv', false)->name, 'Sverige');

        $this->assertSame(App::getLocale(), 'sv');
    }

    public function testSetLocale()
    {
        $country = Country::first();

        $this->assertSame($country->name, 'Sverige');
        $this->assertSame($country->translate()->name, 'Sverige');

        App::setLocale('en');

        $this->assertSame($country->name, 'Sweden');
        $this->assertSame($country->translate()->name, 'Sweden');
    }

    public function testCachedTranslations()
    {
        $country = Country::first();

        $translations = ['en' => $country->translate('en'), 'sv' => $country->translate('sv')];
        $cache = $this->getProtectedProperty($country, 'cache');

        $this->assertCount(2, $cache);
        $this->assertSame($translations, $cache);

        $this->assertQueryCount(0, function () use ($country) {
            $country->translate('en');
        });
    }

    public function testGetAttributes()
    {
        $country = Country::first();

        $this->assertSame($country->translate()->name, 'Sverige');
        $this->assertSame($country->name, 'Sverige');
    }

    public function testSetAttributes()
    {
        App::setLocale('en');

        $country = Country::first();

        $this->assertSame($country->name, 'Sweden');

        $country->name = 'Kingdom of Sweden';

        $this->assertSame($country->name, 'Kingdom of Sweden');
        $this->assertSame($country->translate()->name, 'Kingdom of Sweden');
        $this->assertSame($country->translate('sv')->name, 'Sverige');
    }

    public function testCreate()
    {
        App::setLocale('en');

        $country = Country::unguarded(function () {
            return Country::create(['code' => 'AU', 'name' => 'Australia']);
        });

        $this->assertDatabaseHas('countries', ['code' => 'AU']);
        $this->assertDatabaseHas('country_translations', [
            'name' => 'Australia',
            'country_code' => $country->code, 'locale' => 'en',
        ]);
    }

    public function testDeleteTranslations()
    {
        Country::find('SE')->translations()->delete();

        $this->assertSame(1, Country::count());
        $this->assertSame(0, DB::table('country_translations')->where('country_code', '=', 'SE')->count());
    }

    public function testDeleteParent()
    {
        Country::find('SE')->delete();

        $this->assertSame(0, Country::count());
        $this->assertSame(0, DB::table('country_translations')->where('country_code', '=', 'SE')->count());
    }

    public function testIsDirty()
    {
        $country = Country::first();
        $country->name = 'A new name';

        $this->assertTrue($country->isDirty());
        $this->assertTrue($country->isDirty('name'));
        $this->assertFalse($country->isDirty('foo'));
    }

    public function testGetDirtyTranslations()
    {
        $country = Country::first();
        $country->name = 'A new name';

        $this->assertSame(['name' => 'A new name'], $country->getDirtyTranslations());
    }

    public function testNoEagerLoad()
    {
        $this->assertQueryCount(Country::count() + 1, function () {
            Country::all()->pluck('name');
        });
    }

    public function testEagerLoad()
    {
        $this->assertQueryCount(2, function () {
            Country::with('translations')->get()->pluck('name');
        });
    }

    public function testWithTranslationsScopeWithNoParameter()
    {
        $country = Country::withTranslations()->first();

        $this->assertTrue($country->relationLoaded('translations'));

        $this->assertQueryCount(0, function () use ($country) {
            $this->assertSame(1, $country->translations->count());
            $this->assertSame('Sverige', $country->name);
        });
    }

    public function testWithTranslationsScopeWithParameter()
    {
        $country = Country::withTranslations('en')->first();

        $this->assertTrue($country->relationLoaded('translations'));

        $this->assertQueryCount(0, function () use ($country) {
            $this->assertSame(1, $country->translations->count());
            $this->assertSame('Sweden', $country->name);
        });
    }
}

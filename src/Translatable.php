<?php

/*
 * This file is part of Laravel Translator.
 *
 * (c) Vincent Klaiber <hello@vinkla.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vinkla\Translator;

use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

/**
 * This is the translatable trait.
 *
 * @author Vincent Klaiber <hello@vinkla.com>
 */
trait Translatable
{
    /**
     * The cached locales.
     *
     * @static
     *
     * @var array
     */
    protected static $cachedLocales = [];

    /**
     * The cached translations.
     *
     * @var array
     */
    protected $cachedTranslations = [];

    /**
     * The translation instance.
     *
     * @var mixed
     */
    protected $translatorInstance;

    /**
     * Prepare a translator instance and fetch translations.
     *
     * @param null $locale
     *
     * @throws \Vinkla\Translator\TranslatorException
     *
     * @return mixed
     */
    public function translate($locale = null)
    {
        return $this->getTranslation(true, $locale);
    }

    /**
     * Fetch the translation by their relations and locale.
     *
     * @param bool $exists
     * @param null $locale
     *
     * @throws \Vinkla\Translator\TranslatorException
     *
     * @return mixed
     */
    private function getTranslation($exists = true, $locale = null)
    {
        if (!$this->translator || !class_exists($this->translator)) {
            throw new TranslatableException('Please set the $translator property to your translation model path.');
        }

        if (!$this->translatorInstance) {
            $this->translatorInstance = new $this->translator();

            // If translations have been eager loaded, copy them to the cache.
            if (array_key_exists('translations', $this->relations)) {
                foreach ($this->translations as $translation) {
                    $this->cachedTranslations[$translation->locale_id] = $translation;
                }
            }
        }

        $localeId = $this->getLocaleId($locale);

        // If there already is a current translation, use it.
        if (isset($this->cachedTranslations[$localeId])) {
            return $this->cachedTranslations[$localeId];
        }

        // Fetch the translation by their locale id.
        $translation = $this->getTranslationByLocaleId($localeId);

        if ($translation) {
            $this->cachedTranslations[$localeId] = $translation;

            return $this->cachedTranslations[$localeId];
        }

        // Fetch fallback translation if its set in the config.
        if ($exists && $this->useFallback()) {
            return $this->getTranslationByLocaleId(
                $this->getFallackLocaleId()
            );
        }

        // If we can't find any translation, return a new instance.
        return $this->newTranslation(['locale_id' => $localeId]);
    }

    /**
     * Fill the model with an array of attributes.
     *
     * @param array $attributes
     *
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     *
     * @return $this
     */
    public function fill(array $attributes)
    {
        $totallyGuarded = $this->totallyGuarded();

        foreach ($attributes as $key => $value) {
            if (!in_array($key, $this->translatedAttributes)) {
                continue;
            }

            $this->getTranslation(false);

            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            } elseif ($totallyGuarded) {
                throw new MassAssignmentException($key);
            }

            unset($attributes[$key]);
        }

        return parent::fill($attributes);
    }

    /**
     * Finish processing on a successful save operation.
     *
     * @param  array  $options
     *
     * @return void
     */
    public function finishSave(array $options)
    {
        if (count($this->cachedTranslations)) {
            $this->translations()->saveMany($this->cachedTranslations);
        }

        return parent::finishSave($options);
    }

    /**
     * Set a given attribute on the model.
     *
     * @param $key
     * @param $value
     *
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->translatedAttributes)) {
            return $this->getTranslation()->$key = $value;
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Get an attribute from the model.
     *
     * @param $key
     *
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (in_array($key, $this->translatedAttributes)) {
            return $this->getTranslation() ? $this->getTranslation()->$key : null;
        }

        return parent::getAttribute($key);
    }

    /**
     * Get an attribute array of all arrayable attributes.
     *
     * @return array
     */
    protected function getArrayableAttributes()
    {
        return array_merge(
            parent::getArrayableAttributes($this->attributes),
            $this->getTranslation()->getArrayableAttributes()
        );
    }

    /**
     * Fetch the translation by their locale.
     *
     * @param $localeId
     *
     * @return mixed
     */
    private function getTranslationByLocaleId($localeId)
    {
        $translation = $this->translatorInstance
            ->where('locale_id', $localeId)
            ->where($this->getForeignKey(), $this->id)
            ->first();

        if ($translation) {
            $translation->visible = $this->translatedAttributes;
        }

        return $translation;
    }

    /**
     * Get the current locale set within the app.
     *
     * @param null $locale
     *
     * @throws \Vinkla\Translator\TranslatorException
     *
     * @return mixed
     */
    private function getLocaleId($locale = null)
    {
        return $this->getLocale($locale ?: App::getLocale())->id;
    }

    /**
     * Get the fallback locale set within the app.
     *
     * @return mixed
     */
    private function getFallackLocaleId()
    {
        return $this->getLocaleId(Config::get('app.fallback_locale'));
    }

    /**
     * Fetch a locale by its locale.
     *
     * @param $locale
     *
     * @throws \Vinkla\Translator\TranslatorException
     *
     * @return mixed
     */
    private function getLocale($locale)
    {
        if (isset(self::$cachedLocales[$locale])) {
            return self::$cachedLocales[$locale];
        }

        $localeInstance = $this->getLocaleInstance();

        $column = $this->getLocaleColumn();

        self::$cachedLocales[$locale] = $localeInstance->where($column, $locale)->first();

        return self::$cachedLocales[$locale];
    }

    /**
     * Create a new translation instance of the translator model.
     *
     * @param array $attributes
     * @param bool $exists
     *
     * @return mixed
     */
    private function newTranslation($attributes = [], $exists = false)
    {
        $translation = new $this->translatorInstance();

        $fillable = $this->getParentFillable($translation->getFillable());

        $translation->fillable($fillable);
        $translation->visible = $this->translatedAttributes;

        $attributes = array_add($attributes, 'locale_id', $this->getLocaleId());

        $translation->fill((array) $attributes);

        $translation->exists = $exists;

        $this->cachedTranslations[$attributes['locale_id']] = $translation;

        return $translation;
    }

    /**
     * Get the locale column key.
     *
     * @return string
     */
    private function getLocaleColumn()
    {
        return Config::get('translator.column') ?: 'language';
    }

    /**
     * Fetch the locale instance.
     *
     * @throws \Vinkla\Translator\TranslatorException
     *
     * @return mixed
     */
    private function getLocaleInstance()
    {
        if (!Config::has('translator.locale')) {
            throw new TranslatableException(
                'Please set the \'locale\' property in the configuration to your Locale model path.'
            );
        }

        return App::make(Config::get('translator.locale'));
    }

    /**
     * Get the fillable attributes.
     *
     * @param array $defaults
     *
     * @return array
     */
    private function getParentFillable($defaults = [])
    {
        $fillable = $this->getFillable();

        array_push($fillable, 'locale_id');

        return array_merge($fillable, $defaults);
    }

    /**
     * Check if whether we should fetch the
     * fallback translation or not.
     *
     * @return mixed
     */
    private function useFallback()
    {
        return (bool) Config::get('translator.fallback');
    }

    /**
     * Setup a one-to-many relation.
     *
     * @return mixed
     */
    public function translations()
    {
        return $this->hasMany($this->translator);
    }
}

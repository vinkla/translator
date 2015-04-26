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
     * The current translation.
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
     * @return mixed
     * @throws TranslatorException
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
     * @return mixed
     * @throws TranslatableException
     */
    private function getTranslation($exists = true, $locale = null)
    {
        if (!$this->translator || !class_exists($this->translator)) {
            throw new TranslatableException('Please set the $translator property to your translation model path.');
        }

        if (!$this->translatorInstance) {
            $this->translatorInstance = new $this->translator();
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
     * @return $this
     *
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
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
     * Save the model to the database.
     *
     * @param array $options
     * @return bool
     */
    public function save(array $options = [])
    {
        $saved = parent::save($options);

        if ($saved && count($this->cachedTranslations)) {
            $this->translations()->saveMany($this->cachedTranslations);
        }

        return $saved;
    }

    /**
     * Update the model in the database.
     *
     * @param array $attributes
     * @return bool|int
     */
    public function update(array $attributes = [])
    {
        $updated = parent::update($attributes);

        if ($updated) {
            $this->translations()->saveMany($this->cachedTranslations);
        }

        return $updated;
    }

    /**
     * Set a given attribute on the model.
     *
     * @param $key
     * @param $value
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
     * Fetch the translation by their locale.
     *
     * @param $localeId
     * @return mixed
     */
    private function getTranslationByLocaleId($localeId)
    {
        return $this->translatorInstance
            ->where('locale_id', $localeId)
            ->where($this->getForeignKey(), $this->id)
            ->first();
    }

    /**
     * Get the current locale set within the app.
     *
     * @param null $locale
     * @return mixed
     * @throws TranslatorException
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
     * @return mixed
     * @throws TranslatorException
     */
    private function getLocale($locale)
    {
        $localeInstance = $this->getLocaleInstance();

        $column = $this->getLocaleColumn();

        return $localeInstance->where($column, $locale)->first();
    }

    /**
     * Create a new translation instance of the translator model.
     *
     * @param array $attributes
     * @param bool $exists
     * @return mixed
     */
    private function newTranslation($attributes = [], $exists = false)
    {
        $translation = new $this->translatorInstance();

        $fillable = $this->getParentFillable($translation->getFillable());

        $translation->fillable($fillable);

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
     * @return mixed
     * @throws TranslatableException
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

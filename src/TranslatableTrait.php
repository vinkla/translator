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

use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

/**
 * This is the translatable trait.
 *
 * @author Vincent Klaiber <hello@vinkla.com>
 */
trait TranslatableTrait
{
    /**
     * The cached locales.
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
     * @throws \Vinkla\Translator\TranslatableException
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
     * @throws \Vinkla\Translator\TranslatableException
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
                    $this->cachedTranslations[$translation->locale] = $translation;
                }
            }
        }

        $locale = $this->getLocale($locale);

        // If there already is a current translation, use it.
        if (isset($this->cachedTranslations[$locale])) {
            return $this->cachedTranslations[$locale];
        }

        // Fetch the translation by their locale id.
        $translation = $this->findByLocale($locale);

        if ($translation) {
            $this->cachedTranslations[$locale] = $translation;

            return $this->cachedTranslations[$locale];
        }

        // Fetch fallback translation if its set in the config.
        if ($exists && $this->fallback()) {
            return $this->findByLocale($this->getFallackLocale());
        }

        // If we can't find any translation, return a new instance.
        return $this->newTranslation(['locale' => $locale]);
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
     * Save the model to the database.
     *
     * @param array $options
     *
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
     *
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
     * Fetch the translation by their locale id.
     *
     * @param string $locale
     *
     * @return mixed
     */
    private function findByLocale($locale)
    {
        $translation = $this->translatorInstance
            ->where('locale', $locale)
            ->where($this->getForeignKey(), $this->id)
            ->first();

        if ($translation) {
            $translation->visible = $this->translatedAttributes;
        }

        return $translation;
    }

    /**
     * Get the fallback locale set within the app.
     *
     * @return mixed
     */
    private function getFallackLocale()
    {
        return config('app.fallback_locale');
    }

    /**
     * Fetch a locale by its id.
     *
     * @param null $locale
     *
     * @throws \Vinkla\Translator\TranslatableException
     *
     * @return mixed
     */
    private function getLocale($locale = null)
    {
        $locales = $this->getSupportedLocales();

        // Fetch supported locales.
        // Check if the exist.
        // Remember cache.
    }

    private function getSupportedLocales()
    {
        $driver = $this->getDriver();

        if ($driver === 'database') {
            $locales =
        }

        if ($driver === 'file') {
            $locales = config('translator.locales');
        }

        throw new TranslatableException('Please provide a valid translator driver.');
    }

    private function getDriver()
    {
        return config('translator.driver', 'file');
    }

    /**
     * Create a new translation instance of the translator model.
     *
     * @param array $attributes
     * @param bool $exists
     *
     * @return mixed
     */
    private function newTranslation(array $attributes = [], $exists = false)
    {
        $translation = new $this->translatorInstance();

        $fillable = $this->getParentFillable($translation->getFillable());

        $translation->fillable($fillable);

        $translation->visible = $this->translatedAttributes;

        $attributes = array_add($attributes, 'locale', $this->getLocale());

        $translation->fill($attributes);

        $translation->exists = $exists;

        $this->cachedTranslations[$attributes['locale']] = $translation;

        return $translation;
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

        array_push($fillable, 'locale');

        return array_merge($fillable, $defaults);
    }

    /**
     * Check if whether we should fetch the fallback translation or not.
     *
     * @return bool
     */
    private function fallback()
    {
        return config('translator.fallback', 'false');
    }
}

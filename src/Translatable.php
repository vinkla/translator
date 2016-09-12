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

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;

/**
 * This is the translatable trait.
 *
 * @author Vincent Klaiber <hello@vinkla.com>
 */
trait Translatable
{
    /**
     * The translations cache.
     *
     * @var array
     */
    protected $cache = [];

    /**
     * Get a translation.
     *
     * @param string|null $locale
     * @param bool $fallback
     *
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public function translate($locale = null, $fallback = true)
    {
        $locale = $locale ?: $this->getLocale();

        $translation = $this->getTranslation($locale);

        if (!$translation && $fallback) {
            $translation = $this->getTranslation($this->getFallback());
        }

        if (!$translation && !$fallback) {
            $translation = $this->getEmptyTranslation($locale);
        }

        return $translation;
    }

    /**
     * Get a translation or create new.
     *
     * @param string $locale
     *
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    protected function translateOrNew($locale)
    {
        $translation = $this->getTranslation($locale);

        if (!$translation) {
            return $this->translations()
                ->where('locale', $locale)
                ->firstOrNew(['locale' => $locale]);
        }

        return $translation;
    }

    /**
     * Get a translation.
     *
     * @param string $locale
     *
     * @return \Illuminate\Database\Eloquent\Model|static|null
     */
    protected function getTranslation($locale)
    {
        if (isset($this->cache[$locale])) {
            return $this->cache[$locale];
        }

        $translation = $this->translations()
            ->where('locale', $locale)
            ->first();

        if ($translation) {
            $this->cache[$locale] = $translation;
        }

        return $translation;
    }

    /**
     * Get an empty translation.
     *
     * @param string $locale
     *
     * @return mixed
     */
    protected function getEmptyTranslation($locale)
    {
        $appLocale = $this->getLocale();

        $this->setLocale($locale);

        $translation = null;

        foreach ($this->getTranslatable() as $attribute) {
            $translation = $this->setAttribute($attribute, null);
        }

        $this->setLocale($appLocale);

        return $translation;
    }

    /**
     * Get an attribute from the model or translation.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (in_array($key, $this->getTranslatable())) {
            return $this->translate() ? $this->translate()->$key : null;
        }

        return parent::getAttribute($key);
    }

    /**
     * Set a given attribute on the model or translation.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->getTranslatable())) {
            $translation = $this->translateOrNew($this->getLocale());

            $translation->$key = $value;

            $this->cache[$this->getLocale()] = $translation;

            return $translation;
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Get the translatable array.
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    protected function getTranslatable()
    {
        if (!property_exists($this, 'translatable')) {
            throw new InvalidArgumentException('Missing property [translatable].');
        }

        return $this->translatable;
    }

    /**
     * Determine if the model or given attribute(s) have been modified.
     *
     * @param array|string|null $attributes
     *
     * @return bool
     */
    public function isDirty($attributes = null)
    {
        if (! empty($attributes)) {
            return parent::isDirty($attributes);
        }

        return parent::isDirty() ?: count($this->cache) > 0;
    }

    /**
     * Finish processing on a successful save operation.
     *
     * @param array $options
     *
     * @return void
     */
    protected function finishSave(array $options)
    {
        $this->translations()->saveMany($this->cache);

        parent::finishSave($options);
    }

    /**
     * Set the locale.
     *
     * @param string $locale
     *
     * @return void
     */
    protected function setLocale($locale)
    {
        App::setLocale($locale);
    }

    /**
     * Get the locale.
     *
     * @return string
     */
    protected function getLocale()
    {
        return App::getLocale();
    }

    /**
     * Get the fallback locale.
     *
     * @return string
     */
    protected function getFallback()
    {
        return Config::get('app.fallback_locale');
    }

    /**
     * Get the translations relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    abstract public function translations();
}

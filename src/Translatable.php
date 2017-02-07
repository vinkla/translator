<?php

/*
 * This file is part of Laravel Translator.
 *
 * (c) Vincent Klaiber <hello@vinkla.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Vinkla\Translator;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
     * The translations cache.
     *
     * @var array
     */
    protected $cache = [];

    /**
     * Query scope for eager-loading the translations for current (or a given) locale.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param string|null $locale
     *
     * @return void
     */
    public static function scopeWithTranslations(Builder $builder, string $locale = null)
    {
        $locale = $locale ?: (new static())->getLocale();

        $builder->with(['translations' => function (HasMany $query) use ($locale) {
            $query->where('locale', $locale);
        }]);
    }

    /**
     * Get a translation.
     *
     * @param string|null $locale
     * @param bool $fallback
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function translate(string $locale = null, bool $fallback = true): Model
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
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function translateOrNew(string $locale): Model
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
     * Get a translation from cache, loaded relation, or database.
     *
     * @param string $locale
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    protected function getTranslation(string $locale)
    {
        if (isset($this->cache[$locale])) {
            return $this->cache[$locale];
        }

        $translation = $this->translations
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
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function getEmptyTranslation(string $locale): Model
    {
        $appLocale = $this->getLocale();

        $this->setLocale($locale);

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
     * Get the translatable attributes array.
     *
     * @throws \Vinkla\Translator\TranslatableException
     *
     * @return array
     */
    protected function getTranslatable(): array
    {
        if (!property_exists($this, 'translatable')) {
            throw new TranslatableException('Missing property [translatable].');
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
    public function isDirty($attributes = null): bool
    {
        $dirty = array_merge($this->getDirty(), $this->getDirtyTranslations());

        if (is_null($attributes)) {
            return count($dirty) > 0;
        }

        if (!is_array($attributes)) {
            $attributes = func_get_args();
        }

        foreach ($attributes as $attribute) {
            if (array_key_exists($attribute, $dirty)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the translatable attributes that have been changed since last sync.
     *
     * @return array
     */
    public function getDirtyTranslations(): array
    {
        $dirty = [];

        foreach ($this->cache as $translation) {
            foreach ($translation->attributes as $key => $value) {
                if (!array_key_exists($key, $translation->original)) {
                    $dirty[$key] = $value;
                } elseif ($value !== $translation->original[$key] && !$translation->originalIsNumericallyEquivalent($key)) {
                    $dirty[$key] = $value;
                }
            }
        }

        return $dirty;
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
    protected function setLocale(string $locale)
    {
        App::setLocale($locale);
    }

    /**
     * Get the current locale.
     *
     * @return string
     */
    protected function getLocale(): string
    {
        return App::getLocale();
    }

    /**
     * Get the fallback locale.
     *
     * @return string
     */
    protected function getFallback(): string
    {
        return Config::get('app.fallback_locale');
    }

    /**
     * Get the translations relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    abstract public function translations(): HasMany;
}

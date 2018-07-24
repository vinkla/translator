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
use Illuminate\Support\Facades\Config;

/**
 * This is the translatable trait.
 *
 * @author Vincent Klaiber <hello@vinkla.com>
 */
trait Translatable
{
    /**
     * The cache of translations.
     *
     * @var array
     */
    protected $translationCache = [];

    /**
     * Boot the translatable trait.
     *
     * @return void
     */
    public static function bootTranslatable(): void
    {
        static::created(function (Model $model) {
            $model->translations()->saveMany($model->translationCache);
        });

        static::updating(function (Model $model) {
            $model->translations()->saveMany($model->translationCache);
        });
    }

    /**
     * Get a translation.
     *
     * @param string|null $locale
     * @param bool $fallback
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function translate(string $locale = null, bool $fallback = true): ?Model
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
     * Query scope for eager-loading the translations for current (or a given) locale.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $locale
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithTranslations(Builder $query, string $locale = null): Builder
    {
        $locale = $locale ?: $this->getLocale();

        return $query->with(['translations' => function (HasMany $query) use ($locale) {
            $query->where('locale', $locale);
        }]);
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
        if (isset($this->translationCache[$locale])) {
            return $this->translationCache[$locale];
        }

        if (!$this->exists) {
            return $this->translations()->newModelInstance(['locale' => $locale]);
        }

        return $this->translations()
            ->where('locale', $locale)
            ->firstOrNew(['locale' => $locale]);
    }

    /**
     * Get a translation from cache, loaded relation, or database.
     *
     * @param string $locale
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    protected function getTranslation(string $locale): ?Model
    {
        if (isset($this->translationCache[$locale])) {
            return $this->translationCache[$locale];
        }

        if (!$this->exists) {
            return null;
        }

        $translation = $this->translations
            ->where('locale', $locale)
            ->first();

        if ($translation) {
            $this->translationCache[$locale] = $translation;
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

            $this->translationCache[$this->getLocale()] = $translation;

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
        $attributes = is_array($attributes) ? $attributes : func_get_args();

        if (parent::isDirty($attributes)) {
            return true;
        }

        foreach ($this->translationCache as $translation) {
            if ($translation->isDirty($attributes)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Delete the model from the database with its translations.
     *
     * @return bool|null
     */
    public function delete(): ?bool
    {
        if ($deleted = parent::delete()) {
            $this->translations()->delete();
        }

        return $deleted;
    }

    /**
     * Set the locale.
     *
     * @param string $locale
     *
     * @return void
     */
    protected function setLocale(string $locale): void
    {
        Config::set('app.locale', $locale);
    }

    /**
     * Get the current locale.
     *
     * @return string
     */
    protected function getLocale(): string
    {
        return Config::get('app.locale');
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

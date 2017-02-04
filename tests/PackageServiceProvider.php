<?php

namespace Vinkla\Tests\Translator;

use Illuminate\Support\ServiceProvider;

class PackageServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(realpath(__DIR__.'/database/migrations'));
    }
}

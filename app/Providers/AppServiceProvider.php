<?php

namespace App\Providers;

use App\Translation\EnglishKeyTranslator;
use Illuminate\Contracts\Translation\Translator as TranslatorContract;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->extend('translator', function (TranslatorContract $translator, $app) {
            return new EnglishKeyTranslator(
                $app['translation.loader'],
                $translator->getLocale()
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register observers
        \App\Models\License::observe(\App\Observers\LicenseObserver::class);
    }
}

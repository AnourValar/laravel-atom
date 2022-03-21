<?php

namespace AnourValar\LaravelAtom\Providers;

use Illuminate\Support\ServiceProvider;

class LaravelAtomServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(\AnourValar\LaravelAtom\Service::class, function ($app)
        {
            return new \AnourValar\LaravelAtom\Service(new \AnourValar\LaravelAtom\Registry());
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // config
        $this->mergeConfigFrom(__DIR__.'/../resources/config/atom.php', 'atom');
        $this->publishes([ __DIR__.'/../resources/config/atom.php' => config_path('atom.php')], 'config');

        // langs
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang/', 'laravel-atom');
        $this->publishes([__DIR__.'/../resources/lang/' => lang_path('vendor/laravel-atom')]);

        // migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}

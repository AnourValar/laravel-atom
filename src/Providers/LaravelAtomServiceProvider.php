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

        // migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}

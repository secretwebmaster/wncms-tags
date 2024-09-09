<?php

namespace Wncms\Tags;

use Illuminate\Support\ServiceProvider;

class TagsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/wncms-tags.php', 'wncms-tags');
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/wncms-tags.php' => config_path('wncms-tags.php'),
                __DIR__ . '/../migrations/' => database_path('migrations'),
            ], 'wncms-tags');
        }
    }
}
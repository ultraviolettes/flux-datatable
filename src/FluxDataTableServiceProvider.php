<?php

namespace Ultraviolettes\FluxDataTable;

use Illuminate\Support\ServiceProvider;

class FluxDataTableServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'flux-datatable');
        $this->publishes([
            __DIR__.'/../config/flux-datatable.php' => config_path('flux-datatable.php'),
        ], 'config');
        // ...
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/flux-datatable.php', 'flux-datatable');
    }
}

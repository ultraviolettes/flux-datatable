<?php

namespace Ultraviolettes\FluxDataTable;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use Livewire\Livewire;
use Ultraviolettes\FluxDataTable\Http\Livewire\FluxDataTable as FluxDataTableComponent;

class FluxDataTableServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'flux-datatable');

        $this->publishes([
            __DIR__.'/../config/flux-datatable.php' => config_path('flux-datatable.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/flux-datatable'),
        ], 'views');

        $this->registerLivewireComponents();
    }

    protected function registerLivewireComponents(): self
    {
        $this->callAfterResolving(BladeCompiler::class, function () {
            Livewire::component('flux-datatable', FluxDataTableComponent::class);
        });

        return $this;
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/flux-datatable.php', 'flux-datatable');

        // Register the main class to use with the facade
        $this->app->bind('flux-datatable', function () {
            return new FluxDataTable;
        });
    }
}

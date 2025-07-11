<?php

namespace Ultraviolettes\FluxDataTable;

use Illuminate\View\Compilers\BladeCompiler;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Ultraviolettes\FluxDataTable\Components\Widget;
use Ultraviolettes\FluxDataTable\Livewire\FluxDataTable as FluxDataTableComponent;

class FluxDataTableServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('flux-datatable')
            ->hasViews('flux-datatable')
            ->hasViewComponents('flux-datatable', Widget::class)
            ->hasConfigFile('flux-datatable');

        $this->registerLivewireComponents();
    }

    protected function registerLivewireComponents(): self
    {
        $this->callAfterResolving(BladeCompiler::class, function () {
            Livewire::component('flux-datatable', FluxDataTableComponent::class);
        });

        return $this;
    }
}

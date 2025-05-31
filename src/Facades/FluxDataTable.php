<?php

namespace Ultraviolettes\FluxDataTable\Facades;

use Illuminate\Support\Facades\Facade;

class FluxDataTable extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'flux-datatable';
    }
}

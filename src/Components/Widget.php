<?php

namespace Ultraviolettes\FluxDataTable\Components;

use Illuminate\View\View;

class Widget
{
    public function render(): View
    {
        return view('flux-datatable::components.widget');

    }
}

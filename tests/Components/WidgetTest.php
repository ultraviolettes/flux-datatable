<?php

use Illuminate\Support\Number;
use Ultraviolettes\FluxDataTable\DataObject\WidgetDataObject;

it('flux datatable widget render with no error', function () {
    $view = $this->blade(
        '<x-flux-datatable::widget :data="$widget" />',
        ['widget' => new WidgetDataObject(label: 'Super', value: 'valeur')]
    );

    $view->assertSee('valeur');
});

it('flux datatable widget render with currency', function () {
    $view = $this->blade(
        '<x-flux-datatable::widget :data="$widget" />',
        ['widget' => new WidgetDataObject(label: 'Super', value: '10')->isCurrency()]
    );

    $view->assertSee(Number::currency(10));
});

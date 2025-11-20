<?php

namespace Ultraviolettes\FluxDataTable\Tests\Fixtures;

use Illuminate\Database\Eloquent\Builder;
use Ultraviolettes\FluxDataTable\Livewire\FluxDataTable;

class TestTable extends FluxDataTable
{
    public function columns(): array
    {
        return [
            [
                'label' => 'Name',
                'field' => 'name',
                'sortable' => true,
                'searchable' => true,
            ],
        ];
    }

    public function builder(): Builder
    {
        return Item::query();
    }
}

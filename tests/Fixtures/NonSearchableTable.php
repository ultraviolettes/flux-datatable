<?php

namespace Ultraviolettes\FluxDataTable\Tests\Fixtures;

use Illuminate\Database\Eloquent\Builder;
use Ultraviolettes\FluxDataTable\Livewire\FluxDataTable;

/**
 * Fixture used to assert that the search input is hidden when no column
 * is marked as searchable.
 */
class NonSearchableTable extends FluxDataTable
{
    public function columns(): array
    {
        return [
            [
                'label' => 'Name',
                'field' => 'name',
            ],
        ];
    }

    public function builder(): Builder
    {
        return Item::query();
    }
}

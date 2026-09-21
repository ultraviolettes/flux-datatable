<?php

namespace Ultraviolettes\FluxDataTable\Tests\Fixtures;

use Illuminate\Database\Eloquent\Builder;
use Ultraviolettes\FluxDataTable\Livewire\FluxDataTable;

/**
 * Fixture used to assert that subclass-level defaults for $usePagination
 * and $useViewMode survive the call to config() during mount().
 */
class OverriddenConfigTable extends FluxDataTable
{
    public bool $usePagination = false;

    public bool $useViewMode = true;

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

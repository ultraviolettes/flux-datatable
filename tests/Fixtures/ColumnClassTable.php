<?php

namespace Ultraviolettes\FluxDataTable\Tests\Fixtures;

use Illuminate\Database\Eloquent\Builder;
use Ultraviolettes\FluxDataTable\Livewire\FluxDataTable;

/**
 * Fixture exercising the column `class` option in its two supported forms :
 * - static string (legacy behavior, applied to both header and cells)
 * - Closure(Model): ?string (per-row, cell-only)
 */
class ColumnClassTable extends FluxDataTable
{
    public function columns(): array
    {
        return [
            [
                'label' => 'Static',
                'field' => 'name',
                'render' => fn ($row) => $row->name,
                'class' => 'static-class',
            ],
            [
                'label' => 'Dyn',
                'field' => 'dyn',
                'render' => fn ($row) => $row->name,
                'class' => fn ($row) => $row->id % 2 === 0 ? 'even-row' : 'odd-row',
            ],
            [
                'label' => 'Maybe',
                'field' => 'maybe',
                'render' => fn ($row) => '-',
                'class' => fn ($row) => $row->id === 1 ? 'first-only' : null,
            ],
        ];
    }

    public function builder(): Builder
    {
        return Item::query()->orderBy('id');
    }
}

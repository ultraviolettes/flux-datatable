<?php

namespace Ultraviolettes\FluxDataTable\Tests\Fixtures;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Ultraviolettes\FluxDataTable\Livewire\FluxDataTable;

/**
 * Fixture exercising the `rowAttributes()` hook : the whole row is clickable,
 * like a file explorer opening a folder.
 */
class RowAttributesTable extends FluxDataTable
{
    public function columns(): array
    {
        return [
            [
                'label' => 'Name',
                'field' => 'name',
                'render' => fn ($row) => $row->name,
            ],
        ];
    }

    public function rowAttributes(Model $row): array
    {
        return [
            'wire:click' => "open({$row->id})",
            'class' => 'cursor-pointer',
            'data-row-id' => (string) $row->id,
        ];
    }

    public function builder(): Builder
    {
        return Item::query()->orderBy('id');
    }
}

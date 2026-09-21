<?php

namespace Ultraviolettes\FluxDataTable\Tests\Fixtures;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Ultraviolettes\FluxDataTable\BulkAction;
use Ultraviolettes\FluxDataTable\Livewire\FluxDataTable;

class DuplicateBulkActionTable extends FluxDataTable
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

    public function bulkActions(): Collection
    {
        return collect([
            BulkAction::make('delete')->label('Delete')->action(fn () => null),
            BulkAction::make('delete')->label('Remove')->action(fn () => null),
        ]);
    }
}

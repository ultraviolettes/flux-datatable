<?php

namespace Ultraviolettes\FluxDataTable\Tests\Fixtures;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Ultraviolettes\FluxDataTable\BulkAction;
use Ultraviolettes\FluxDataTable\Livewire\FluxDataTable;

class BulkActionTable extends FluxDataTable
{
    public static array $applied = [];

    public function columns(): array
    {
        return [
            [
                'label' => 'Name',
                'field' => 'name',
                'searchable' => true,
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
            BulkAction::make('Archive')->action(fn (array $ids) => static::$applied = $ids),
            BulkAction::make('Move')
                ->icon('folder-arrow-down')
                ->disabledWhen(fn (array $ids) => in_array((string) Item::query()->where('name', 'Charlie')->value('id'), $ids, true)
                    ? 'Charlie cannot be moved.'
                    : null)
                ->action(fn (array $ids) => static::$applied = $ids),
            BulkAction::make('Delete')
                ->variant('danger')
                ->requiresConfirmation()
                ->confirmationText('Deleted items stay visible in existing quotes.')
                ->action(fn (array $ids) => static::$applied = $ids),
        ]);
    }
}

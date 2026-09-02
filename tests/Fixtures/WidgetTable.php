<?php

namespace Ultraviolettes\FluxDataTable\Tests\Fixtures;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Ultraviolettes\FluxDataTable\DataObject\WidgetDataObject;
use Ultraviolettes\FluxDataTable\Filters\SelectFilter;
use Ultraviolettes\FluxDataTable\Livewire\FluxDataTable;

/**
 * Fixture d'un tableau dont le widget d'en-tête agrège `price` en dérivant de
 * `filteredQuery()` : le total doit toujours porter exactement sur les lignes
 * affichées (filtres + recherche, toutes pages confondues).
 */
class WidgetTable extends FluxDataTable
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
            [
                'label' => 'Price',
                'field' => 'price',
            ],
        ];
    }

    public function filters(): array
    {
        return [
            'category_id' => SelectFilter::make('Category', 'category_id')
                ->options([1 => 'One', 2 => 'Two']),
        ];
    }

    /**
     * @return Collection<WidgetDataObject>
     */
    #[Computed]
    public function headerWidgets(): Collection
    {
        return collect([
            new WidgetDataObject(label: 'Total', value: (string) $this->total()),
        ]);
    }

    public function total(): int
    {
        return (int) $this->widgetQuery()->sum('price');
    }

    public function builder(): Builder
    {
        return Item::query();
    }
}

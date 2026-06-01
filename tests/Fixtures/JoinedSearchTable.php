<?php

namespace Ultraviolettes\FluxDataTable\Tests\Fixtures;

use Illuminate\Database\Eloquent\Builder;
use Ultraviolettes\FluxDataTable\Livewire\FluxDataTable;

/**
 * Fixture dont le builder joint une table (`categories`) qui possède aussi une
 * colonne `name`, afin de reproduire le cas où une recherche non qualifiée sur
 * `name` serait ambiguë. Expose le SQL généré par la recherche pour pouvoir
 * l'asserter sans exécuter la requête (le driver de test SQLite ne gère pas
 * `ilike`).
 */
class JoinedSearchTable extends FluxDataTable
{
    /** @var array<int, string> */
    public array $fields = ['name'];

    public function columns(): array
    {
        return collect($this->fields)
            ->map(fn (string $field) => [
                'label' => $field,
                'field' => $field,
                'searchable' => true,
            ])
            ->all();
    }

    public function builder(): Builder
    {
        return Item::query()
            ->leftJoin('categories', 'categories.id', '=', 'items.category_id');
    }

    public function searchSql(): string
    {
        return $this->applySearch($this->builder())->toSql();
    }
}

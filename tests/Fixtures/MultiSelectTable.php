<?php

namespace Ultraviolettes\FluxDataTable\Tests\Fixtures;

use Illuminate\Database\Eloquent\Builder;
use Ultraviolettes\FluxDataTable\Filters\MultiSelectFilter;
use Ultraviolettes\FluxDataTable\Livewire\FluxDataTable;

/**
 * Fixture exerçant `MultiSelectFilter` : quatre catégories, dont la dernière est
 * écartée par la valeur par défaut — le cas « tout sauf les annulées ».
 *
 * Les propriétés publiques permettent aux tests de faire varier options,
 * valeur par défaut et `query()` sans multiplier les fixtures.
 */
class MultiSelectTable extends FluxDataTable
{
    /** @var array<int|string, string> */
    public array $filterOptions = [
        1 => 'One',
        2 => 'Two',
        3 => 'Three',
        4 => 'Four',
    ];

    /** @var array<int, int|string> */
    public array $filterDefault = [1, 2, 3];

    public bool $useCustomQuery = false;

    /** @var array<int, mixed>|null Valeurs reçues par le `query()` personnalisé. */
    public static ?array $queryCallbackValues = null;

    public function mount(
        array $perPageOptions = [],
        array $actions = [],
        string $viewMode = 'table',
        ?array $filterOptions = null,
        ?array $filterDefault = null,
        bool $useCustomQuery = false,
    ): void {
        $this->filterOptions = $filterOptions ?? $this->filterOptions;
        $this->filterDefault = $filterDefault ?? $this->filterDefault;
        $this->useCustomQuery = $useCustomQuery;

        parent::mount($perPageOptions, $actions, $viewMode);
    }

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

    public function filters(): array
    {
        $filter = MultiSelectFilter::make('Category', 'category_id')
            ->options($this->filterOptions);

        if ($this->filterDefault !== []) {
            $filter->defaultValue($this->filterDefault);
        }

        if ($this->useCustomQuery) {
            $filter->query(function ($query, $values) {
                static::$queryCallbackValues = $values;

                return $query->whereIn('category_id', $values);
            });
        }

        return ['category_id' => $filter];
    }

    public function builder(): Builder
    {
        return Item::query();
    }
}

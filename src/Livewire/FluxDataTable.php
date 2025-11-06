<?php

namespace Ultraviolettes\FluxDataTable\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Ultraviolettes\FluxDataTable\BulkAction;
use Ultraviolettes\FluxDataTable\DataObject\WidgetDataObject;
use Ultraviolettes\FluxDataTable\Traits\WithConfig;

class FluxDataTable extends Component
{
    use WithConfig, WithPagination;

    public array $perPageOptions = [];

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 'name')]
    public ?string $sortBy = 'name';

    #[Url(except: 'asc')]
    public string $sortDirection = 'asc';

    public int $perPage = 10;

    public array $filters = [];

    public array $actions = [];

    public array $selected = [];

    public string $bulkActionLabel = '';

    public bool $selectAll = false;

    public string $viewMode = 'table';

    /**
     * Fields to search when using the search input.
     * Default is ['name'] for backward compatibility.
     */
    public array $searchableFields = [];

    protected $updatesQueryString = ['search', 'sortBy', 'sortDirection', 'page', 'perPage', 'viewMode', 'filters'];

    public function mount(
        array $perPageOptions = [],
        array $actions = [],
        string $viewMode = 'table'
    ): void {
        $this->perPageOptions = empty($perPageOptions) ? config('flux-datatable.per_page', [10, 25, 50, 100]) : $perPageOptions;
        $this->perPage = $this->perPageOptions[0] ?? 10;
        $this->actions = $actions;
        $this->viewMode = $viewMode;

        $searchableFields = collect($this->columns())
            ->filter(fn (array $col) => isset($col['searchable']) && $col['searchable']);

        if ($searchableFields->isNotEmpty()) {
            $this->searchableFields = $searchableFields->pluck('field')->toArray();
        }

        $this->config();
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function executeAction(string $actionName, $rowId): void
    {
        if (isset($this->actions[$actionName]) && is_callable($this->actions[$actionName])) {
            $this->actions[$actionName]($rowId);
        }
    }

    public function toggleSelectAll(): void
    {
        $this->selectAll = ! $this->selectAll;

        if ($this->selectAll) {
            $this->selected = $this->getRecordIds();
        } else {
            $this->selected = [];
        }
    }

    public function toggleSelect($rowId): void
    {
        if (in_array($rowId, $this->selected)) {
            $this->selected = array_diff($this->selected, [$rowId]);
            $this->selectAll = false;
        } else {
            $this->selected[] = $rowId;
            if (count($this->selected) === count($this->getRecordIds())) {
                $this->selectAll = true;
            }
        }
    }

    public function setViewMode(string $mode): void
    {
        if (in_array($mode, ['table', 'card'])) {
            $this->viewMode = $mode;
        }
    }

    protected function getRecordIds(): array
    {
        return $this->records()->pluck('id')->toArray();
    }

    #[Computed]
    public function records(): LengthAwarePaginator
    {
        // Handle different types of data sources
        return $this->builder()
            ->tap(fn ($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
            ->tap(fn ($query) => $this->applyFilters($query))
            ->when($this->search, function ($query) {
                collect(explode(' ', $this->search))
                    ->filter()
                    ->each(function ($term) use ($query) {
                        $query->where(function ($subQuery) use ($term) {
                            foreach ($this->searchableFields as $field) {

                                if( Str::contains($field, '.')){
                                    // Chercher sur la relation
                                    $relation = Str::before($field, '.');
                                    $relationField = Str::after($field, '.');
                                    $subQuery->orWhereHas($relation, function ($subQuery) use ($relationField, $term) {
                                        $subQuery->where($relationField, 'ilike', "%{$term}%");
                                    });
                                } else {
                                    $subQuery->orWhere($field, 'ilike', "%{$term}%");
                                }

                            }
                        });
                    });
            })
            ->paginate($this->perPage);
    }

    public function render(): View
    {

        return view('flux-datatable::livewire.table', [ // @phpstan-ignore-line
            'columns' => $this->columns(),
            'tableFilters' => $this->filters(),
            'bulkActions' => $this->bulkActions(),
        ]);
    }

    public function columns(): array
    {
        throw new \BadMethodCallException('Child class must implement the columns method.');
    }

    /**
     * Define the filters for the datatable.
     * Child classes should override this method to define filters.
     */
    public function filters(): array
    {
        return [];
    }

    /**
     * Define the bulkAction for the datatable.
     * Child classes should override this method to define filters.
     *
     * @return Collection<BulkAction>
     */
    public function bulkActions(): Collection
    {
        return collect();
    }

    public function bulkActionLabel(string $bulkActionLabel): self
    {
        $this->bulkActionLabel = $bulkActionLabel;

        return $this;
    }

    public function setBulkActionLabel(string $bulkActionLabel): void
    {
        $this->bulkActionLabel = $bulkActionLabel;
    }

    public function executeBulkAction(string $actionName): void
    {
        if ($action = $this->bulkActions()->firstWhere('slug', $actionName)) {
            $action->apply($this->selected);
        }
    }

    /**
     * @return Collection<WidgetDataObject>
     */
    #[Computed]
    public function headerWidgets(): Collection
    {
        return collect();
    }

    /**
     * Reset all filters to their default values.
     */
    public function resetFilters(): void
    {
        $this->filters = [];
        $this->resetPage();
    }

    /**
     * Apply filters to the query.
     */
    protected function applyFilters(Builder $query): Builder
    {
        foreach ($this->filters() as $field => $filter) {
            if (isset($this->filters[$field]) && $this->filters[$field] !== '') {
                $query = $filter->apply($query, $this->filters[$field]);
            }
        }

        return $query;
    }

    /**
     * Define the base query. Child classes should override this.
     *
     * @throws \BadMethodCallException
     */
    public function builder(): Builder
    {
        throw new \BadMethodCallException('Child class must implement the getBaseQuery method.');
    }
}

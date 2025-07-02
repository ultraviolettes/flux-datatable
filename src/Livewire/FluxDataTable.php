<?php

namespace Ultraviolettes\FluxDataTable\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class FluxDataTable extends Component
{
    use WithPagination;

    public array $perPageOptions = [];

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 'name')]
    public ?string $sortBy = 'name';

    #[Url(except: 'asc')]
    public string $sortDirection = 'asc';

    public int $perPage = 10;

    public array $actions = [];

    public array $bulkActions = [];

    public array $selected = [];

    public bool $selectAll = false;

    public string $viewMode = 'table';

    public array $filters = [];

    protected $updatesQueryString = ['search', 'sortBy', 'sortDirection', 'page', 'perPage', 'viewMode', 'filters'];

    public function mount(array $perPageOptions = [], array $actions = [], array $bulkActions = [], string $viewMode = 'table'): void
    {
        $this->perPageOptions = $perPageOptions ?? config('flux-datatable.per_page', [10, 25, 50, 100]);
        $this->perPage = $this->perPageOptions[0] ?? 10;
        $this->actions = $actions;
        $this->bulkActions = $bulkActions;
        $this->viewMode = $viewMode;
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

    public function executeBulkAction(string $actionName): void
    {
        if (isset($this->bulkActions[$actionName]) && is_callable($this->bulkActions[$actionName])) {
            $this->bulkActions[$actionName]($this->selected);
            $this->selected = [];
            $this->selectAll = false;
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
        return $this->records->pluck('id')->toArray();
    }

    #[Computed]
    public function records()
    {
        // Handle different types of data sources
        $query = $this->builder()
            ->tap(fn ($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
            ->when($this->search, fn ($query) => $query->where('name', 'like', "%{$this->search}%"))
            ->tap(fn ($query) => $this->applyFilters($query));

        return $query->paginate($this->perPage);
    }

    public function render(): View
    {
        // Get Flux UI configuration
        $fluxUiConfig = config('flux-datatable.flux_ui', [
            'use_container' => true,
            'use_pagination' => true,
            'use_empty_state' => true,
        ]);

        return view('flux-datatable::livewire.table', [
            'fluxUiConfig' => $fluxUiConfig,
            'columns' => $this->columns(),
            'tableFilters' => $this->filters(),
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

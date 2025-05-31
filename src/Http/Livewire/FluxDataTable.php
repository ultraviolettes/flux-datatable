<?php

namespace Ultraviolettes\FluxDataTable\Http\Livewire;

use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class FluxDataTable extends Component
{
    use WithPagination;

    public array $columns = [];

    public mixed $data = [];

    public array $perPageOptions = [];

    public string $search = '';

    public string $sortBy = '';

    public string $sortDirection = 'asc';

    public int $perPage = 10;

    public array $actions = [];

    public array $bulkActions = [];

    public array $selected = [];

    public bool $selectAll = false;

    public string $viewMode = 'table';

    protected $updatesQueryString = ['search', 'sortBy', 'sortDirection', 'page', 'perPage', 'viewMode'];

    public function mount(array $columns, $data, array $perPageOptions = [], array $actions = [], array $bulkActions = [], string $viewMode = 'table')
    {
        $this->columns = $columns;
        $this->data = $data;
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
        $query = $this->getBaseQuery();

        // Apply pagination
        return $query->paginate($this->perPage);
    }

    public function render()
    {
        // Get Flux UI configuration
        $fluxUiConfig = config('flux-datatable.flux_ui', [
            'use_container' => true,
            'use_pagination' => true,
            'use_empty_state' => true,
        ]);

        return view('flux-datatable::livewire.table', [
            'fluxUiConfig' => $fluxUiConfig,
        ]);
    }

    /**
     * @return array|\Illuminate\Database\Eloquent\Builder|mixed
     */
    public function getBaseQuery(): mixed
    {
        if ($this->data instanceof \Illuminate\Database\Eloquent\Builder) {
            $query = $this->data;
        } elseif ($this->data instanceof \Illuminate\Pagination\Paginator || $this->data instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $query = $this->data->getCollection()->toQuery();
        } elseif ($this->data instanceof Collection) {
            $query = $this->data->toQuery();
        } else {
            $query = collect($this->data)->toQuery();
        }

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                foreach ($this->columns as $col) {
                    if (isset($col['field']) && isset($col['searchable']) && $col['searchable'] !== false) {
                        $q->orWhere($col['field'], 'like', '%'.$this->search.'%');
                    }
                }
            });
        }

        // Apply sorting using tap function
        return $query->tap(fn ($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query);
    }
}

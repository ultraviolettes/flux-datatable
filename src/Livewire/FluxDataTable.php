<?php

namespace Ultraviolettes\FluxDataTable\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
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

    #[Url(except: 10)]
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

        // Logique de persistence (Priorité : URL > Cache (User) > Session)
        if (! request()->query('perPage')) {
            $this->restorePerPagePreference();
        }

        // Validation : on s'assure que la valeur est valide
        if (! in_array($this->perPage, $this->perPageOptions)) {
            $this->perPage = $this->perPageOptions[0] ?? 10;
        }

        $this->actions = $actions;
        $this->viewMode = $viewMode;

        $searchableFields = collect($this->columns())
            ->filter(fn (array $col) => isset($col['searchable']) && $col['searchable']);

        if ($searchableFields->isNotEmpty()) {
            $this->searchableFields = $searchableFields->pluck('field')->toArray();
        }

        // Filter default value
        foreach ($this->filters() as $filter) {
            if ($filter->defaultValue) {
                $this->filters[$filter->getField()] = $filter->defaultValue;
            }
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

    public function updatedPerPage($value): void
    {
        $this->savePerPagePreference($value);
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
            ->tap(fn ($query) => $this->applySearch($query))
            ->paginate($this->perPage);
    }

    /**
     * Applique la recherche plein-texte sur les colonnes `searchable`.
     *
     * Chaque terme (séparé par une espace) doit matcher au moins une colonne
     * searchable (AND entre les termes, OR entre les colonnes).
     */
    protected function applySearch($query)
    {
        return $query->when($this->search, function ($query) {
            collect(explode(' ', $this->search))
                ->filter()
                ->each(function ($term) use ($query) {
                    $query->where(function ($subQuery) use ($term) {
                        foreach ($this->searchableFields as $field) {
                            $this->applySearchTerm($subQuery, $field, $term);
                        }
                    });
                });
        });
    }

    /**
     * Ajoute la condition de recherche d'un terme sur un champ donné.
     *
     * Un champ pointé (`relation.colonne`) n'est traité comme une relation
     * (`whereHas`) que si le préfixe est réellement une relation du modèle.
     * Sinon il est considéré comme une colonne déjà qualifiée (`table.colonne`).
     * Les colonnes directes sont qualifiées avec la table du modèle pour rester
     * non ambiguës lorsque le builder contient des jointures (ex. un `leftJoin`
     * vers une table possédant elle aussi une colonne `name`).
     */
    protected function applySearchTerm($query, string $field, string $term): void
    {
        if (Str::contains($field, '.') && $this->fieldTargetsRelation($query, Str::before($field, '.'))) {
            $relation = Str::before($field, '.');
            $relationField = Str::after($field, '.');

            $query->orWhereHas($relation, function ($relationQuery) use ($relationField, $term) {
                $relationQuery->where($this->qualifySearchColumn($relationQuery, $relationField), 'ilike', "%{$term}%");
            });

            return;
        }

        $query->orWhere($this->qualifySearchColumn($query, $field), 'ilike', "%{$term}%");
    }

    /**
     * Détermine si le préfixe d'un champ pointé correspond à une relation Eloquent.
     */
    protected function fieldTargetsRelation($query, string $relation): bool
    {
        return method_exists($query, 'getModel') && $query->getModel()->isRelation($relation);
    }

    /**
     * Qualifie une colonne avec la table du modèle quand c'est possible (builder
     * Eloquent), pour éviter les références ambiguës en présence de jointures.
     * Une colonne déjà qualifiée (`table.colonne`) est laissée intacte.
     */
    protected function qualifySearchColumn($query, string $field): string
    {
        return method_exists($query, 'qualifyColumn') ? $query->qualifyColumn($field) : $field;
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

    public function removeFilter($filterField): void
    {
        unset($this->filters[$filterField]);
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function restorePerPagePreference(): void
    {
        $key = $this->getPerPageKey();

        // 1. Si utilisateur connecté, on regarde dans le cache permanent
        if (Auth::check() && Cache::has($key)) {
            $this->perPage = (int) Cache::get($key);

            return;
        }

        // 2. Sinon (ou si pas de cache), on regarde la session (invités)
        if (session()->has($key)) {
            $this->perPage = (int) session()->get($key);
        }
    }

    protected function savePerPagePreference($value): void
    {
        $key = $this->getPerPageKey();

        // 1. Toujours en session (pour la navigation immédiate)
        session()->put($key, $value);

        // 2. Si utilisateur connecté, on sauvegarde "pour toujours" dans le cache
        if (Auth::check()) {
            Cache::forever($key, $value);
        }
    }

    /**
     * Generate a unique key based on table name AND user ID if authenticated.
     */
    protected function getPerPageKey(): string
    {
        $name = static::class;
        $suffix = Auth::check() ? '_user_'.Auth::id() : '_guest';

        return 'flux_datatable_per_page_'.md5($name).$suffix;
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

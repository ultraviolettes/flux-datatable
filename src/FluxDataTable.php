<?php

namespace Ultraviolettes\FluxDataTable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class FluxDataTable
{
    protected array $columns = [];

    protected $data;

    protected array $perPageOptions;

    protected array $actions = [];

    protected array $bulkActions = [];

    public function __construct()
    {
        $this->perPageOptions = config('flux-datatable.per_page', [10, 25, 50, 100]);
    }

    /**
     * Set the columns for the data table.
     *
     * @param  array  $columns  Array of column definitions with 'field' and 'label' keys
     * @return $this
     */
    public function columns(array $columns): self
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Set the data source for the data table.
     *
     * @param  mixed  $data  Collection, array, or Eloquent Builder
     * @return $this
     */
    public function data($data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Set the per-page options for pagination.
     *
     * @param  array  $options  Array of integers representing page size options
     * @return $this
     */
    public function perPageOptions(array $options): self
    {
        $this->perPageOptions = $options;

        return $this;
    }

    /**
     * Set the actions for the data table.
     *
     * @param  array  $actions  Array of actions with name as key and callback as value
     * @return $this
     */
    public function actions(array $actions): self
    {
        $this->actions = $actions;

        return $this;
    }

    /**
     * Set the bulk actions for the data table.
     *
     * @param  array  $actions  Array of bulk actions with name as key and callback as value
     * @return $this
     */
    public function bulkActions(array $actions): self
    {
        $this->bulkActions = $actions;

        return $this;
    }

    /**
     * Render the data table as a Livewire component.
     *
     * @return mixed
     */
    public function render()
    {
        return app()->make('livewire')->mount('flux-datatable::table', [
            'columns' => $this->columns,
            'data' => $this->data,
            'perPageOptions' => $this->perPageOptions,
            'actions' => $this->actions,
            'bulkActions' => $this->bulkActions,
        ]);
    }

    /**
     * Convert the data table to HTML.
     */
    public function toHtml(): string
    {
        return $this->render();
    }

    /**
     * Magic method to convert the data table to a string.
     */
    public function __toString(): string
    {
        return $this->toHtml();
    }
}

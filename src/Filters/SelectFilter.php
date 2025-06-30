<?php

namespace Ultraviolettes\FluxDataTable\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\ComponentAttributeBag;
use Illuminate\View\View;

class SelectFilter extends Filter
{
    protected array $options = [];
    protected ?\Closure $queryCallback = null; // Callback pour la query personnalisée

    /**
     * Set the options for the select filter
     *
     * @param array $options
     * @return $this
     */
    public function options(array $options): self
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Get the options for the select filter
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Render the filter
     *
     * @return View
     */
    public function render(): View
    {
        return view('flux-datatable::filters.select', [
            'name' => $this->name,
            'field' => $this->field,
            'options' => $this->options,
        ]);
    }

    /**
     * Set a custom query callback for the filter.
     *
     * @param callable $callback
     * @return $this
     */
    public function query(callable $callback): self
    {
        $this->queryCallback = $callback;
        return $this;
    }


    /**
     * Apply the filter to the query
     *
     * @param Builder $query
     * @param mixed $value
     * @return Builder
     */
    public function apply($query, $value)
    {
        if (empty($value)) {
            return $query;
        }

        // Si une callback est définie via `query()`, appelez-la
        if ($this->queryCallback) {
            return call_user_func($this->queryCallback, $query, $value);
        }

        return $query->where($this->field, $value);
    }

    /**
     * Create a new SelectFilter instance
     *
     * @param string $name
     * @param string $field
     * @return static
     */
    public static function make(string $name, string $field): self
    {
        return new static($name, $field);
    }
}

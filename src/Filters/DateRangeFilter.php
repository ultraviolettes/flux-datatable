<?php

namespace Ultraviolettes\FluxDataTable\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class DateRangeFilter extends Filter
{
    protected ?\Closure $queryCallback = null;
    protected array $config = [];

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
     * Set configuration options for the date range picker.
     *
     * @param array $config
     * @return $this
     */
    public function config(array $config): self
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Render the filter
     *
     * @return View
     */
    public function render(): View
    {
        return view('flux-datatable::filters.date-range', [
            'name' => $this->name,
            'field' => $this->field,
            'config' => $this->config,
        ]);
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
        if (empty($value) || !is_array($value)) {
            return $query;
        }

        // If a callback is defined via `query()`, call it
        if ($this->queryCallback) {
            return call_user_func($this->queryCallback, $query, $value);
        }

        // Default behavior: filter by date range
        if (isset($value[0]) && isset($value[1])) {
            return $query->whereBetween($this->field, [$value[0], $value[1]]);
        }

        return $query;
    }

    /**
     * Create a new DateRangeFilter instance
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

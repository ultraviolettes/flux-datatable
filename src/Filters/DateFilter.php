<?php

namespace Ultraviolettes\FluxDataTable\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

final class DateFilter extends Filter
{
    protected ?\Closure $queryCallback = null;

    /**
     * Set a custom query callback for the filter.
     *
     * @return $this
     */
    public function query(callable $callback): self
    {
        $this->queryCallback = $callback;

        return $this;
    }

    /**
     * Render the filter
     */
    public function render(): View
    {
        return view('flux-datatable::filters.date', [  // @phpstan-ignore-line
            'name' => $this->name,
            'field' => $this->field,
        ]);
    }

    /**
     * Apply the filter to the query
     *
     * @param  Builder  $query
     * @param  mixed  $value
     * @return Builder
     */
    public function apply($query, $value)
    {
        if (empty($value)) {
            return $query;
        }

        // If a callback is defined via `query()`, call it
        if ($this->queryCallback) {
            return call_user_func($this->queryCallback, $query, $value);
        }

        // Default behavior: filter by date
        return $query->whereDate($this->field, $value);
    }

    /**
     * Create a new DateFilter instance
     *
     * @return static
     */
    public static function make(string $name, string $field): self
    {
        return new self($name, $field);
    }
}

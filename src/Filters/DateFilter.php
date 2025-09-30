<?php

namespace Ultraviolettes\FluxDataTable\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

final class DateFilter extends Filter
{
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
     */
    public function apply($query, $value): Builder
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

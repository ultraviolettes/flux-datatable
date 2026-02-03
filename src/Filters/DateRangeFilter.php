<?php

namespace Ultraviolettes\FluxDataTable\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

final class DateRangeFilter extends Filter
{
    protected array $config = [];

    /**
     * Set configuration options for the date range picker.
     *
     * @return $this
     */
    public function config(array $config): self
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Render the filter
     */
    public function render(): View
    {
        return view('flux-datatable::filters.date-range', [ // @phpstan-ignore-line
            'name' => $this->name,
            'field' => $this->field,
            'config' => $this->config,
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
        if (empty($value) || ! is_array($value)) {
            return $query;
        }

        // If a callback is defined via `query()`, call it
        if ($this->queryCallback) {
            return call_user_func($this->queryCallback, $query, $value);
        }

        // Default behavior: filter by date range
        return $query->whereBetween($this->field, $value);
    }

    public function getKeyLabel($key): string
    {
        return implode(' - ', $key);
    }

    /**
     * Create a new DateRangeFilter instance
     *
     * @return static
     */
    public static function make(string $name, string $field): self
    {
        return new self($name, $field);
    }
}

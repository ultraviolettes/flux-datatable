<?php

namespace Ultraviolettes\FluxDataTable\Filters;

use Illuminate\Database\Eloquent\Builder;

abstract class Filter
{
    protected string $name;

    protected string $field;

    protected ?\Closure $queryCallback = null; // Callback pour la query personnalisée

    protected array $options = [];

    public function __construct(string $name, string $field)
    {
        $this->name = $name;
        $this->field = $field;
    }


    public function getName(): string
    {
        return $this->name;
    }

    public function getField(): string
    {
        return $this->field;
    }

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
     * Apply the filter to the query
     *
     * @param  Builder  $query
     * @param  mixed  $value
     * @return Builder
     */
    public function apply($query, $value): Builder
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

    abstract public function render();

    /**
     * Create a new SelectFilter instance
     *
     * @return static
     */
    abstract static function make(string $name, string $field): self;

}

<?php

namespace Ultraviolettes\FluxDataTable\Filters;

use Illuminate\Database\Eloquent\Builder;
use Ultraviolettes\FluxDataTable\Filters\Filter;

final class RadioFilter extends Filter
{

    protected array $options = [];

    public ?string $variant = null;

    /**
     * Set the options for the select filter
     *
     * @return $this
     */
    public function options(array $options): self
    {
        $this->options = $options;

        return $this;
    }
    
    public function variant(string $variant): self
    {
        $this->variant = $variant;

        return $this;
    }


    /**
     * Get the options for the select filter
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function render()
    {
        return view('flux-datatable::filters.radio', [ // @phpstan-ignore-line
            'name' => $this->name,
            'field' => $this->field,
            'options' => $this->options,
            'variant' => $this->variant,
        ]);
    }

    /**
     * Create a new SelectFilter instance
     *
     * @return static
     */
    public static function make(string $name, string $field): self
    {
        return new self($name, $field);
    }
}

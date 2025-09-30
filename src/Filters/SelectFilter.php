<?php

namespace Ultraviolettes\FluxDataTable\Filters;

use Illuminate\View\View;

final class SelectFilter extends Filter
{
    protected array $options = [];

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

    /**
     * Get the options for the select filter
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Render the filter
     */
    public function render(): View
    {
        return view('flux-datatable::filters.select', [ // @phpstan-ignore-line
            'name' => $this->name,
            'field' => $this->field,
            'options' => $this->options,
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

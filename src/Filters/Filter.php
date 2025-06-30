<?php

namespace Ultraviolettes\FluxDataTable\Filters;

abstract class Filter
{
    protected string $name;
    protected string $field;
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

    abstract public function render();

    abstract public function apply($query, $value);
}

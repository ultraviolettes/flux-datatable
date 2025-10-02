<?php

namespace Ultraviolettes\FluxDataTable;

use Illuminate\Support\Str;

class BulkAction
{
    public bool $requiresConfirmation = false;

    public string $slug;

    public ?\Closure $callback = null;

    public string $confirmationIcon = 'exclamation-triangle';

    public ?string $icon = null;

    public function __construct(public string $label)
    {
        $this->slug = Str::slug($label);
    }

    public static function make(string $label): self
    {
        return new self($label);
    }

    public function action(\Closure $callback): self
    {
        $this->callback = $callback;

        return $this;
    }

    public function apply($selected): void
    {
        call_user_func($this->callback, $selected);
    }

    public function requiresConfirmation(): self
    {
        $this->requiresConfirmation = true;

        return $this;

    }

    public function icon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function confirmationIcon(string $confirmationIcon): self
    {
        $this->confirmationIcon = $confirmationIcon;

        return $this;
    }
}

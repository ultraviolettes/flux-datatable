<?php

namespace Ultraviolettes\FluxDataTable\DataObject;

use Illuminate\Support\Number;

class WidgetDataObject
{
    public function __construct(
        public string $label,
        public string $value {
            get {
                return $this->isCurrency ? Number::currency((float) $this->value) : $this->value;
            }
        },
        public bool $isCurrency = false,
    ) {}

    public function isCurrency(bool $isCurrency = true): self
    {
        $this->isCurrency = $isCurrency;
        return $this;
    }
}

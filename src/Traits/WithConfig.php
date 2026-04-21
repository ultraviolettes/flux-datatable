<?php

namespace Ultraviolettes\FluxDataTable\Traits;

trait WithConfig
{
    public bool $usePagination;

    public bool $useViewMode;

    public function config(): void
    {
        // Only hydrate from config when the subclass (or rehydrated Livewire state)
        // has not already assigned a value. A typed property without a default is
        // "uninitialized" → isset() returns false.
        if (! isset($this->usePagination)) {
            $this->usePagination = config('flux-datatable.flux_ui.use_pagination', true);
        }

        if (! isset($this->useViewMode)) {
            $this->useViewMode = config('flux-datatable.flux_ui.use_view_mode', false);
        }

        if ($this->bulkActionLabel === '') {
            $this->setBulkActionLabel(__('flux-datatable::flux-datatable.bulk_action_label'));
        }
    }

    public function setUsePagination(bool $value): void
    {
        $this->usePagination = $value;
    }

    public function setUseViewMode(bool $value): void
    {
        $this->useViewMode = $value;
    }
}

<?php

namespace Ultraviolettes\FluxDataTable\Traits;

trait WithConfig
{
    public bool $usePagination = true;

    public bool $useViewMode = false;

    public function config(): void
    {
        $this->setUsePagination(config('flux-datatable.flux_ui.use_pagination', true));
        $this->setUseViewMode(config('flux-datatable.flux_ui.use_view_mode', false));
        $this->setBulkActionLabel(__('flux-datatable::flux-datatable.bulk_action_label'));
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

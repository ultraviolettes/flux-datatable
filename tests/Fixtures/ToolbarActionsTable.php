<?php

namespace Ultraviolettes\FluxDataTable\Tests\Fixtures;

use Illuminate\Contracts\Support\Htmlable;

class ToolbarActionsTable extends TestTable
{
    public int $foldersCreated = 0;

    public function toolbarActions(): ?Htmlable
    {
        return view()->file(__DIR__.'/views/toolbar-actions.blade.php');
    }

    public function createFolder(): void
    {
        $this->foldersCreated++;
    }
}

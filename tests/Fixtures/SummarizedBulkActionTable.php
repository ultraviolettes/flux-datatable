<?php

namespace Ultraviolettes\FluxDataTable\Tests\Fixtures;

/**
 * Un consommateur qui écrit lui-même le récapitulatif de la sélection, dans le
 * vocabulaire de son domaine.
 */
class SummarizedBulkActionTable extends BulkActionTable
{
    public function selectionSummary(array $selected): ?string
    {
        return $selected === [] ? null : count($selected).' files selected · 7 parts';
    }

    public function selectionHint(array $selected): ?string
    {
        return $selected === [] ? null : 'Move only applies to files';
    }
}

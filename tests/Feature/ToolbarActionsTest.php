<?php

use Livewire\Livewire;
use Ultraviolettes\FluxDataTable\Tests\Fixtures\TestTable;
use Ultraviolettes\FluxDataTable\Tests\Fixtures\ToolbarActionsTable;

function toolbar(string $html): DOMXPath
{
    $dom = new DOMDocument;
    // Les balises Flux (`ui-select`…) sont inconnues de libxml : on tait ses
    // avertissements, l'arbre reste exploitable.
    @$dom->loadHTML('<?xml encoding="utf-8"?>'.$html, LIBXML_NOERROR);

    return new DOMXPath($dom);
}

it('renders no toolbar actions slot when the table defines none', function () {
    Livewire::test(TestTable::class)
        ->assertDontSeeHtml('data-flux-datatable-toolbar-actions');
});

it('renders the consumer actions in the toolbar, before the per-page select', function () {
    $dom = toolbar(Livewire::test(ToolbarActionsTable::class)->html());

    $right = $dom->query('//div[@data-flux-datatable-toolbar]/div[2]')->item(0);
    $children = array_values(array_filter(
        iterator_to_array($right->childNodes),
        fn (DOMNode $node) => $node instanceof DOMElement
    ));

    // Blade compile la vue du consommateur : la balise Flux devient un bouton.
    expect($children[0]->getAttribute('data-flux-datatable-toolbar-actions'))->not->toBeNull()
        ->and($dom->query('.//button[contains(., "New folder")]', $children[0])->length)->toBe(1)
        ->and($dom->query('descendant-or-self::select', $children[1])->length)->toBeGreaterThan(0);
});

it('calls the table method from a toolbar action', function () {
    Livewire::test(ToolbarActionsTable::class)
        ->assertSeeHtml('wire:click="createFolder"')
        ->call('createFolder')
        ->assertSet('foldersCreated', 1);
});

it('lets the toolbar wrap instead of overflowing in a narrow column', function () {
    $dom = toolbar(Livewire::test(ToolbarActionsTable::class)->html());

    foreach (['//div[@data-flux-datatable-toolbar]', '//div[@data-flux-datatable-toolbar]/div'] as $query) {
        foreach ($dom->query($query) as $node) {
            expect($node->getAttribute('class'))->toContain('flex-wrap');
        }
    }
});

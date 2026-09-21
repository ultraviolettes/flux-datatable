<?php

use Livewire\Livewire;
use Ultraviolettes\FluxDataTable\Tests\Fixtures\BulkActionTable;
use Ultraviolettes\FluxDataTable\Tests\Fixtures\Item;

beforeEach(function () {
    Item::query()->delete();
    BulkActionTable::$applied = [];

    foreach (['Alpha', 'Bravo', 'Charlie'] as $name) {
        Item::query()->create(['name' => $name]);
    }
});

it('binds the checkbox group to $selected, so the header "select all" reaches the server', function () {
    // `flux:checkbox.all` coche les cases par programme : seul un `wire:model`
    // sur le groupe remonte ce changement. Un `wire:click` par ligne ne part pas.
    $html = Livewire::test(BulkActionTable::class)->html();

    expect($html)->toMatch('/<ui-checkbox-group\b[^>]*wire:model\.live(\.self)?="selected"/')
        ->and($html)->not->toContain('toggleSelect(');
});

it('gives each row checkbox its record id as value', function () {
    $ids = Item::query()->orderBy('id')->pluck('id');

    $html = Livewire::test(BulkActionTable::class)->html();

    foreach ($ids as $id) {
        expect($html)->toMatch('/<ui-checkbox\b[^>]*value="'.$id.'"/');
    }
});

it('enables the bulk action button once rows are selected', function () {
    $bulkButton = fn (string $html) => preg_match('/<ui-dropdown\b[^>]*>\s*(<button\b[^>]*>)/', $html, $m) ? $m[1] : '';

    $component = Livewire::test(BulkActionTable::class);

    expect($bulkButton($component->html()))->toMatch('/\sdisabled[\s=>]/');

    $ids = Item::query()->pluck('id')->map(fn ($id) => (string) $id)->all();

    $component->set('selected', $ids);

    expect($bulkButton($component->html()))->not->toBe('')
        ->and($bulkButton($component->html()))->not->toMatch('/\sdisabled[\s=>]/');

    $component->call('executeBulkAction', 'archive');

    expect(BulkActionTable::$applied)->toEqualCanonicalizing($ids);
});

it('drops selected rows that are no longer displayed', function () {
    $alpha = Item::query()->where('name', 'Alpha')->first();
    $bravo = (string) Item::query()->where('name', 'Bravo')->value('id');

    $component = Livewire::test(BulkActionTable::class)
        ->set('selected', [(string) $alpha->id, $bravo]);

    $alpha->delete();

    $component->call('$refresh')
        ->assertSet('selected', [$bravo])
        ->call('executeBulkAction', 'archive');

    expect(BulkActionTable::$applied)->toBe([$bravo]);
});

it('limits the selection to the current page', function () {
    $pageOne = Item::query()->orderBy('name')->limit(2)->pluck('id')->map(fn ($id) => (string) $id)->all();

    Livewire::test(BulkActionTable::class)
        ->set('perPage', 2)
        ->set('selected', $pageOne)
        ->assertSet('selected', $pageOne)
        ->call('gotoPage', 2)
        ->assertSet('selected', []);
});

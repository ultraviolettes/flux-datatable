<?php

use Livewire\Livewire;
use Ultraviolettes\FluxDataTable\Tests\Fixtures\Item;
use Ultraviolettes\FluxDataTable\Tests\Fixtures\RowAttributesTable;
use Ultraviolettes\FluxDataTable\Tests\Fixtures\TestTable;

beforeEach(function () {
    Item::query()->delete();
    Item::query()->create(['name' => 'Alpha']);
    Item::query()->create(['name' => 'Bravo']);
});

it('renders the attributes returned by rowAttributes() on the row', function () {
    Livewire::test(RowAttributesTable::class)
        ->assertSeeHtml('wire:click="open(1)"')
        ->assertSeeHtml('wire:click="open(2)"');
});

it('renders the attributes on each row, resolved per row', function () {
    $html = Livewire::test(RowAttributesTable::class)->html();

    expect(substr_count($html, 'data-row-id="1"'))->toBe(1)
        ->and(substr_count($html, 'data-row-id="2"'))->toBe(1)
        ->and(substr_count($html, 'class="cursor-pointer"'))->toBe(2);
});

it('puts the attributes on the <tr> itself, not on a child', function () {
    // Le hook n'a d'intérêt que si les attributs atterrissent sur la ligne :
    // c'est ce qui rend la ligne entière cliquable.
    $html = Livewire::test(RowAttributesTable::class)->html();

    preg_match_all('/<tr\b[^>]*>/', $html, $matches);

    $clickableRows = array_filter(
        $matches[0],
        fn (string $tr) => str_contains($tr, 'wire:click="open(')
            && str_contains($tr, 'cursor-pointer')
            && str_contains($tr, 'data-row-id=')
    );

    expect($clickableRows)->toHaveCount(2);
});

it('adds no attribute when rowAttributes() is not overridden', function () {
    $html = Livewire::test(TestTable::class)->html();

    expect($html)->not->toContain('data-row-id')
        ->and($html)->not->toContain('wire:click="open(');
});

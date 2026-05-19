<?php

use Livewire\Livewire;
use Ultraviolettes\FluxDataTable\Tests\Fixtures\ColumnClassTable;
use Ultraviolettes\FluxDataTable\Tests\Fixtures\Item;

beforeEach(function () {
    Item::query()->delete();
    Item::query()->create(['name' => 'Alpha']);
    Item::query()->create(['name' => 'Bravo']);
    Item::query()->create(['name' => 'Charlie']);
});

it('applies a string class on cells (legacy behavior)', function () {
    Livewire::test(ColumnClassTable::class)
        ->assertSee('static-class', false);
});

it('resolves a callable class against each row on cells', function () {
    // id=1 → odd, id=2 → even, id=3 → odd
    $html = Livewire::test(ColumnClassTable::class)->html();

    expect(substr_count($html, 'odd-row'))->toBe(2);
    expect(substr_count($html, 'even-row'))->toBe(1);
});

it('does not emit a class when the callable returns null for a given row', function () {
    // class `fn ($row) => $row->id === 1 ? 'first-only' : null` :
    // only the first row should carry the class.
    $html = Livewire::test(ColumnClassTable::class)->html();

    expect(substr_count($html, 'first-only'))->toBe(1);
});

it('does not apply the callable class on the header (no row context)', function () {
    // The header for column 2 ("Dyn") must not emit 'odd-row' / 'even-row'
    // (the column header iteration cannot resolve a row-dependent callable).
    // We assert by counting cell-side occurrences match exactly the row count.
    $html = Livewire::test(ColumnClassTable::class)->html();

    // 3 rows total ; odd_row twice + even_row once = 3 occurrences only.
    // If the header also rendered the callable, it would add an extra one.
    expect(substr_count($html, 'odd-row') + substr_count($html, 'even-row'))->toBe(3);
});

it('still applies a string class on the header (legacy behavior)', function () {
    // 'static-class' should appear on both the <th> and 3 <td> = 4 occurrences.
    $html = Livewire::test(ColumnClassTable::class)->html();

    expect(substr_count($html, 'static-class'))->toBe(4);
});

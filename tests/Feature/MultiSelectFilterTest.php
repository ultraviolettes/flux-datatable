<?php

use Livewire\Livewire;
use Ultraviolettes\FluxDataTable\Filters\MultiSelectFilter;
use Ultraviolettes\FluxDataTable\Filters\SelectFilter;
use Ultraviolettes\FluxDataTable\Tests\Fixtures\Item;
use Ultraviolettes\FluxDataTable\Tests\Fixtures\MultiSelectTable;

beforeEach(function () {
    Item::query()->delete();
    MultiSelectTable::$queryCallbackValues = null;

    Item::query()->create(['name' => 'Alpha', 'category_id' => 1]);
    Item::query()->create(['name' => 'Bravo', 'category_id' => 2]);
    Item::query()->create(['name' => 'Charlie', 'category_id' => 3]);
    Item::query()->create(['name' => 'Delta', 'category_id' => 4]);
});

/** Noms des lignes affichées, triés pour ne pas dépendre de l'ordre. */
function displayedNames(MultiSelectTable $table): array
{
    return $table->records()->pluck('name')->sort()->values()->all();
}

it('returns the union of the selected values with a default whereIn', function () {
    $table = Livewire::test(MultiSelectTable::class)
        ->set('filters', ['category_id' => [1, 3]])
        ->instance();

    expect(displayedNames($table))->toBe(['Alpha', 'Charlie']);
});

it('behaves like a single-value select when one value is selected', function () {
    $table = Livewire::test(MultiSelectTable::class)
        ->set('filters', ['category_id' => [2]])
        ->instance();

    expect(displayedNames($table))->toBe(['Bravo']);
});

it('applies no filter at all when the selection is emptied', function () {
    // `empty([])` court-circuite `apply()` : le tableau montre toutes les lignes,
    // sans erreur. Comportement volontaire, ne pas « réparer ».
    $table = Livewire::test(MultiSelectTable::class)
        ->set('filters', ['category_id' => []])
        ->instance();

    expect(displayedNames($table))->toBe(['Alpha', 'Bravo', 'Charlie', 'Delta']);
});

it('sets the default value on mount and applies it on the first render', function () {
    $table = Livewire::test(MultiSelectTable::class)->instance();

    expect($table->filters)->toBe(['category_id' => [1, 2, 3]])
        ->and(displayedNames($table))->toBe(['Alpha', 'Bravo', 'Charlie']);
});

it('ignores an empty default value', function () {
    $table = Livewire::test(MultiSelectTable::class, ['filterDefault' => []])->instance();

    expect($table->filters)->toBe([])
        ->and(displayedNames($table))->toBe(['Alpha', 'Bravo', 'Charlie', 'Delta']);
});

it('passes the array to a custom query callback', function () {
    $table = Livewire::test(MultiSelectTable::class, ['useCustomQuery' => true])
        ->set('filters', ['category_id' => [2, 4]])
        ->instance();

    expect(displayedNames($table))->toBe(['Bravo', 'Delta'])
        ->and(MultiSelectTable::$queryCallbackValues)->toBe([2, 4]);
});

it('passes an array to the callback even for a single selected value', function () {
    Livewire::test(MultiSelectTable::class, ['useCustomQuery' => true])
        ->set('filters', ['category_id' => [2]]);

    expect(MultiSelectTable::$queryCallbackValues)->toBe([2]);
});

it('renders the pill without a TypeError for one, several and almost all values', function ($selection, $expected) {
    Livewire::test(MultiSelectTable::class)
        ->set('filters', ['category_id' => $selection])
        ->assertSee('Category : '.$expected, false);
})->with([
    'one value' => [[2], 'Two'],
    'two values' => [[1, 2], 'One, Two'],
    'almost all' => [[1, 2, 3], 'all except Four'],
    'all' => [[1, 2, 3, 4], 'all'],
]);

it('falls back to a count when neither the selection nor the gap is short', function () {
    $options = array_combine(range(1, 10), array_map(fn ($i) => "Option {$i}", range(1, 10)));

    Livewire::test(MultiSelectTable::class, ['filterOptions' => $options, 'filterDefault' => []])
        ->set('filters', ['category_id' => [1, 2, 3, 4, 5]])
        ->assertSee('Category : 5 selected', false);
});

it('renders no pill when the selection is empty', function () {
    Livewire::test(MultiSelectTable::class)
        ->set('filters', ['category_id' => []])
        ->assertDontSee('Category :', false);
});

it('drops the whole filter when the pill is closed', function () {
    // La valeur par défaut n'est posée qu'au mount : fermer la pill réaffiche
    // bien toutes les lignes, y compris celles que le défaut écartait.
    $component = Livewire::test(MultiSelectTable::class)
        ->call('removeFilter', 'category_id');

    expect($component->instance()->filters)->toBe([])
        ->and(displayedNames($component->instance()))->toBe(['Alpha', 'Bravo', 'Charlie', 'Delta']);
});

it('keeps a key as its own label when no option matches', function () {
    $filter = MultiSelectFilter::make('Category', 'category_id')->options([1 => 'One']);

    expect($filter->getKeyLabel('99'))->toBe('99');
});

it('leaves single-value filters untouched by getPillLabel', function () {
    $filter = SelectFilter::make('Category', 'category_id')
        ->options([1 => 'One', 2 => 'Two']);

    expect($filter->getPillLabel(2))->toBe('Two');
});

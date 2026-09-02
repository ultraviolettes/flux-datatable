<?php

use Livewire\Livewire;
use Ultraviolettes\FluxDataTable\Tests\Fixtures\Item;
use Ultraviolettes\FluxDataTable\Tests\Fixtures\WidgetTable;

beforeEach(function () {
    Item::query()->delete();
    Item::query()->create(['name' => 'Alpha', 'price' => 100, 'category_id' => 1]);
    Item::query()->create(['name' => 'Bravo', 'price' => 200, 'category_id' => 1]);
    Item::query()->create(['name' => 'Charlie', 'price' => 400, 'category_id' => 2]);
});

it('reflects an active filter in an aggregate built on filteredQuery', function () {
    $table = Livewire::test(WidgetTable::class)
        ->set('filters', ['category_id' => 1])
        ->instance();

    expect($table->total())->toBe(300);
});

it('reflects a search term in the query used by aggregates', function () {
    // Le driver de test (SQLite) ne gère pas `ilike` : on asserte sur le SQL
    // plutôt que d'exécuter l'agrégat.
    $table = new WidgetTable;
    $table->searchableFields = ['name'];
    $table->search = 'Alpha';

    expect($table->filteredQuery()->toSql())->toContain('ilike');
});

it('carries filters and search together', function () {
    $table = new WidgetTable;
    $table->searchableFields = ['name'];
    $table->search = 'Alpha';
    $table->filters = ['category_id' => 1];

    expect($table->filteredQuery()->toSql())
        ->toContain('"category_id" = ?')
        ->toContain('ilike');
});

it('aggregates every page, not just the current one', function () {
    $table = Livewire::test(WidgetTable::class)
        ->set('perPage', 1)
        ->instance();

    // La page affichée ne contient qu'une ligne, le total porte sur les trois.
    expect($table->records()->count())->toBe(1)
        ->and($table->total())->toBe(700);
});

it('applies no sort and no pagination to filteredQuery', function () {
    $table = new WidgetTable;
    $table->sortBy = 'name';

    expect($table->filteredQuery()->toSql())
        ->not->toContain('order by')
        ->not->toContain('limit');
});

it('returns a fresh query on each call so aggregates cannot pollute each other', function () {
    $table = new WidgetTable;

    $first = $table->filteredQuery();
    $first->where('name', 'Alpha');

    $second = $table->filteredQuery();

    expect($second)->not->toBe($first)
        ->and($second->toSql())->not->toContain('"name" = ?');
});

it('keeps records() consistent with the widget query', function () {
    $table = Livewire::test(WidgetTable::class)
        ->set('filters', ['category_id' => 1])
        ->instance();

    expect($table->records()->pluck('price')->sum())->toBe($table->total());
});

it('still sorts, filters and paginates records()', function () {
    $component = Livewire::test(WidgetTable::class)
        ->set('perPage', 2)
        ->set('sortBy', 'price')
        ->set('sortDirection', 'desc');

    $records = $component->instance()->records();

    expect($records->total())->toBe(3)
        ->and($records->count())->toBe(2)
        ->and($records->pluck('name')->all())->toBe(['Charlie', 'Bravo']);

    $filtered = $component->set('filters', ['category_id' => 2])->instance()->records();

    expect($filtered->total())->toBe(1)
        ->and($filtered->first()->name)->toBe('Charlie');
});

it('renders a header widget consistent with the filtered table', function () {
    Livewire::test(WidgetTable::class)
        ->assertSee('700')
        ->set('filters', ['category_id' => 1])
        ->assertSee('300')
        ->assertDontSee('700');
});

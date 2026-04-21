<?php

use Livewire\Livewire;
use Ultraviolettes\FluxDataTable\Tests\Fixtures\NonSearchableTable;
use Ultraviolettes\FluxDataTable\Tests\Fixtures\TestTable;

it('affiche le champ de recherche quand au moins une colonne est searchable', function () {
    Livewire::test(TestTable::class)
        ->assertSee('wire:model.live.debounce.500ms="search"', false);
});

it('renseigne searchableFields quand au moins une colonne est searchable', function () {
    Livewire::test(TestTable::class)
        ->assertSet('searchableFields', ['name']);
});

it('masque le champ de recherche quand aucune colonne nest searchable', function () {
    Livewire::test(NonSearchableTable::class)
        ->assertDontSee('wire:model.live.debounce.500ms="search"', false);
});

it('laisse searchableFields vide quand aucune colonne nest searchable', function () {
    Livewire::test(NonSearchableTable::class)
        ->assertSet('searchableFields', []);
});

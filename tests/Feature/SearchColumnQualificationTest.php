<?php

use Ultraviolettes\FluxDataTable\Tests\Fixtures\JoinedSearchTable;

it('qualifies a direct search column with the model table to stay unambiguous on joins', function () {
    $table = new JoinedSearchTable;
    $table->search = 'foo';
    $table->searchableFields = ['name'];

    // Qualifié avec la table du modèle → pas d'ambiguïté avec categories.name
    expect($table->searchSql())
        ->toContain('"items"."name"')
        ->not->toContain('where ("name"');
});

it('leaves an explicitly qualified, non-relation column untouched', function () {
    $table = new JoinedSearchTable;
    $table->search = 'foo';
    $table->searchableFields = ['categories.name'];

    // `categories` n'est pas une relation du modèle Item → colonne littérale,
    // pas de whereHas (donc pas de sous-requête `exists`).
    expect($table->searchSql())
        ->toContain('"categories"."name"')
        ->not->toContain('exists');
});

it('searches through a real relation with whereHas', function () {
    $table = new JoinedSearchTable;
    $table->search = 'foo';
    $table->searchableFields = ['category.name'];

    // `category` est une vraie relation Eloquent → whereHas (sous-requête exists).
    expect($table->searchSql())->toContain('exists');
});

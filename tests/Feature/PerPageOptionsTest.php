<?php

use Illuminate\Auth\GenericUser;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;
use Ultraviolettes\FluxDataTable\Tests\Fixtures\Item;
use Ultraviolettes\FluxDataTable\Tests\Fixtures\TestTable;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    // Seed a bunch of items so pagination has an effect
    // Sous SQLite, truncate() peut tenter de nettoyer sqlite_sequence et échouer.
    // On utilise delete() pour éviter l'erreur `no such table: sqlite_sequence`.
    Item::query()->delete();
    for ($i = 1; $i <= 30; $i++) {
        Item::query()->create(['name' => sprintf('Item %02d', $i)]);
    }
});

it('utilise les perPageOptions par défaut depuis la config quand aucun paramètre nest fourni', function () {
    Config::set('flux-datatable.per_page', [7, 14, 21]);

    Livewire::test(TestTable::class)
        ->assertSet('perPageOptions', [7, 14, 21]);
});

it('accepte des perPageOptions personnalisés passés au composant', function () {
    Livewire::test(TestTable::class, [
        'perPageOptions' => [5, 10, 20],
    ])->assertSet('perPageOptions', [5, 10, 20]);
});

it('valide la valeur perPage et fait un fallback sur la première option si invalide', function () {
    // Par défaut, le composant a perPage = 10, on fournit des options qui ne contiennent pas 10
    Livewire::test(TestTable::class, [
        'perPageOptions' => [25, 50],
    ])->assertSet('perPage', 25);
});

it('applique la pagination selon la valeur de perPage', function () {
    Livewire::test(TestTable::class, [
        'perPageOptions' => [5, 10, 20],
    ])
        ->set('perPage', 5)
        ->call('$refresh')
        ->assertSet('perPage', 5)
        // Vérifie que la première page contient bien 5 éléments via les noms visibles
        ->assertSeeInOrder(['Item 01', 'Item 02', 'Item 03', 'Item 04', 'Item 05'])
        ->assertDontSee('Item 06');
});

it('met à jour perPage et conserve la valeur', function () {
    Livewire::test(TestTable::class, [
        'perPageOptions' => [5, 10, 20],
    ])
        ->set('perPage', 10)
        ->assertSet('perPage', 10);
});

it('restaure la préférence perPage depuis la session pour un invité', function () {
    $key = 'flux_datatable_per_page_'.md5(TestTable::class).'_guest';
    Session::put($key, 25);

    Livewire::test(TestTable::class, [
        'perPageOptions' => [10, 25, 50],
    ])->assertSet('perPage', 25);
});

it('restaure la préférence perPage depuis le cache pour un utilisateur authentifié', function () {
    $user = new GenericUser(['id' => 123]);
    actingAs($user);

    $key = 'flux_datatable_per_page_'.md5(TestTable::class).'_user_123';
    Cache::forever($key, 50);

    Livewire::test(TestTable::class, [
        'perPageOptions' => [10, 25, 50],
    ])->assertSet('perPage', 50);
});

it('la query string perPage a priorité sur cache et session', function () {
    $user = new GenericUser(['id' => 456]);
    actingAs($user);

    $guestKey = 'flux_datatable_per_page_'.md5(TestTable::class).'_guest';
    Session::put($guestKey, 25);

    $key = 'flux_datatable_per_page_'.md5(TestTable::class).'_user_456';
    Cache::forever($key, 50);

    Livewire::withQueryParams(['perPage' => 20])
        ->test(TestTable::class, [
            'perPageOptions' => [10, 20, 50],
        ])
        ->assertSet('perPage', 20);
});

it('update perPage persiste en session (invité) et en cache (auth)', function () {
    // invité
    $guestKey = 'flux_datatable_per_page_'.md5(TestTable::class).'_guest';
    Livewire::test(TestTable::class, [
        'perPageOptions' => [10, 25],
    ])
        ->set('perPage', 25);

    expect(session()->get($guestKey))->toBe(25);

    // utilisateur
    $user = new GenericUser(['id' => 789]);
    actingAs($user);

    $userKey = 'flux_datatable_per_page_'.md5(TestTable::class).'_user_789';
    Livewire::test(TestTable::class, [
        'perPageOptions' => [10, 25],
    ])->set('perPage', 25);

    expect(Cache::get($userKey))->toBe(25);
});

<?php

use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use Ultraviolettes\FluxDataTable\Tests\Fixtures\OverriddenConfigTable;
use Ultraviolettes\FluxDataTable\Tests\Fixtures\TestTable;

it('utilise la valeur de config pour usePagination quand la sous-classe ne la redéfinit pas', function () {
    Config::set('flux-datatable.flux_ui.use_pagination', false);

    Livewire::test(TestTable::class)
        ->assertSet('usePagination', false);
});

it('utilise la valeur de config pour useViewMode quand la sous-classe ne la redéfinit pas', function () {
    Config::set('flux-datatable.flux_ui.use_view_mode', true);

    Livewire::test(TestTable::class)
        ->assertSet('useViewMode', true);
});

it('préserve la valeur de usePagination définie dans la sous-classe même si la config dit autre chose', function () {
    Config::set('flux-datatable.flux_ui.use_pagination', true);

    Livewire::test(OverriddenConfigTable::class)
        ->assertSet('usePagination', false);
});

it('préserve la valeur de useViewMode définie dans la sous-classe même si la config dit autre chose', function () {
    Config::set('flux-datatable.flux_ui.use_view_mode', false);

    Livewire::test(OverriddenConfigTable::class)
        ->assertSet('useViewMode', true);
});

it('préserve bulkActionLabel défini dans la sous-classe', function () {
    Livewire::test(OverriddenConfigTable::class)
        ->assertSet('bulkActionLabel', 'Subclass label');
});

it('remplit bulkActionLabel depuis les traductions quand la sous-classe ne le définit pas', function () {
    Livewire::test(TestTable::class)
        ->assertSet('bulkActionLabel', __('flux-datatable::flux-datatable.bulk_action_label'));
});

it('préserve la valeur de usePagination après une requête Livewire (rehydration)', function () {
    Config::set('flux-datatable.flux_ui.use_pagination', true);

    Livewire::test(OverriddenConfigTable::class)
        ->assertSet('usePagination', false)
        ->call('$refresh')
        ->assertSet('usePagination', false);
});

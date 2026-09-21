<?php

use Livewire\Livewire;
use Ultraviolettes\FluxDataTable\BulkAction;
use Ultraviolettes\FluxDataTable\Tests\Fixtures\BulkActionTable;
use Ultraviolettes\FluxDataTable\Tests\Fixtures\DuplicateBulkActionTable;
use Ultraviolettes\FluxDataTable\Tests\Fixtures\Item;

beforeEach(function () {
    Item::query()->delete();
    BulkActionTable::$applied = [];

    foreach (['Alpha', 'Bravo'] as $name) {
        Item::query()->create(['name' => $name]);
    }
});

it('runs the action by its name, whatever its label', function () {
    $ids = Item::query()->pluck('id')->map(fn ($id) => (string) $id)->all();

    // Libellé « Move to a folder », nom `move`.
    Livewire::test(BulkActionTable::class)
        ->set('selected', $ids)
        ->call('executeBulkAction', 'move');

    expect(BulkActionTable::$applied)->toEqualCanonicalizing($ids);
});

it('keeps the name when the label changes', function () {
    $action = BulkAction::make('delete')->label('Supprimer');

    expect($action->label('Supprimer définitivement')->name)->toBe('delete');
});

it('keeps the name when the label is translated', function () {
    $nameIn = function (string $locale) {
        app()->setLocale($locale);

        return BulkAction::make('confirm')->label(__('flux-datatable::flux-datatable.confirm'));
    };

    $en = $nameIn('en');
    $fr = $nameIn('fr');

    expect($en->label)->not->toBe($fr->label)
        ->and($en->name)->toBe('confirm')
        ->and($fr->name)->toBe('confirm');
});

it('derives a default label from the name', function () {
    expect(BulkAction::make('move-to-folder')->label)->toBe('Move To Folder')
        ->and(BulkAction::make('archive')->label)->toBe('Archive');
});

it('rejects a name that would not survive the wire:click or the modal name', function (string $name) {
    BulkAction::make($name);
})->throws(InvalidArgumentException::class)->with([
    'a space' => 'move to folder',
    'a quote' => "it's",
    'an accent' => 'déplacer',
    'empty' => '',
]);

it('fails loudly on an unknown action name instead of doing nothing', function () {
    $ids = Item::query()->pluck('id')->map(fn ($id) => (string) $id)->all();

    Livewire::test(BulkActionTable::class)
        ->set('selected', $ids)
        ->call('executeBulkAction', 'move-to-a-folder');
})->throws(InvalidArgumentException::class, 'Unknown bulk action [move-to-a-folder]');

it('refuses two bulk actions with the same name', function () {
    try {
        Livewire::test(DuplicateBulkActionTable::class);
    } catch (Throwable $e) {
        // L'exception remonte enveloppée dans une ViewException par le rendu.
        while (! $e instanceof LogicException && $e->getPrevious()) {
            $e = $e->getPrevious();
        }

        expect($e)->toBeInstanceOf(LogicException::class)
            ->and($e->getMessage())->toContain('duplicated: delete');

        return;
    }

    $this->fail('Two bulk actions named [delete] were accepted.');
});

it('scopes confirmation modal names to the component, so two tables on a page do not collide', function () {
    $first = Livewire::test(BulkActionTable::class);
    $second = Livewire::test(BulkActionTable::class);

    expect($first->id())->not->toBe($second->id());

    $first->assertSeeHtml('confirm-modal-'.$first->id().'-delete')
        ->assertDontSeeHtml('confirm-modal-'.$second->id().'-delete');

    $second->assertSeeHtml('confirm-modal-'.$second->id().'-delete');
});

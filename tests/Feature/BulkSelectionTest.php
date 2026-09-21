<?php

use Livewire\Livewire;
use Ultraviolettes\FluxDataTable\Tests\Fixtures\BulkActionTable;
use Ultraviolettes\FluxDataTable\Tests\Fixtures\Item;

/**
 * La balise ouvrante du bouton d'action groupée portant ce libellé.
 */
function bulkButton(string $html, string $label): string
{
    preg_match_all('/<button\b[^>]*>(?:(?!<\/button>).)*<\/button>/s', $html, $matches);

    foreach ($matches[0] as $button) {
        if (preg_match('/>\s*'.preg_quote($label, '/').'\s*</', $button)) {
            preg_match('/^<button\b[^>]*>/', $button, $opening);

            return $opening[0];
        }
    }

    return '';
}

function isDisabled(string $openingTag): bool
{
    return (bool) preg_match('/\sdisabled[\s=>]/', $openingTag);
}

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

it('renders one always-visible button per bulk action, without a dropdown', function () {
    $html = Livewire::test(BulkActionTable::class)->html();

    expect(bulkButton($html, 'Archive'))->not->toBe('')
        ->and(bulkButton($html, 'Move to a folder'))->not->toBe('')
        ->and(bulkButton($html, 'Delete forever'))->not->toBe('')
        ->and($html)->not->toContain('<ui-dropdown');
});

it('disables every button, without tooltip, when nothing is selected', function () {
    $html = Livewire::test(BulkActionTable::class)->html();

    foreach (['Archive', 'Move to a folder', 'Delete forever'] as $label) {
        expect(isDisabled(bulkButton($html, $label)))->toBeTrue();
    }

    expect($html)->not->toContain('Charlie cannot be moved.')
        ->and($html)->not->toContain('<ui-tooltip');
});

it('enables the buttons once rows are selected and runs the action', function () {
    $ids = Item::query()->whereIn('name', ['Alpha', 'Bravo'])->pluck('id')->map(fn ($id) => (string) $id)->all();

    $component = Livewire::test(BulkActionTable::class)->set('selected', $ids);

    foreach (['Archive', 'Move to a folder', 'Delete forever'] as $label) {
        expect(isDisabled(bulkButton($component->html(), $label)))->toBeFalse();
    }

    $component->call('executeBulkAction', 'move');

    expect(BulkActionTable::$applied)->toEqualCanonicalizing($ids);
});

it('disables an action through disabledWhen and shows its reason', function () {
    $ids = Item::query()->pluck('id')->map(fn ($id) => (string) $id)->all();

    $html = Livewire::test(BulkActionTable::class)->set('selected', $ids)->html();

    expect(isDisabled(bulkButton($html, 'Move to a folder')))->toBeTrue()
        ->and(isDisabled(bulkButton($html, 'Archive')))->toBeFalse()
        ->and($html)->toMatch('/<ui-tooltip\b.*Charlie cannot be moved\./s');
});

it('refuses to run an action disabled by disabledWhen, even when called directly', function () {
    $ids = Item::query()->pluck('id')->map(fn ($id) => (string) $id)->all();

    Livewire::test(BulkActionTable::class)
        ->set('selected', $ids)
        ->call('executeBulkAction', 'move');

    expect(BulkActionTable::$applied)->toBe([]);
});

it('does not run an action on an empty selection', function () {
    Livewire::test(BulkActionTable::class)->call('executeBulkAction', 'archive');

    expect(BulkActionTable::$applied)->toBe([]);
});

it('applies the variant to the button', function () {
    $html = Livewire::test(BulkActionTable::class)->html();

    expect(bulkButton($html, 'Delete forever'))->toContain('data-flux-button')
        ->and(bulkButton($html, 'Delete forever'))->toContain('bg-red-500')
        ->and(bulkButton($html, 'Archive'))->not->toContain('bg-red-500');
});

it('shows the custom confirmation text in the confirmation modal', function () {
    $component = Livewire::test(BulkActionTable::class);

    $component->assertSeeHtml('confirm-modal-'.$component->id().'-delete')
        ->assertSee('Deleted items stay visible in existing quotes.')
        ->assertDontSeeHtml('confirm-modal-'.$component->id().'-archive');
});

it('summarises the selection next to the buttons', function () {
    $ids = Item::query()->pluck('id')->map(fn ($id) => (string) $id)->all();

    Livewire::test(BulkActionTable::class)
        ->assertSee(trans_choice('flux-datatable::flux-datatable.selection_summary', 0))
        ->set('selected', array_slice($ids, 0, 1))
        ->assertSee(trans_choice('flux-datatable::flux-datatable.selection_summary', 1))
        ->set('selected', $ids)
        ->assertSee(trans_choice('flux-datatable::flux-datatable.selection_summary', 3, ['count' => 3]));
});

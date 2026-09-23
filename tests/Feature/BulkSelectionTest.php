<?php

use Livewire\Livewire;
use Ultraviolettes\FluxDataTable\Tests\Fixtures\BulkActionTable;
use Ultraviolettes\FluxDataTable\Tests\Fixtures\Item;
use Ultraviolettes\FluxDataTable\Tests\Fixtures\SummarizedBulkActionTable;

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

function xpath(string $html): DOMXPath
{
    $dom = new DOMDocument;
    // Les balises Flux (`ui-checkbox`, `ui-modal`…) sont inconnues de libxml :
    // on tait ses avertissements, l'arbre reste exploitable.
    @$dom->loadHTML('<?xml encoding="utf-8"?>'.$html, LIBXML_NOERROR);

    return new DOMXPath($dom);
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

    expect(bulkButton($html, 'Archive (0)'))->not->toBe('')
        ->and(bulkButton($html, 'Move to a folder'))->not->toBe('')
        ->and(bulkButton($html, 'Delete forever'))->not->toBe('')
        ->and($html)->not->toContain('<ui-dropdown');
});

it('disables every button, without tooltip, when nothing is selected', function () {
    $html = Livewire::test(BulkActionTable::class)->html();

    foreach (['Archive (0)', 'Move to a folder', 'Delete forever'] as $label) {
        expect(isDisabled(bulkButton($html, $label)))->toBeTrue();
    }

    expect($html)->not->toContain('Charlie cannot be moved.')
        ->and($html)->not->toContain('<ui-tooltip');
});

it('enables the buttons once rows are selected and runs the action', function () {
    $ids = Item::query()->whereIn('name', ['Alpha', 'Bravo'])->pluck('id')->map(fn ($id) => (string) $id)->all();

    $component = Livewire::test(BulkActionTable::class)->set('selected', $ids);

    foreach (['Archive (2)', 'Move to a folder', 'Delete forever'] as $label) {
        expect(isDisabled(bulkButton($component->html(), $label)))->toBeFalse();
    }

    $component->call('executeBulkAction', 'move');

    expect(BulkActionTable::$applied)->toEqualCanonicalizing($ids);
});

it('disables an action through disabledWhen and shows its reason', function () {
    $ids = Item::query()->pluck('id')->map(fn ($id) => (string) $id)->all();

    $html = Livewire::test(BulkActionTable::class)->set('selected', $ids)->html();

    expect(isDisabled(bulkButton($html, 'Move to a folder')))->toBeTrue()
        ->and(isDisabled(bulkButton($html, 'Archive (3)')))->toBeFalse()
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

    expect(bulkButton($html, 'Archive (0)'))->toContain('bg-[var(--color-accent)]')
        ->and(bulkButton($html, 'Move to a folder'))->toContain('bg-white')
        ->and(bulkButton($html, 'Move to a folder'))->not->toContain('bg-[var(--color-accent)]');
});

it('renders a danger action as red text without background, not as a solid red button', function () {
    $html = Livewire::test(BulkActionTable::class)->html();

    expect(bulkButton($html, 'Delete forever'))->toContain('bg-transparent')
        ->and(bulkButton($html, 'Delete forever'))->toContain('--color-red-700')
        ->and(bulkButton($html, 'Delete forever'))->not->toContain('bg-red-500');
});

it('keeps a danger confirm button in the confirmation modal', function () {
    $component = Livewire::test(BulkActionTable::class);

    $confirm = xpath($component->html())->query(
        '//dialog[@data-modal="confirm-modal-'.$component->id().'-delete"]//button[contains(., "Confirm")]'
    );

    expect($confirm->length)->toBe(1)
        ->and($confirm->item(0)->getAttribute('class'))->toContain('bg-red-500');
});

it('switches the active banner to dark styles, but not its confirmation modals', function () {
    $ids = Item::query()->pluck('id')->map(fn ($id) => (string) $id)->all();
    $darkBanner = '//div[@data-flux-datatable-bulk-actions][contains(concat(" ", @class, " "), " dark ")]';

    $component = Livewire::test(BulkActionTable::class);

    expect(xpath($component->html())->query($darkBanner)->length)->toBe(0);

    $dom = xpath($component->set('selected', $ids)->html());

    // Les boutons du bandeau prennent leur rendu sombre ; la modale, rendue
    // hors du bandeau, garde le thème de la page.
    expect($dom->query($darkBanner)->length)->toBe(1)
        ->and($dom->query($darkBanner.'//dialog')->length)->toBe(0)
        ->and($dom->query('//dialog[@data-modal="confirm-modal-'.$component->id().'-delete"]')->length)->toBe(1);
});

it('evaluates a closure label with the current selection', function () {
    $ids = Item::query()->pluck('id')->map(fn ($id) => (string) $id)->all();

    $component = Livewire::test(BulkActionTable::class);

    expect(bulkButton($component->html(), 'Archive (0)'))->not->toBe('');

    expect(bulkButton($component->set('selected', array_slice($ids, 0, 2))->html(), 'Archive (2)'))->not->toBe('');
});

it('shows a scope note in the hint line without disabling the action', function () {
    $ids = Item::query()->whereIn('name', ['Alpha', 'Bravo'])->pluck('id')->map(fn ($id) => (string) $id)->all();

    $html = Livewire::test(BulkActionTable::class)->set('selected', $ids)->html();

    expect($html)->toMatch('/data-flux-datatable-selection-hint[^>]*>\s*Move only applies to files\s*</')
        ->and(isDisabled(bulkButton($html, 'Move to a folder')))->toBeFalse();
});

it('shows no scope note for an action that is disabled', function () {
    // Charlie grise « Move » : sa note de portée n'a plus d'objet.
    $ids = Item::query()->pluck('id')->map(fn ($id) => (string) $id)->all();

    Livewire::test(BulkActionTable::class)
        ->set('selected', $ids)
        ->assertDontSee('Move only applies to files');
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

/**
 * Le bandeau d'actions groupées, de sa balise ouvrante à la fin du tableau.
 */
function bulkBanner(string $html): string
{
    preg_match('/<div\b[^>]*data-flux-datatable-bulk-actions.*<\/table>/s', $html, $matches);

    return $matches[0] ?? '';
}

function bannerState(string $html): ?string
{
    preg_match('/<div\b[^>]*data-flux-datatable-bulk-actions[^>]*data-state="(\w+)"/', $html, $matches);

    return $matches[1] ?? null;
}

it('renders the banner at the head of the table, inside the checkbox group, above the scroll area', function () {
    $html = Livewire::test(BulkActionTable::class)->html();

    // Dans le groupe : sinon la case « tout sélectionner » ne remonte plus la
    // sélection (#44). Avant la zone de défilement : le bandeau garde la largeur
    // du tableau quand celui-ci défile horizontalement.
    expect($html)->toMatch('/<ui-checkbox-group\b[^>]*>.*data-flux-datatable-bulk-actions.*<ui-table-scroll-area\b.*<\/ui-checkbox-group>/s');
});

it('moves the "select all" checkbox from the header column into the banner', function () {
    $html = Livewire::test(BulkActionTable::class)->html();

    expect(bulkBanner($html))->toMatch('/data-flux-datatable-bulk-actions.*<ui-checkbox\b[^>]*\ball="all".*<ui-table-scroll-area/s')
        ->and(preg_match_all('/<ui-checkbox\b[^>]*\ball="all"/', $html))->toBe(1);
});

it('switches the banner from idle to active with the selection', function () {
    $ids = Item::query()->pluck('id')->map(fn ($id) => (string) $id)->all();

    $component = Livewire::test(BulkActionTable::class);

    expect(bannerState($component->html()))->toBe('idle');

    $component->set('selected', array_slice($ids, 0, 1));

    expect(bannerState($component->html()))->toBe('active');

    $component->set('selected', []);

    expect(bannerState($component->html()))->toBe('idle');
});

it('invites to check rows while nothing is selected, and says nothing more afterwards', function () {
    $ids = Item::query()->pluck('id')->map(fn ($id) => (string) $id)->all();

    Livewire::test(BulkActionTable::class)
        ->assertSeeHtml('data-flux-datatable-selection-hint')
        ->assertSee(__('flux-datatable::flux-datatable.selection_hint_empty'))
        ->set('selected', $ids)
        ->assertDontSeeHtml('data-flux-datatable-selection-hint');
});

it('lets the consumer write the summary and the hint', function () {
    $ids = Item::query()->pluck('id')->map(fn ($id) => (string) $id)->all();

    Livewire::test(SummarizedBulkActionTable::class)
        // `null` : la traduction générique reprend la main.
        ->assertSee(trans_choice('flux-datatable::flux-datatable.selection_summary', 0))
        ->set('selected', $ids)
        ->assertSee('3 files selected · 7 parts')
        ->assertSee('Move only applies to files')
        ->assertDontSee(trans_choice('flux-datatable::flux-datatable.selection_summary', 3, ['count' => 3]));
});

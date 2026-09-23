# Changelog

All notable changes to `:package_name` will be documented in this file.

## Unreleased

### Added

- Each bulk action button carries `data-flux-datatable-bulk-action="{name}"` and `data-variant="{declared variant}"`, so a consumer can style it without targeting Flux's internal classes. #58
- `toolbarActions(): ?Htmlable` hook on `FluxDataTable`: the consumer's own buttons in the table toolbar, on the right, before the per-page select. `null` by default. The toolbar now wraps (`flex-wrap`) instead of overflowing in a narrow column. #52
- `BulkAction::label()` accepts a `Closure(array $selected): string`, read with the new `labelFor(array $selected)`, for a label carrying a count only the consumer knows (*Add to quote (7)*). New `BulkAction::scopeNote(Closure)`: a note on what an available action really applies to, shown on the banner's second line without disabling it; the default `selectionHint()` joins the notes of all available actions. The active banner carries the `dark` class so its Flux buttons use their dark rendering; confirmation modals are rendered outside it. #51
- Bulk actions sit in a banner at the head of the table, in the `header` slot of `flux:table` (inside the checkbox group, above the horizontal scroll area). It shows the "select all" checkbox, which leaves the header column, a two-line selection summary and the buttons, with two states: light and disabled with no selection, dark and available otherwise (`data-state="idle"|"active"`). New overridable hooks `selectionSummary(array $selected): ?string` (`null` falls back to the `selection_summary` translation) and `selectionHint(array $selected): ?string` (defaults to the new `selection_hint_empty` translation while nothing is selected). #50
- Bulk actions are rendered as one always-visible button each, in a bar above the table, instead of being folded into a dropdown. New `BulkAction` methods: `variant()` (Flux button variant, `outline` by default), `disabledWhen(Closure)` (returns the reason the action is unavailable for a selection, shown as a tooltip on the disabled button and enforced in `executeBulkAction`) and `confirmationText()`. A selection summary (*"3 items selected"*) sits next to the buttons (`selection_summary` translation key).
- `rowAttributes(Model $row): array` hook on `FluxDataTable`: puts arbitrary HTML attributes (`wire:click`, `class`, `data-*`…) on the row itself, in table and card mode, making a whole row clickable. Empty by default.
- `class` column option now accepts a `Closure(Model): ?string` in addition to a plain string. The closure is resolved against each row and applied to the cell only (the header skips row-dependent callables). Enables per-row styling such as greying out inactive rows. Backward compatible with the existing string usage.

### Fixed

- The right side of the toolbar (consumer actions and per-page select) stays against the table's right edge when the toolbar wraps: `ms-auto` when it drops alone under the left side, `justify-end` when it wraps itself, and on the consumer actions block too. The no-op `justify-self-end` (a grid utility) is removed from the per-page select. #56
- Rows no longer break under Flux 2.16+ / blaze: the row attribute bag is passed with `:attributes` instead of being spread as `{{ $rowAttributes }}` inside the `<flux:table.row>` tag, which blaze mis-compiles (attributes silently dropped, or `syntax error, unexpected token "endif"` at view compilation). The test suite now registers the Flux, Flux Pro and blaze service providers so Flux components are really rendered.

### Removed

- **Breaking:** `$bulkActionLabel`, `bulkActionLabel()`, `setBulkActionLabel()` and the `bulk_action_label` translation key. The dropdown they labelled no longer exists; drop any call to them.

### Changed

- Bulk action buttons are spaced with `gap-3` instead of `gap-2`: at 8px, a primary and a secondary action side by side read as one block. On the active (dark) banner, `outline` buttons get a white veil (`white/14` background, `white/25` border) instead of Flux's barely contrasted `zinc-700`. #58
- **Breaking:** a `danger` bulk action is rendered as red text without background (Flux `ghost` button, `red` colour) instead of a solid red button. Its confirmation button stays solid red. #51
- **Breaking:** `BulkAction::make()` now takes a stable **name** instead of the label, and `$slug` is replaced by `$name`. The label is set with `->label()` (defaults to `Str::headline($name)`). Migrate `BulkAction::make('Supprimer')` to `BulkAction::make('delete')->label('Supprimer')`, and `executeBulkAction('supprimer')` to `executeBulkAction('delete')`. Names are restricted to letters, digits, `-` and `_`. `executeBulkAction()` now throws on an unknown name, and two actions sharing a name throw at render. Confirmation modal names are prefixed with the Livewire component id, so two tables on the same page no longer open each other's modal.
- Replaced seven `@phpstan-ignore-line` comments on `view(…)` calls with a single scoped `ignoreErrors` entry carrying `reportUnmatched: false`. Larastan only resolves the package's namespaced views (`flux-datatable::…`) when an application has already discovered the provider, so the error appeared on CI and not locally — and the inline ignores were reported as dead (`ignore.unmatchedLine`) wherever it did not. `composer analyse` is now clean in both environments.
- Narrowed the supported versions to the single target the package is actually used and tested on: PHP 8.5, Laravel 13, Livewire 4, Flux UI 2.16+. Dev dependencies were narrowed the same way.

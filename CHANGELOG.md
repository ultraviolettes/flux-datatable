# Changelog

All notable changes to `:package_name` will be documented in this file.

## Unreleased

### Added

- Bulk actions are rendered as one always-visible button each, in a bar above the table, instead of being folded into a dropdown. New `BulkAction` methods: `variant()` (Flux button variant, `outline` by default), `disabledWhen(Closure)` (returns the reason the action is unavailable for a selection, shown as a tooltip on the disabled button and enforced in `executeBulkAction`) and `confirmationText()`. A selection summary (*"3 items selected"*) sits next to the buttons (`selection_summary` translation key).
- `rowAttributes(Model $row): array` hook on `FluxDataTable`: puts arbitrary HTML attributes (`wire:click`, `class`, `data-*`…) on the row itself, in table and card mode, making a whole row clickable. Empty by default.
- `class` column option now accepts a `Closure(Model): ?string` in addition to a plain string. The closure is resolved against each row and applied to the cell only (the header skips row-dependent callables). Enables per-row styling such as greying out inactive rows. Backward compatible with the existing string usage.

### Fixed

- Rows no longer break under Flux 2.16+ / blaze: the row attribute bag is passed with `:attributes` instead of being spread as `{{ $rowAttributes }}` inside the `<flux:table.row>` tag, which blaze mis-compiles (attributes silently dropped, or `syntax error, unexpected token "endif"` at view compilation). The test suite now registers the Flux, Flux Pro and blaze service providers so Flux components are really rendered.

### Removed

- **Breaking:** `$bulkActionLabel`, `bulkActionLabel()`, `setBulkActionLabel()` and the `bulk_action_label` translation key. The dropdown they labelled no longer exists; drop any call to them.

### Changed

- Replaced seven `@phpstan-ignore-line` comments on `view(…)` calls with a single scoped `ignoreErrors` entry carrying `reportUnmatched: false`. Larastan only resolves the package's namespaced views (`flux-datatable::…`) when an application has already discovered the provider, so the error appeared on CI and not locally — and the inline ignores were reported as dead (`ignore.unmatchedLine`) wherever it did not. `composer analyse` is now clean in both environments.
- Narrowed the supported versions to the single target the package is actually used and tested on: PHP 8.5, Laravel 13, Livewire 4, Flux UI 2.16+. Dev dependencies were narrowed the same way.

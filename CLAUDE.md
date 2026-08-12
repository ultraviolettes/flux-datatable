# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

`ultraviolettes/flux-datatable` — A standalone Tailwind + Livewire DataTable package with Flux UI styling for Laravel.

**Requirements:** PHP 8.4+, Laravel 10/11/12/13, Livewire 3.6+/4.0, Flux UI 2.1+, Spatie Laravel Data 4.17+.

## Commands

```bash
composer test              # Run Pest tests
vendor/bin/pest --filter=TestName  # Run a single test
composer analyse           # PHPStan static analysis
composer format            # Format code with Pint
composer prepare           # Discover package (testbench)
composer test-coverage     # Tests with coverage
```

CI runs on PHP 8.4/8.5, Laravel 12 + 13, Ubuntu + Windows. Flux UI credentials are stored as GitHub secrets.

## Architecture

### Core: `FluxDataTable` Livewire Component (`src/Livewire/FluxDataTable.php`)

Abstract base that custom datatables extend. Subclasses **must** implement:
- `columns()` — returns array of column definitions (supports render callbacks)
- `builder()` — returns the base Eloquent `Builder` query

Optional overrides: `filters()`, `bulkActions()`, `headerWidgets()`.

The `records()` computed property chains: filters → search → sorting → pagination.

### Filter System (`src/Filters/`)

All filters extend abstract `Filter` class and use a static `make()` factory with fluent config. Types: `SelectFilter`, `DateFilter`, `DateRangeFilter`, `RadioFilter`. Each has a corresponding Blade view in `resources/views/filters/`. Custom filters implement `render()` and `make()`.

### URL/State Persistence

Multiple properties use Livewire's `#[Url]` attribute: search, sortBy, sortDirection, page, perPage, viewMode, filters. Per-page preference is persisted via cache (authenticated users) or session (guests), with query string taking precedence.

### Service Provider (`src/FluxDataTableServiceProvider.php`)

Uses Spatie Package Tools. Registers the Livewire component as `flux-datatable`, publishes views/config/translations, registers the Widget view component.

### Config (`config/flux-datatable.php`)

Controls `per_page` options and Flux UI toggles (`use_pagination`, `use_view_mode`).

### Views (`resources/views/`)

- `livewire/table.blade.php` — main table with search, filter modal, bulk actions, table/card view modes, pagination
- `filters/` — per-filter-type Blade partials
- `components/widget.blade.php` — header stat cards

### Supporting Classes

- `BulkAction` — factory pattern, optional confirmation dialog, receives selected IDs
- `WidgetDataObject` — Spatie Data DTO for header widgets, supports currency formatting
- `WithConfig` trait — manages package config state on the component

### Translations

English and French in `resources/lang/`. Keys cover filters, actions, empty state, confirmation dialogs.

## Testing

Uses Pest 3 with Laravel, Livewire, and architecture plugins. Base `TestCase` extends Orchestra Testbench, loads test migrations from `tests/database/migrations/`, and registers both the package and Livewire service providers. Test fixtures (model + component) live in `tests/Fixtures/`. Architecture tests enforce no `dd()`/`dump()`/`ray()` calls.
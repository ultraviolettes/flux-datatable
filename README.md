# FluxDataTable

[![Packagist Version](https://img.shields.io/packagist/v/ultraviolettes/flux-datatable.svg?style=flat-square)](https://packagist.org/packages/ultraviolettes/flux-datatable) [![License](https://img.shields.io/packagist/l/ultraviolettes/flux-datatable.svg?style=flat-square)](LICENSE.md)

A standalone, Tailwind+Livewire DataTable wrapper with Flux UI styling for Laravel.  
Ready-to-use, highly customizable, and easy to extend for any Laravel project.

---

## 🚀 Features

- ✅ **Server-side pagination**
- ✅ **Column sorting** with customizable direction
- ✅ **Global search** with debounce
- ✅ **Per-page options** for pagination
- ✅ **Custom column rendering** with callbacks
- ✅ **Sortable/searchable column configuration**
- ✅ **Empty state handling**
- ✅ **Fully responsive & accessible**
- ✅ **Flux UI integration** with beautiful, consistent styling
- ✅ **Config-driven components** with fallback options
- ✅ **View mode switching** between table and card views
- ✅ **Filter modal** with customizable filters
- ✅ **Search input** with live updates
- ✅ **Header widgets** whose aggregates stay in sync with the filtered table
- ✅ **Multi-value filters** with a default selection

---

## 📦 Requirements

- **PHP** ≥ 8.4
- **Laravel** 10.x → 13.x
- **Livewire** ≥ 3.6 (Livewire 4 supported)
- **TailwindCSS** (via Laravel Mix or Vite)
- **Flux UI** (for default styling)

---

## 💾 Installation

```bash
composer require ultraviolettes/flux-datatable
```

## Publish config and views

```bash
php artisan vendor:publish --provider="Ultraviolettes\FluxDataTable\FluxDataTableServiceProvider" --tag="config"
php artisan vendor:publish --provider="Ultraviolettes\FluxDataTable\FluxDataTableServiceProvider" --tag="views"
```

## ⚙️ Configuration

```php
return [
    // Default per-page options for pagination dropdown
    'per_page' => [10, 25, 50, 100],

    // Flux UI Table Configuration
    'flux_ui' => [
        // Enable/disable Flux UI pagination component
        'use_pagination' => true,
        // Enable/disable view mode table / card
        'use_view_mode' => true,
    ],
];
```

## 🔧 Usage

### Flux UI Integration

This package now fully integrates with Flux UI Table components to provide a beautiful, consistent styling experience. The integration includes:

- **Table Components**: Uses Flux UI Table components for the main table structure
- **Form Components**: Uses Flux UI Input and Select components for search and pagination controls
- **Utility Components**: Uses Flux UI Empty State and Pagination components for better user experience

You can configure the Flux UI integration in the `config/flux-datatable.php` file:

```php
'flux_ui' => [
    // Enable/disable Flux UI Table container component
    'use_container' => true,
    // Enable/disable Flux UI pagination component
    'use_pagination' => true,
    // Enable/disable Flux UI empty state component
    'use_empty_state' => true,
],
```

If you disable any of these options, the package will fall back to standard HTML and Laravel's built-in components.

### Basic Usage

You can use the FluxDataTable facade to create a new data table:

```php
use Ultraviolettes\FluxDataTable\Facades\FluxDataTable;

// In your controller
public function index()
{
    $table = FluxDataTable::columns([
        ['label' => 'ID', 'field' => 'id'],
        ['label' => 'Name', 'field' => 'name'],
        ['label' => 'Email', 'field' => 'email'],
    ])->data(User::query());

    return view('users.index', compact('table'));
}
```

Then in your blade view:

```blade
<div>
    {!! $table !!}
</div>
```

### Advanced Column Configuration

You can configure columns with additional options:

```php
FluxDataTable::columns([
    [
        'label' => 'ID', 
        'field' => 'id',
        'sortable' => true,
        'searchable' => true,
    ],
    [
        'label' => 'Name', 
        'field' => 'name',
        'sortable' => true,
        'searchable' => true,
    ],
    [
        'label' => 'Status', 
        'field' => 'status',
        'sortable' => false,
        'searchable' => false,
        'render' => function($row) {
            return '<span class="badge badge-' . $row->status . '">' . ucfirst($row->status) . '</span>';
        }
    ],
    [
        'label' => 'Role', 
        'field' => 'role',
        'sortable' => false,
        'searchable' => false,
        'render' => '<flux:badge size="lg" color="sky" variant="pill">
            <div class="mr-2 h-2 w-2 rounded-full bg-blue-500"></div>
            <span class="text-black">Admin</span>
        </flux:badge>'
    ],
    [
        'label' => 'Actions', 
        'field' => null,
        'sortable' => false,
        'searchable' => false,
        'render' => function($row) {
            return '<a href="/users/' . $row->id . '/edit" class="btn btn-sm btn-primary">Edit</a>';
        }
    ],
])->data(User::query());
```

### Per-column CSS classes

Each column can declare a `class` option to apply CSS classes to its cells.
Two forms are supported:

- **Static string** — applied to both the column header and every cell of that column:

```php
[
    'label' => 'Actions',
    'field' => null,
    'class' => 'bg-zinc-50',
    'render' => fn ($row) => view('rows.actions', ['row' => $row]),
],
```

- **Closure `fn ($row): ?string`** — evaluated against each row, only applied to the cell (the header has no row context and skips the callable). Useful to highlight, mute or otherwise discriminate a row based on its data:

```php
[
    'label' => 'Provider name',
    'field' => 'name',
    'render' => fn ($row) => $row->name,
    // grey out inactive rows
    'class' => fn ($row) => $row->is_active ? null : 'text-zinc-400 italic',
],
```

Returning `null` from the callable emits no class for that row. Both forms can be mixed across columns in the same table.

### Customizing Per-Page Options

You can customize the per-page options:

```php
FluxDataTable::columns([
    // your columns
])->data(User::query())
  ->perPageOptions([5, 15, 30, 50]);
```

### Using with Livewire Directly

If you prefer to use the Livewire component directly:

```blade
<livewire:flux-datatable::table
    :columns="[
        ['label' => 'ID', 'field' => 'id'],
        ['label' => 'Name', 'field' => 'name'],
        ['label' => 'Email', 'field' => 'email'],
    ]"
    :data="App\Models\User::query()"
    :perPageOptions="[10, 25, 50, 100]"
/>
```

### View Mode Switching

The FluxDataTable component now supports switching between table and card views:

```php
// In your Livewire component or controller
$table = FluxDataTable::columns([
    // your columns
])->data(User::query());

// Set the default view mode (optional, defaults to 'table')
$table->viewMode('card');
```

Users can switch between views using the built-in view mode buttons. The current view mode is preserved in the URL query string.

### Filter System

The FluxDataTable component includes a powerful filtering system that allows you to define filters for your data table. The filters are displayed in a modal that can be opened by clicking the "Filtres" button.

To define filters, override the `filters()` method in your Livewire component:

```php
use Ultraviolettes\FluxDataTable\Filters\SelectFilter;
use Ultraviolettes\FluxDataTable\Filters\DateFilter;

class UsersTable extends \Ultraviolettes\FluxDataTable\Http\Livewire\FluxDataTable
{
    // ... other methods

    public function filters(): array
    {
        return [
            'status' => SelectFilter::make(__('Status'), 'status')
                ->options([
                    '' => 'Tous',
                    'active' => 'Actif',
                    'inactive' => 'Inactif',
                    'pending' => 'En attente',
                ]),
            'role' => SelectFilter::make('Rôle', 'role')
                ->options([
                    '' => 'Tous',
                    'admin' => 'Administrateur',
                    'user' => 'Utilisateur',
                    'guest' => 'Invité',
                ]),
            'created_at' => DateFilter::make(__('Creation date'), 'created_at')
                ->query(fn ($query, $date) => $query->whereDate('created_at', $date)),
        ];
    }
}
```

The filter values are automatically applied to the query when the user selects a value
from the filter dropdown.

> **Note** — unlike `search`, `sortBy`, `sortDirection` and `perPage`, filter values are
> **not** persisted in the URL query string: `$updatesQueryString` is Livewire 2 syntax
> and has no effect on Livewire 3+. A filtered view cannot be shared by copying the URL.

You can reset all filters to their default values by clicking the "Réinitialiser" button in the filter modal.

#### Available Filter Types

Currently, the following filter types are available:

- **SelectFilter**: A dropdown filter that allows users to select a single value from a list of options.
- **MultiSelectFilter**: The same dropdown, allowing several values at once — see below.
- **RadioFilter**: A radio group, for a single value among a short list.
- **DateFilter**: A date picker filter that allows users to select a date for filtering.
- **DateRangeFilter**: A date range picker, applied with `whereBetween`.

More filter types will be added in future releases.

#### MultiSelectFilter

`SelectFilter` and `RadioFilter` produce a single scalar, so the user can only ever
see *one* status at a time. `MultiSelectFilter` takes several values on the same
dimension and shows their union — a plain `whereIn($field, $values)` by default:

```php
use Ultraviolettes\FluxDataTable\Filters\MultiSelectFilter;

'status' => MultiSelectFilter::make('Status', 'status')
    ->options([
        2 => 'Awaiting validation',
        3 => 'In production',
        4 => 'Shipping',
        8 => 'Cancelled',
    ])
    ->defaultValue([2, 3, 4])                                    // everything but "Cancelled"
    ->query(fn ($query, $values) => $query->whereIn('status_id', $values)),
```

A custom `query()` callback always receives an **array**, even when a single value
is selected.

`defaultValue()` is where this filter earns its keep. Pre-selecting every value but
one hides that value by default while leaving it one click away — which is what you
want instead of adding a second "exclude" filter alongside the first. Two controls on
the same dimension can contradict each other (including *Cancelled* while another
filter still excludes it yields an empty table and two perfectly sensible-looking
pills), and no precedence rule is guessable. One multi-select is one source of truth.

Two behaviours worth knowing:

- **Emptying the selection applies no filter at all**, and the table falls back to
  every row. Closing the pill does the same, since `removeFilter()` drops the whole
  entry: the default value is only set on `mount()` and does not come back.
- **The pill label adapts to the selection** so a near-complete one never renders as a
  long enumeration: up to two values are listed, an all-but-a-few selection reads
  *"all except Cancelled"*, everything else falls back to *"5 selected"*. The wording
  lives in the package translations (`filter_all`, `filter_all_except`,
  `filter_selected_count`) and can be overridden by publishing them.

A filter with a multiple value renders its pill through `getPillLabel()`, which is
the extension point to override in your own filters — `getKeyLabel()` only ever
receives a single scalar key.

### Search Functionality

The search functionality is now enhanced with a dedicated search input:

```php
// The search input is included by default in the table template
// The search property is automatically bound to the input
```

#### Customizing Searchable Fields

By default, the search functionality only searches in the 'name' field. You can customize which fields are searched by using the `setSearchableFields` method or by passing the fields in the constructor:

```php
// Using the setSearchableFields method
class UsersTable extends \Ultraviolettes\FluxDataTable\Livewire\FluxDataTable
{
    public function mount()
    {
        parent::mount();
        $this->setSearchableFields(['name', 'email', 'description']);
    }
}

// Or when initializing the component
<livewire:users-table 
    :searchableFields="['name', 'email', 'description']"
/>
```

The search will now look for matches in all specified fields, using OR conditions between them.

You can also customize the search behavior in your Livewire component:

```php
// In your Livewire component
public function updatingSearch()
{
    // Custom logic when search is updated
    $this->resetPage();
}
```

### Header Widgets

`headerWidgets()` renders stat cards above the table. An aggregate shown there must
describe **the rows the user is actually looking at** — so build it from
`filteredQuery()`, never from `builder()`.

`filteredQuery()` is the table query after filters and search, without sorting and
without pagination. It is the single source of truth for the displayed rows:

```php
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Ultraviolettes\FluxDataTable\DataObject\WidgetDataObject;

#[Computed]
public function headerWidgets(): Collection
{
    return collect([
        new WidgetDataObject(
            label: 'Total',
            value: (string) $this->filteredQuery()->sum('price_ht'),
        )->isCurrency(),
    ]);
}
```

Do **not** rebuild the query by hand:

```php
// ✗ Applies filters but silently forgets the search term: the widget will
//   contradict the table as soon as the user types something.
$this->builder()->tap(fn ($query) => $this->applyFilters($query))->sum('price_ht');
```

A few properties worth knowing:

- **No pagination.** The aggregate covers the whole filtered result, not the current page.
- **No sorting.** Sorting is a display concern and stays in `records()`.
- **A new instance on every call.** An Eloquent builder mutates in place, so
  `filteredQuery()` is deliberately not memoized: two widgets can each add their own
  clauses without polluting one another.
- **Public.** Your own tests can assert that a widget and the table cover the same set.

#### Widgets on a subset of the rows

If your widgets legitimately cover a subset of the displayed rows (excluding parent
orders that would be double-counted, for instance), override `widgetQuery()` rather
than diverging widget by widget. The difference is then declared once, explicitly —
and the widget label should tell the user about it:

```php
public function widgetQuery(): Builder
{
    return $this->filteredQuery()->whereNull('subscription_parent_id');
}
```

`headerWidgets()` is free to use either: `filteredQuery()` for "exactly the table",
`widgetQuery()` for "the table, minus what this table's widgets deliberately skip".

#### Keeping the figure readable

Because the widget now covers exactly the displayed rows, rows the user does not care
about — cancelled orders, say — land in the total and make it hard to read at a glance.
The answer is a [`MultiSelectFilter`](#multiselectfilter) whose `defaultValue()` pre-selects
everything but those rows: the total stays faithful to the table *and* immediately
readable, without a second filter that could contradict the first.

## 🛠️ Testing

```bash
composer require --dev orchestra/testbench livewire/livewire
php artisan test
```

Example feature test in tests/Feature/FluxDataTableTest.php:

```php
<?php

namespace Ultraviolettes\FluxDataTable\Tests\Feature;

use Livewire\Livewire;
use Orchestra\Testbench\TestCase;
use Ultraviolettes\FluxDataTable\FluxDataTableServiceProvider;

class FluxDataTableTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            FluxDataTableServiceProvider::class,
            \Livewire\LivewireServiceProvider::class,
        ];
    }

    /** @test */
    public function it_displays_records()
    {
        $users = \App\Models\User::factory()->count(3)->create();

        Livewire::test('flux-datatable::table', [
            'columns' => [['label' => 'Name', 'field' => 'name']],
            'data'    => \App\Models\User::query(),
        ])->assertSee($users->first()->name);
    }
}
```

## 🤝 Contributing

1. Fork the repo
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -am 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

Please follow the PSR-12 coding standard and run `composer run format` before submitting.

## 📄 License
This package is open-sourced under the MIT license.

## 📜 Changelog
See CHANGELOG.md for release notes and version history.

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

---

## 📦 Requirements

- **PHP** ≥ 8.4
- **Laravel** ≥ 10.x
- **Livewire** ≥ 2.x
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
        // Enable/disable Flux UI Table container component
        'use_container' => true,
        // Enable/disable Flux UI pagination component
        'use_pagination' => true,
        // Enable/disable Flux UI empty state component
        'use_empty_state' => true,
    ],

    // Legacy CSS classes (deprecated)
    'classes' => [
        'wrapper'      => 'overflow-x-auto',
        'table'        => 'min-w-full divide-y divide-gray-200',
        'thead'        => 'bg-gray-50',
        'th'           => 'px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider',
        'tbody'        => 'bg-white divide-y divide-gray-200',
        'td'           => 'px-6 py-4 whitespace-nowrap',
        'pagination'   => 'mt-4',
        'search_input' => 'mb-4 p-2 border rounded',
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

### Filter Modal

The FluxDataTable component includes a filter modal that can be customized:

```blade
<!-- The filter modal is included by default in the table template -->
<!-- You can customize the filter modal content in your published views -->
```

### Search Functionality

The search functionality is now enhanced with a dedicated search input:

```php
// The search input is included by default in the table template
// The search property is automatically bound to the input
```

You can customize the search behavior in your Livewire component:

```php
// In your Livewire component
public function updatingSearch()
{
    // Custom logic when search is updated
    $this->resetPage();
}
```

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

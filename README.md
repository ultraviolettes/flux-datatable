# FluxDataTable

[![Packagist Version](https://img.shields.io/packagist/v/your-vendor/flux-datatable.svg?style=flat-square)](https://packagist.org/packages/your-vendor/flux-datatable) [![Build Status](https://img.shields.io/github/actions/workflow/status/your-vendor/flux-datatable/ci.yml?branch=main&style=flat-square)](https://github.com/your-vendor/flux-datatable/actions) [![License](https://img.shields.io/packagist/l/your-vendor/flux-datatable.svg?style=flat-square)](LICENSE.md)

A standalone, Tailwind-powered Livewire DataTable component styled with Flux UI for Laravel.  
Ready-to-use, highly customizable, and easy to extend for any Laravel project.

---

## 🚀 Features

- ✅ **Server-and-client pagination** (Flux pro feature)  
- ✅ **Column sorting** (Flux pro feature)  
- ✅ **Global search** (with debounce)  
- ✅ **Bulk actions** (select & operate on multiple rows)  
- ✅ **Loading & empty states**  
- ✅ **Configurable column visibility**  
- ✅ **Fully responsive & accessible** (Flux pro feature) 
- ✅ **Blade slots** for custom cell rendering
- ✅ **Config-driven CSS classes** via Tailwind

---

## 📦 Requirements

- **PHP** ≥ 8.1  
- **Laravel** ≥ 12.x  
- **Livewire** ≥ 2.x  
- **TailwindCSS** (via Laravel Mix or Vite)  
- **Flux UI** (for default styling)
- **Flux Pro** (go to https://fluxui.dev/pricing to get your key)

---

## 💾 Installation

```bash
composer require ultraviolettes/flux-datatable
```

## Publish config and views

```bash
php artisan vendor:publish --provider="YourVendor\FluxDataTable\FluxDataTableServiceProvider" --tag="config"
php artisan vendor:publish --provider="YourVendor\FluxDataTable\FluxDataTableServiceProvider" --tag="views"
```

## ⚙️ Configuration

```php
return [
    // Default per-page options for pagination dropdown
    'per_page' => [10, 25, 50, 100],

    // Default CSS classes for table elements (Tailwind)
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

1. Create a Livewire Component

```bash
php artisan make:livewire FluxDataTable
```

app/Http/Livewire/FluxDataTable.php

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class FluxDataTable extends Component
{
    use WithPagination;

    public array  $columns       = [];
    public mixed  $data          = [];
    public string $search        = '';
    public string $sortField     = '';
    public string $sortDirection = 'asc';

    protected $updatesQueryString = ['search', 'sortField', 'sortDirection', 'page'];

    public function mount(array $columns, $data)
    {
        $this->columns = $columns;
        $this->data    = $data;
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField     = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = $this->data instanceof \Illuminate\Pagination\Paginator || $this->data instanceof \Illuminate\Pagination\LengthAwarePaginator
            ? $this->data->getCollection()->toQuery()
            : collect($this->data)->toQuery();

        if ($this->search) {
            $query->where(function($q) {
                foreach ($this->columns as $col) {
                    $q->orWhere($col['field'], 'like', '%'.$this->search.'%');
                }
            });
        }

        if ($this->sortField) {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        $perPage = config('flux-datatable.per_page')[0] ?? 10;

        $records = $query->paginate($perPage);

        return view('flux-datatable::livewire.table', [
            'records' => $records,
        ]);
    }
}
```

2. Create the Blade View

resources/views/livewire/table.blade.php

```bladehtml
<div class="{{ config('flux-datatable.classes.wrapper') }}">
    <div>
        <input
            type="text"
            wire:model.debounce.500ms="search"
            placeholder="Search..."
            class="{{ config('flux-datatable.classes.search_input') }}"
        />
    </div>

    <table class="{{ config('flux-datatable.classes.table') }}">
        <thead class="{{ config('flux-datatable.classes.thead') }}">
            <tr>
                @foreach ($columns as $col)
                    <th
                        wire:click="sortBy('{{ $col['field'] }}')"
                        class="{{ config('flux-datatable.classes.th') }}"
                    >
                        {{ $col['label'] }}
                        @if ($sortField === $col['field'])
                            @if ($sortDirection === 'asc') ▲ @else ▼ @endif
                        @endif
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody class="{{ config('flux-datatable.classes.tbody') }}">
            @forelse ($records as $row)
                <tr>
                    @foreach ($columns as $col)
                        <td class="{{ config('flux-datatable.classes.td') }}">
                            {{ data_get($row, $col['field']) }}
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columns) }}" class="text-center py-4">
                        No records found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="{{ config('flux-datatable.classes.pagination') }}">
        {{ $records->links() }}
    </div>
</div>
```

3. Embed in Your App

```bladehtml
<livewire:flux-data-table
    :columns="[
        ['label' => 'ID',    'field' => 'id'],
        ['label' => 'Name',  'field' => 'name'],
        ['label' => 'Email', 'field' => 'email'],
    ]"
    :data="App\Models\User::query()"
/>
```

🛠️ Testing

```bash
composer require --dev orchestra/testbench livewire/livewire
php artisan test
```

Example feature test in tests/Feature/FluxDataTableTest.php:

```php
<?php

namespace YourVendor\FluxDataTable\Tests\Feature;

use Livewire\Livewire;
use Orchestra\Testbench\TestCase;
use YourVendor\FluxDataTable\FluxDataTableServiceProvider;

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

        Livewire::test('flux-data-table', [
            'columns' => [['label' => 'Name', 'field' => 'name']],
            'data'    => \App\Models\User::query(),
        ])->assertSee($users->first()->name);
    }
}
```

## 🤝 Contributing

Fork the repo

Create your feature branch (git checkout -b feature/fooBar)
Commit your changes (git commit -am 'Add some fooBar')
Push to the branch (git push origin feature/fooBar)
Open a Pull Request
Please follow the PSR-12 coding standard and run composer run lint before submitting.

## 📄 License
This package is open-sourced under the MIT license.

## 📜 Changelog
See CHANGELOG.md for release notes and version history.

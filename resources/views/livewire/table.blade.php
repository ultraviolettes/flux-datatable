<div x-data="{ viewMode: @entangle('viewMode') }">
    <div class="flex flex-row gap-4 pt-4">
        @if($tableFilters)
        <flux:modal.trigger name="filter-modal">
            <flux:button variant="filled">+ Filtres</flux:button>
        </flux:modal.trigger>
        @endif
        <flux:input icon="magnifying-glass" placeholder="Rechercher" wire:model.live="search" />
        <flux:button.group>
            <flux:button icon="table-cells" x-bind:class="{ 'bg-primary': viewMode === 'table' }" wire:click="setViewMode('table')"></flux:button>
            <flux:button icon="squares-2x2" x-bind:class="{ 'bg-primary-500': viewMode === 'card' }" wire:click="setViewMode('card')"></flux:button>
        </flux:button.group>

        <flux:modal name="filter-modal" class="md:w-96">
            <div class="space-y-6">
                <flux:fieldset>
                    <flux:legend>Filtres</flux:legend>
                    <div class="space-y-6">
                        @foreach($tableFilters as $field => $filter)
                            {!! $filter->render() !!}
                        @endforeach
                    </div>
                </flux:fieldset>
                <div class="flex">
                    <flux:spacer />
                    <flux:button wire:click="resetFilters" variant="ghost">Réinitialiser</flux:button>
                    <flux:button x-on:click="$dispatch('close-modal', 'filter-modal')" variant="primary">Appliquer les filtres</flux:button>
                </div>
            </div>
        </flux:modal>
    </div>

    <div class="m-8"></div>

    @if(count($bulkActions) > 0)
        <div class="mb-4">
            <flux:dropdown>
                <flux:button size="sm" :disabled="count($selected) === 0">
                    Bulk Actions
                </flux:button>

                <flux:menu>
                    @foreach($bulkActions as $name => $action)
                        <flux:menu.item wire:click="executeBulkAction('{{ $name }}')">
                            {{ $name }}
                        </flux:menu.item>
                    @endforeach
                </flux:menu>
            </flux:dropdown>
        </div>
    @endif

    <!-- Table View -->
    <div x-show="viewMode === 'table'">
        <flux:checkbox.group>
            <flux:table :paginate="$this->records" data-flux-table="UV_FDT">
                <flux:table.columns>
                    <flux:table.column class="w-10">
                        <flux:checkbox.all />
                    </flux:table.column>
                    @foreach ($columns as $col)
                        <flux:table.column
                            x-data="{ sortable: {{ $col['sortable'] ? 'true' : 'false' }} }"
                            align="center"
                            sortable="$col['sortable'] ?? true"
                            sorted="$sortBy === $col['field']"
                            direction="$sortDirection"
                            x-bind:class="{ 'cursor-pointer': sortable }"
                            x-on:click="sortable ? $wire.sort('{{ $col['field'] }}') : null"
                        >
                            {{ $col['label'] }}
                        </flux:table.column>
                    @endforeach

                    @if(count($actions) > 0)
                        <flux:table.column>
                            Actions
                        </flux:table.column>
                    @endif
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($this->records as $row)
                        <flux:table.row wire:key="$row->id ?? $loop->index">
                            <flux:table.cell>
                                <flux:checkbox
                                    :value="in_array($row->id, $selected)"
                                    wire:click="toggleSelect('{{ $row->id }}')"
                                />
                            </flux:table.cell>
                            @foreach ($columns as $col)
                                <flux:table.cell>
                                    @if(isset($col['render']))
                                        @if(is_callable($col['render']))
                                            {!! $col['render']($row) !!}
                                        @elseif(is_string($col['render']))
                                            {!! $col['render'] !!}
                                        @else
                                            {{ data_get($row, $col['field']) }}
                                        @endif
                                    @else
                                        {{ data_get($row->toArray(), $col['field']) }}
                                    @endif
                                </flux:table.cell>
                            @endforeach

                            @if(count($actions) > 0)
                                <flux:table.cell>
                                    <flux:dropdown>
                                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom"></flux:button>
                                        <flux:menu>
                                            @foreach($actions as $name => $action)
                                                <flux:menu.item wire:click="executeAction('{{ $name }}', '{{ $row->id }}')">
                                                    {{ $name }}
                                                </flux:menu.item>
                                            @endforeach
                                        </flux:menu>
                                    </flux:dropdown>
                                </flux:table.cell>
                            @endif
                        </flux:table.row>
                    @endforeach

                    @if(count($this->records) === 0)
                        <flux:table.row>
                            <flux:table.cell colspan="{{ count($columns) + (count($bulkActions) > 0 ? 1 : 0) + (count($actions) > 0 ? 1 : 0) }}" class="text-center py-4">
                                @if($fluxUiConfig['use_empty_state'] ?? true)
                                    No records found.
                                @else
                                    No records found.
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endif
                </flux:table.rows>
            </flux:table>
        </flux:checkbox.group>
    </div>

    <!-- Card View -->
    <div x-show="viewMode === 'card'">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3">
            @foreach ($this->records as $row)
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="p-4">
                        @foreach ($columns as $col)
                            <div class="mb-2">
                                <strong>{{ $col['label'] }}:</strong>
                                @if(isset($col['render']))
                                    @if(is_callable($col['render']))
                                        {!! $col['render']($row) !!}
                                    @elseif(is_string($col['render']))
                                        {!! $col['render'] !!}
                                    @else
                                        {{ data_get($row, $col['field']) }}
                                    @endif
                                @else
                                    {{ data_get($row, $col['field']) }}
                                @endif
                            </div>
                        @endforeach

                        @if(count($actions) > 0)
                            <div class="mt-4 flex justify-end">
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"></flux:button>
                                    <flux:menu>
                                        @foreach($actions as $name => $action)
                                            <flux:menu.item wire:click="executeAction('{{ $name }}', '{{ $row->id }}')">
                                                {{ $name }}
                                            </flux:menu.item>
                                        @endforeach
                                    </flux:menu>
                                </flux:dropdown>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if($fluxUiConfig['use_pagination'] ?? true)
            <div class="mt-4">
                <flux:pagination :paginator="$this->records" />
            </div>
        @endif
    </div>
</div>

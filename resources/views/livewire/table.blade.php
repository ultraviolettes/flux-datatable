<div x-data="{ viewMode: @entangle('viewMode') }" class="space-y-8">

    @if($this->headerWidgets->isNotEmpty())

        <div class="flex gap-4">
            @foreach($this->headerWidgets as $widget)
                <x-flux-datatable::widget :data="$widget" wire:key="widget-{{ \Illuminate\Support\Str::slug($widget->label) }}"/>
            @endforeach
        </div>

    @endif


    <div class="flex justify-between items-end gap-4">

        <div class="flex flex-row gap-4">
            @if($tableFilters)
                <flux:modal.trigger name="filter-modal">
                    <flux:button variant="filled" icon="plus">{{ __('flux-datatable::flux-datatable.filters')  }}</flux:button>
                </flux:modal.trigger>
            @endif
            <flux:input icon="magnifying-glass" placeholder="Rechercher" wire:model.live="search" class="max-w-xs" clearable />

            @if($useViewMode)
                <flux:button.group>
                    <flux:button icon="table-cells" x-bind:class="{ 'bg-primary': viewMode === 'table' }" @click="viewMode = 'table'"></flux:button>
                    <flux:button icon="squares-2x2" x-bind:class="{ 'bg-primary-500': viewMode === 'card' }" @click="viewMode = 'card'"></flux:button>
                </flux:button.group>
            @endif

            @if($tableFilters)
                <flux:modal name="filter-modal" class="md:w-96" variant="flyout">
                    <div class="space-y-6">
                        <flux:fieldset>
                            <flux:legend>{{ __('flux-datatable::flux-datatable.filters')  }}</flux:legend>
                            <div class="space-y-6">
                                @foreach($tableFilters as $field => $filter)
                                    {!! $filter->render() !!}
                                @endforeach
                            </div>
                        </flux:fieldset>
                        <div class="flex">
                            <flux:spacer />
                            <flux:button wire:click="resetFilters" variant="ghost">{{ __('flux-datatable::flux-datatable.reset')  }}</flux:button>
                            <flux:button x-on:click="$flux.modals().close()" variant="primary">{{ __('flux-datatable::flux-datatable.apply_filters') }}</flux:button>
                        </div>
                    </div>
                </flux:modal>
            @endif

        </div>

        <div>

            @if($usePagination)
                <flux:select wire:model.live="perPage"  class="justify-self-end max-w-xs">
                    @foreach($perPageOptions as $option)
                        <flux:select.option value="{{ $option }}">{{ $option }}</flux:select.option>
                    @endforeach
                </flux:select>
            @endif
        </div>
    </div>

    @if(!empty($this->filters))
        <div class="flex gap-2">
            @foreach($this->filters as $filterField => $filterValue)
                @php
                    $filter = $tableFilters[$filterField];
                    $label = $filter->getKeyLabel($filterValue);
                    $name = $filter->getName();
                    $showPills = $filter->showPills;
                @endphp

                @if($showPills)
                    <flux:badge>{{ $name }} : {{ $label }} <flux:badge.close wire:click="removeFilter('{{ $filterField }}')" /></flux:badge>
                @endif
            @endforeach
        </div>
    @endif


    @if($bulkActions->isNotEmpty())
        <div class="mb-4">
            <flux:dropdown>
                <flux:button size="sm" :disabled="count($selected) === 0">
                    {{ $bulkActionLabel }}
                </flux:button>

                <flux:menu >
                    @foreach($bulkActions as $action)
                        @if($action->requiresConfirmation)
                            <flux:modal.trigger name="confirm-modal-{{$action->slug}}">
                                <flux:menu.item :icon="$action->icon" keep-open>{{ $action->label }}</flux:menu.item>
                            </flux:modal.trigger>
                            <flux:modal name="confirm-modal-{{$action->slug}}" class="space-y-6 text-center">

                                <div class="inline-flex justify-center mx-auto bg-red-100 rounded-full p-4">

                                    <flux:icon :name="$action->confirmationIcon" class="text-red-500"/>
                                </div>

                                <flux:text>{{ __('flux-datatable::flux-datatable.bulk_action_text') }}</flux:text>

                                <div>
                                    <flux:modal.close>
                                        <flux:button variant="ghost">{{ __('flux-datatable::flux-datatable.cancel') }}</flux:button>
                                    </flux:modal.close>
                                    <flux:button  wire:click="executeBulkAction('{{ $action->slug }}')">{{ __('flux-datatable::flux-datatable.confirm') }}</flux:button>
                                </div>
                            </flux:modal>
                        @else
                            <flux:menu.item :icon="$action->icon" wire:click="executeBulkAction('{{ $action->slug }}')">
                                {{ $action->label }}
                            </flux:menu.item>
                        @endif
                    @endforeach
                </flux:menu>
            </flux:dropdown>
        </div>
    @endif

    <!-- Table View -->
    <div x-show="viewMode === 'table'">
        <flux:checkbox.group>
            <flux:table :paginate="$usePagination ? $this->records : null">
                <flux:table.columns>
                    @if(count($bulkActions) > 0)
                        <flux:table.column class="w-10">
                            <flux:checkbox.all />
                        </flux:table.column>
                    @endif
                    @foreach ($columns as $index => $col)
                        @php
                            $colId = $col['field'] ? \Illuminate\Support\Str::slug($col['field']) : $index;
                            $sticky = $col['sticky'] ?? false;
                            $class = $col['class'] ?? null;
                        @endphp
                        <flux:table.column
                            x-data="{ sortable: {{ isset($col['sortable']) && $col['sortable'] ? 'true' : 'false' }} }"
                            :align="$col['align'] ?? 'center'"
                            :sortable="isset($col['sortable']) && $col['sortable']"
                            :sorted="$sortBy === $col['field']"
                            :direction="$sortDirection"
                            :sticky="$sticky"
                            x-bind:class="{'cursor-pointer': sortable }"
                            x-on:click="sortable ? $wire.sort('{{ $col['field'] }}') : null"
                            :wire:key="'table-th-' . $colId"
                            @class([$class => $class])
                        >
                            @if($col['label'] instanceof \Closure)
                                {!! $col['label']() !!}
                            @else
                                {{ $col['label'] }}
                            @endif
                        </flux:table.column>
                    @endforeach

                    @if(count($actions) > 0)
                        <flux:table.column>
                            {{ __('flux-datatable::flux-datatable.action') }}
                        </flux:table.column>
                    @endif
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($this->records as $row)
                        @php
                            $rowId = $row->id ?? $loop->index;
                        @endphp
                        <flux:table.row :wire:key="'row-key-' . $rowId">
                            @if(count($bulkActions) > 0)
                                <flux:table.cell>
                                    <flux:checkbox
                                        :value="in_array($row->id, $selected)"
                                        wire:click="toggleSelect('{{ $row->id }}')"
                                    />
                                </flux:table.cell>
                            @endif
                            @foreach ($columns as $index => $col)
                                @php
                                    $colId = $col['field'] ? \Illuminate\Support\Str::slug($col['field']) : $index;
                                    $sticky = $col['sticky'] ?? false;
                                    $class = $col['class'] ?? null;
                                @endphp
                                <flux:table.cell :align="$col['align'] ?? 'center'" variant="strong" :wire:key="'row-cell-' . $rowId . '-' . $colId" :sticky="$sticky" @class([$class => $class])>
                                    @if(isset($col['render']))
                                        @if($col['render'] instanceof \Closure)
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
                                {{ __('flux-datatable::flux-datatable.no_record_found')  }}
                            </flux:table.cell>
                        </flux:table.row>
                    @endif
                </flux:table.rows>
            </flux:table>
        </flux:checkbox.group>
    </div>

    @if($useViewMode)
        <!-- Card View -->
        <div x-show="viewMode === 'card'">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3">
                @foreach ($this->records as $row)
                    @php
                        $rowId = $row->id ?? $loop->index;
                    @endphp
                    <div class="bg-white rounded-lg shadow overflow-hidden" wire:key="card-row-{{ $rowId }}">
                        <div class="p-4">
                            @foreach ($columns as $index => $col)
                                @php
                                    $colId = $col['field'] ? \Illuminate\Support\Str::slug($col['field']) : $index;
                                @endphp
                                <div class="mb-2" wire:key="card-col-{{ $colId }}">
                                    <strong>{{ $col['label'] }}:</strong>
                                    @if(isset($col['render']))
                                        @if($col['render'] instanceof \Closure)
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

            @if($usePagination)
                <div class="mt-4">
                    <flux:pagination :paginator="$this->records" />
                </div>
            @endif
        </div>
    @endif
</div>

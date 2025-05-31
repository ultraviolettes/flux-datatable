@php
    // Default configuration if not provided
    $fluxUiConfig = $fluxUiConfig ?? [
        'use_container' => true,
        'use_pagination' => true,
        'use_empty_state' => true,
    ];
@endphp

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

<flux:table :paginate="$this->records">
    <flux:table.columns>

        <flux:table.column class="w-10">
            <flux:checkbox wire:model="selectAll" wire:click="toggleSelectAll"/>
        </flux:table.column>

        @foreach ($columns as $col)
            <flux:table.column
                sortable
                :sorted="$sortBy === $col['field']"
                :direction="$sortDirection"
                wire:click="sort('{{ $col['field'] }}')">
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

            <flux:table.row :key="$row->id ?? $loop->index">
                <flux:table.cell>
                    <flux:checkbox
                        :value="in_array($row->id, $selected)"
                        wire:click="toggleSelect('{{ $row->id }}')"
                    />
                </flux:table.cell>

                @foreach ($columns as $col)
                    <flux:table.cell>
                        @if(isset($col['render']) && is_callable($col['render']))
                            {!! $col['render']($row) !!}
                        @else
                            {{ data_get($row, $col['field']) }}
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

@php
    // Default configuration if not provided
    $fluxUiConfig = $fluxUiConfig ?? [
        'use_container' => true,
        'use_pagination' => true,
        'use_empty_state' => true,
    ];
@endphp

<div x-data="{ viewMode: @entangle('viewMode') }">
    <div class="flex flex-row gap-4 pt-4">
        <flux:modal.trigger name="filter-modal">
            <flux:button variant="filled">+ Filtres</flux:button>
        </flux:modal.trigger>
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
                        <flux:label>Créé par</flux:label>
                        <flux:radio.group variant="cards" :indicator="false" class="max-sm:flex-col">
                            <flux:radio value="team_all" icon="user-group" label="Tout le monde" description="Tous les membres de l'équipe" />
                            <flux:radio value="team_self" icon="user" label="Moi uniquement" description="Seulement moi" />
                        </flux:radio.group>
                        <flux:select variant="listbox" placeholder="Choisir un membre...">
                            <flux:select.option>Moi</flux:select.option>
                            <flux:select.option>Tony Stark</flux:select.option>
                            <flux:select.option>Alain Proviste</flux:select.option>
                            <flux:select.option>Jean Neymar</flux:select.option>
                            <flux:select.option>Marcel Decheval</flux:select.option>
                        </flux:select>
                        <flux:date-picker locale="fr-FR " mode="range" with-presets min-range="3" label="Date de création" />
                        <flux:label>Statut</flux:label>
                        <flux:radio.group variant="cards" :indicator="false" class="flex-col">
                            <flux:radio value="state_estimate" icon="document-text" label="Devis en cours" description="" />
                            <flux:radio value="state_ordered" icon="document-check" label="Déjà commandé" description="" />
                            <flux:radio value="state_file_created" icon="document-plus" label="Fichier créé" description="" />
                        </flux:radio.group>
                    </div>
                </flux:fieldset>
                <div class="flex">
                    <flux:spacer />
                    <flux:button type="submit" variant="ghost">Réinitialiser</flux:button>
                    <flux:button type="submit" variant="primary">Appliquer les filtres</flux:button>
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

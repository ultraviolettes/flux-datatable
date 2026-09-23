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
            @if(! empty($searchableFields))
                <flux:input icon="magnifying-glass" placeholder="Rechercher" wire:model.live.debounce.500ms="search" class="max-w-xs" clearable />
            @endif

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
                @endphp

                {{-- `filled()` écarte les valeurs vides ('', null, []) : une sélection
                     entièrement vidée n'applique aucun filtre, elle ne doit donc pas
                     afficher de pill (et `getPillLabel()` n'a rien à en dire). --}}
                @if($filter->showPills && filled($filterValue))
                    <flux:badge>{{ $filter->getName() }} : {{ $filter->getPillLabel($filterValue) }} <flux:badge.close wire:click="removeFilter('{{ $filterField }}')" /></flux:badge>
                @endif
            @endforeach
        </div>
    @endif


    <!-- Table View -->
    <div x-show="viewMode === 'table'">
        {{-- La sélection passe par le `wire:model` du groupe, pas par un `wire:click` par
            ligne : `flux:checkbox.all` coche les cases par programme, sans clic, et seul le
            groupe voit ce changement. Sans lui, « tout sélectionner » cochait l'écran mais
            laissait `$selected` vide (#44). --}}
        <flux:checkbox.group wire:model.live="selected">
            <flux:table :paginate="$usePagination ? $this->records : null">
                @if($bulkActions->isNotEmpty())
                    <x-slot:header>
                        @php
                            $hasSelection = $selected !== [];
                            $selectionHint = $this->selectionHint($selected);
                        @endphp
                        {{-- Bandeau collé en tête du tableau, dans le slot `header` de
                            `flux:table` : il reste dans le `flux:checkbox.group` (la case
                            « tout sélectionner » doit y vivre pour remonter la sélection,
                            #44) et hors de la zone de défilement, donc à la largeur du
                            tableau même quand celui-ci défile horizontalement.

                            Deux états : fond clair et actions grisées sans sélection, fond
                            foncé et actions disponibles avec. Les couleurs viennent du thème
                            Flux de l'application (`zinc`, `--color-accent`), pas du package.

                            Une action = un bouton toujours visible : on voit ce qu'on peut
                            faire d'une sélection sans ouvrir de menu, et chaque action peut
                            être grisée pour sa propre raison (`disabledWhen`). --}}
                        <div
                            data-flux-datatable-bulk-actions
                            data-state="{{ $hasSelection ? 'active' : 'idle' }}"
                            @class([
                                'flex flex-wrap items-center gap-x-4 gap-y-3 rounded-t-lg px-3 py-3',
                                'bg-zinc-50 dark:bg-white/5' => ! $hasSelection,
                                'bg-zinc-900 dark:bg-zinc-950' => $hasSelection,
                            ])
                        >
                            <flux:checkbox.all />

                            <div class="min-w-0 flex-1">
                                <div
                                    data-flux-datatable-selection-summary
                                    @class([
                                        'text-sm font-medium',
                                        'text-zinc-800 dark:text-white' => ! $hasSelection,
                                        'text-white' => $hasSelection,
                                    ])
                                >
                                    {{ $this->selectionSummary($selected) ?? trans_choice('flux-datatable::flux-datatable.selection_summary', count($selected), ['count' => count($selected)]) }}
                                </div>

                                @if($selectionHint !== null)
                                    <div
                                        data-flux-datatable-selection-hint
                                        @class([
                                            'text-xs',
                                            'text-zinc-500 dark:text-zinc-400' => ! $hasSelection,
                                            'text-zinc-300' => $hasSelection,
                                        ])
                                    >
                                        {{ $selectionHint }}
                                    </div>
                                @endif
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                            @foreach($bulkActions as $action)
                                @php
                                    // Les noms de modale sont globaux dans la page : on les préfixe par l'id
                                    // du composant pour que deux tables ayant chacune une action `delete` ne
                                    // s'ouvrent pas la modale l'une de l'autre.
                                    $confirmModal = 'confirm-modal-' . $this->getId() . '-' . $action->name;
                                    $disabledReason = $action->disabledReasonFor($selected);
                                    $available = $action->isAvailableFor($selected);
                                @endphp

                                @if(! $available)
                                    @if($disabledReason !== null)
                                        {{-- Un bouton désactivé ne reçoit aucun événement souris : l'infobulle
                                            doit s'accrocher à un élément qui l'enveloppe. --}}
                                        <flux:tooltip :content="$disabledReason">
                                            <div>
                                                <flux:button size="sm" :variant="$action->variant" :icon="$action->icon" disabled>{{ $action->label }}</flux:button>
                                            </div>
                                        </flux:tooltip>
                                    @else
                                        <flux:button size="sm" :variant="$action->variant" :icon="$action->icon" disabled>{{ $action->label }}</flux:button>
                                    @endif
                                @elseif($action->requiresConfirmation)
                                    {{-- Le déclencheur n'est rendu que si l'action est disponible : autour d'un
                                        bouton désactivé (`pointer-events-none`), le clic tomberait sur le
                                        déclencheur et ouvrirait quand même la modale. --}}
                                    <flux:modal.trigger name="{{ $confirmModal }}">
                                        <flux:button size="sm" :variant="$action->variant" :icon="$action->icon">{{ $action->label }}</flux:button>
                                    </flux:modal.trigger>
                                @else
                                    <flux:button size="sm" :variant="$action->variant" :icon="$action->icon" wire:click="executeBulkAction('{{ $action->name }}')">{{ $action->label }}</flux:button>
                                @endif

                                @if($action->requiresConfirmation)
                                    <flux:modal name="{{ $confirmModal }}" class="space-y-6 text-center">
                                        <div class="inline-flex justify-center mx-auto bg-red-100 rounded-full p-4">
                                            <flux:icon :name="$action->confirmationIcon" class="text-red-500"/>
                                        </div>

                                        <flux:text>{{ $action->confirmationText ?? __('flux-datatable::flux-datatable.bulk_action_text') }}</flux:text>

                                        <div>
                                            <flux:modal.close>
                                                <flux:button variant="ghost">{{ __('flux-datatable::flux-datatable.cancel') }}</flux:button>
                                            </flux:modal.close>
                                            <flux:modal.close>
                                                <flux:button :variant="$action->variant === 'danger' ? 'danger' : 'primary'" wire:click="executeBulkAction('{{ $action->name }}')">{{ __('flux-datatable::flux-datatable.confirm') }}</flux:button>
                                            </flux:modal.close>
                                        </div>
                                    </flux:modal>
                                @endif
                            @endforeach
                            </div>
                        </div>
                    </x-slot:header>
                @endif

                <flux:table.columns>
                    @if(count($bulkActions) > 0)
                        {{-- La case « tout sélectionner » est dans le bandeau : la colonne
                            reste pour aligner les en-têtes sur les cases des lignes. --}}
                        <flux:table.column class="w-10" />
                    @endif
                    @foreach ($columns as $index => $col)
                        @php
                            $colId = $col['field'] ? \Illuminate\Support\Str::slug($col['field']) : $index;
                            $sticky = $col['sticky'] ?? false;
                            // Sur le header, on n'a pas de $row : un `class` callable n'a pas
                            // de sens ici, on l'ignore. Une string reste appliquée.
                            $class = $col['class'] ?? null;
                            $class = is_string($class) ? $class : null;
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
                            $rowAttributes = new \Illuminate\View\ComponentAttributeBag($this->rowAttributes($row));
                        @endphp
                        {{-- `:attributes` et non `{{ $rowAttributes }}` : le spread d'un bag dans une
                            balise de composant Flux est mal parsé par le compilateur blaze (attributs
                            perdus, voire PHP déséquilibré et `syntax error, unexpected token "endif"`
                            à la compilation). Voir #42. --}}
                        <flux:table.row :wire:key="'row-key-' . $rowId" :attributes="$rowAttributes">
                            @if(count($bulkActions) > 0)
                                <flux:table.cell>
                                    <flux:checkbox :value="$row->id" />
                                </flux:table.cell>
                            @endif
                            @foreach ($columns as $index => $col)
                                @php
                                    $colId = $col['field'] ? \Illuminate\Support\Str::slug($col['field']) : $index;
                                    $sticky = $col['sticky'] ?? false;
                                    // `class` peut être soit une string, soit un callable
                                    // `fn($row): ?string` pour conditionner les classes
                                    // sur la ligne (ex : griser les inactifs).
                                    $class = $col['class'] ?? null;
                                    if (is_callable($class)) {
                                        $class = $class($row);
                                    }
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
                        $rowAttributes = (new \Illuminate\View\ComponentAttributeBag($this->rowAttributes($row)))
                            ->merge(['class' => 'bg-white rounded-lg shadow overflow-hidden']);
                    @endphp
                    <div {{ $rowAttributes }} wire:key="card-row-{{ $rowId }}">
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

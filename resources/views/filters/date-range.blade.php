@use('Illuminate\View\ComponentAttributeBag')
<flux:field>
    <flux:label>{{ $name }}</flux:label>

    @php
        $attrs = new ComponentAttributeBag($config);
    @endphp

    <flux:date-picker mode="range" wire:model.live="filters.{{$field}}" :attributes="$attrs">
        @if(isset($config['hasInput']) && $config['hasInput'])
            <x-slot name="trigger">
                <div class="flex flex-col sm:flex-row gap-6 sm:gap-4">
                    <flux:date-picker.input label="Start"/>
                    <flux:date-picker.input label="End"/>
                </div>
            </x-slot>
        @endif
    </flux:date-picker>
</flux:field>

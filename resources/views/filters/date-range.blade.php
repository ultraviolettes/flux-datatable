<div>
    <flux:label>{{ $name }}</flux:label>

{{--    @php
    $attributes = [
        'mode' => 'range',
        'wire:model.live' => "filters.{$field}"
    ];

    if(isset($config['allowInput'])) {
        $attributes[':allow-input'] = $config['allowInput'] ? 'true' : 'false';
    }
    if(isset($config['altFormat'])) {
        $attributes['alt-format'] = $config['altFormat'];
    }
    if(isset($config['ariaDateFormat'])) {
        $attributes['aria-date-format'] = $config['ariaDateFormat'];
    }
    if(isset($config['dateFormat'])) {
        $attributes['date-format'] = $config['dateFormat'];
    }
    if(isset($config['earliestDate'])) {
        $attributes['earliest-date'] = $config['earliestDate'];
    }
    if(isset($config['latestDate'])) {
        $attributes['latest-date'] = $config['latestDate'];
    }
    if(isset($config['placeholder'])) {
        $attributes['placeholder'] = $config['placeholder'];
    }
    if(isset($config['pillsLocale'])) {
        $attributes['pills-locale'] = $config['pillsLocale'];
    }
    @endphp--}}

    <flux:date-picker mode="range" wire:model.live="filters.{$field}">
        @if(isset($config['mode']) && $config['mode'] === 'input')
            <x-slot name="trigger">
                <div class="flex flex-col sm:flex-row gap-6 sm:gap-4">
                    <flux:date-picker.input label="Start" />
                    <flux:date-picker.input label="End" />
                </div>
            </x-slot>
        @endif
    </flux:date-picker>
</div>

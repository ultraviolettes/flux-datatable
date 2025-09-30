<flux:radio.group wire:model.live="filters.{{ $field }}" :label="$name" :variant="$variant">
    @foreach($options as $value => $label)
        <flux:radio :value="$value" :label="$label" wire:key="{{$field}}-{{$value}}"/>
    @endforeach
</flux:radio.group>


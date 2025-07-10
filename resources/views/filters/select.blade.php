<div>
    <flux:label>{{ $name }}</flux:label>
    <flux:select wire:model.live="filters.{{ $field }}" variant="listbox" placeholder="Choisir...">
        @foreach($options as $value => $label)
            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
        @endforeach
    </flux:select>
</div>

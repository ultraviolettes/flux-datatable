<div>
    <flux:label>{{ $name }}</flux:label>
    <flux:select
        wire:model.live="filters.{{ $field }}"
        variant="listbox"
        multiple
        :placeholder="__('flux-datatable::flux-datatable.choose')"
    >
        @foreach($options as $value => $label)
            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
        @endforeach
    </flux:select>
</div>

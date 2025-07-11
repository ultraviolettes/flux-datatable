@php
    use Ultraviolettes\FluxDataTable\DataObject\WidgetDataObject;
    /**
     * @var WidgetDataObject $data
     */
@endphp

<flux:card class="flex-auto max-w-3xs">
    <flux:subheading>{{$data->label}}</flux:subheading>
    <flux:heading size="xl" class="mb-2">{{$data->value}}</flux:heading>
</flux:card>

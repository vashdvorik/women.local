<x-layouts.admin :title="__('Новость')">
    <x-admin.event-form :event="$event" :editor="$editor" :action="route('admin.events.update', $event)" method="PUT" />
</x-layouts.admin>

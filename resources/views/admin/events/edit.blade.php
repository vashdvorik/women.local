<x-layouts.admin title="Новость">
    <x-admin.event-form :event="$event" :action="route('admin.events.update', $event)" method="PUT" />
</x-layouts.admin>

<x-layouts.admin title="Новая новость">
    <x-admin.event-form :event="$event" :action="route('admin.events.store')" method="POST" />
</x-layouts.admin>

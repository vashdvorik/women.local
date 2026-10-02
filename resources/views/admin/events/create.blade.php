<x-layouts.admin :title="__('Новая новость')">
    <x-admin.event-form :event="$event" :editor="$editor" :action="route('admin.events.store')" method="POST" />
</x-layouts.admin>

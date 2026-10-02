<x-layouts.admin :title="__('Новый тег')">
    <x-admin.tag-form :tag="$tag" :action="route('admin.tags.store')" method="POST" />
</x-layouts.admin>

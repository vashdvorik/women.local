<x-layouts.admin title="Тег">
    <x-admin.tag-form :tag="$tag" :action="route('admin.tags.update', $tag)" method="PUT" />
</x-layouts.admin>

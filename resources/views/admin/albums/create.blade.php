<x-layouts.admin title="Новый фотоальбом" :flush="true">
    <x-admin.album-editor :editor="$editor" :action="route('admin.albums.store')" method="POST"
                          :album="new \App\Models\Album()" />
</x-layouts.admin>

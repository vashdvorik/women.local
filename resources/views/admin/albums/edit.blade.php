<x-layouts.admin :title="$album->rawTranslation('ru')?->title ?: 'Фотоальбом'" :flush="true">
    <x-admin.album-editor :editor="$editor" :action="route('admin.albums.update', $album)" method="PUT"
                          :album="$album" />
</x-layouts.admin>

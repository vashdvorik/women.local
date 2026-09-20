<x-layouts.admin title="Видео">
    <x-admin.video-form :video="$video" :action="route('admin.videos.update', $video)" method="PUT" />
</x-layouts.admin>

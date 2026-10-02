<x-layouts.admin :title="__('Видео')">
    <x-admin.video-form :video="$video" :action="route('admin.videos.update', $video)" method="PUT" />
</x-layouts.admin>

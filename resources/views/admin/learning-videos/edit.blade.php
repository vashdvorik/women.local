<x-layouts.admin :title="__('Обучающие видео')">
    <x-admin.learning-video-form :video="$video" :action="route('admin.learning-videos.update', $video)" method="PUT" />
</x-layouts.admin>

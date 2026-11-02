<x-layouts.admin :title="__('Обучающие видео')">
    <x-admin.learning-video-form :video="$video" :action="route('admin.learning-videos.store')" method="POST" />
</x-layouts.admin>

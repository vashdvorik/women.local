<x-layouts.admin title="Новое видео">
    <x-admin.video-form :video="$video" :action="route('admin.videos.store')" method="POST" />
</x-layouts.admin>

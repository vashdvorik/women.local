<x-layouts.admin :title="__('Новое видео')">
    <x-admin.video-form :video="$video" :action="route('admin.videos.store')" method="POST" />
</x-layouts.admin>

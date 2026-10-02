<x-layouts.admin :title="$post->rawTranslation('ru')?->title ?: __('Публикация')" :flush="true">
    <x-admin.article-editor
        :editor="$editor"
        type="post"
        :action="route('admin.posts.update', $post)"
        method="PUT"
        :article="$post"
        :noun="__('публикация')" />
</x-layouts.admin>

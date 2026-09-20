<x-layouts.admin :title="$post->rawTranslation('ru')?->title ?: 'Новость'" :flush="true">
    <x-admin.article-editor
        :editor="$editor"
        type="post"
        :action="route('admin.posts.update', $post)"
        method="PUT"
        :article="$post"
        noun="новость" />
</x-layouts.admin>

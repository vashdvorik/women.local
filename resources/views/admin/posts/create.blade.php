<x-layouts.admin title="Новая публикация" :flush="true">
    <x-admin.article-editor
        :editor="$editor"
        type="post"
        :action="route('admin.posts.store')"
        method="POST"
        :article="new \App\Models\Post()"
        noun="публикация" />
</x-layouts.admin>

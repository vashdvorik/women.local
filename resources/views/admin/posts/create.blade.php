<x-layouts.admin title="Новая новость" :flush="true">
    <x-admin.article-editor
        :editor="$editor"
        type="post"
        :action="route('admin.posts.store')"
        method="POST"
        :article="new \App\Models\Post()"
        noun="новость" />
</x-layouts.admin>

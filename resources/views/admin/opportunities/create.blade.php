<x-layouts.admin title="Новая возможность" :flush="true">
    <x-admin.article-editor
        :editor="$editor"
        type="opportunity"
        :action="route('admin.opportunities.store')"
        method="POST"
        :article="new \App\Models\Opportunity()"
        noun="возможность" />
</x-layouts.admin>

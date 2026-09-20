<x-layouts.admin :title="$opportunity->rawTranslation('ru')?->title ?: 'Возможность'" :flush="true">
    <x-admin.article-editor
        :editor="$editor"
        type="opportunity"
        :action="route('admin.opportunities.update', $opportunity)"
        method="PUT"
        :article="$opportunity"
        noun="возможность" />
</x-layouts.admin>

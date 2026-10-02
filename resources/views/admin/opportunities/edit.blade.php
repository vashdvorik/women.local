<x-layouts.admin :title="$opportunity->rawTranslation('ru')?->title ?: __('Возможность')" :flush="true">
    <x-admin.article-editor
        :editor="$editor"
        type="opportunity"
        :action="route('admin.opportunities.update', $opportunity)"
        method="PUT"
        :article="$opportunity"
        :noun="__('возможность')" />
</x-layouts.admin>

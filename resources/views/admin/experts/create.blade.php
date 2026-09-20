<x-layouts.admin title="Новый эксперт">
    <x-admin.expert-form :expert="$expert" :action="route('admin.experts.store')" method="POST" />
</x-layouts.admin>

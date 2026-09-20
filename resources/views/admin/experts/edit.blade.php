<x-layouts.admin title="Эксперт">
    <x-admin.expert-form :expert="$expert" :action="route('admin.experts.update', $expert)" method="PUT" />
</x-layouts.admin>

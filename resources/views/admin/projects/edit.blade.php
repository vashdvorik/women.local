<x-layouts.admin title="Проект">
    <x-admin.project-form :project="$project" :action="route('admin.projects.update', $project)" method="PUT" />
</x-layouts.admin>

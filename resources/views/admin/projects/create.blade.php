<x-layouts.admin title="Новый проект">
    <x-admin.project-form :project="$project" :action="route('admin.projects.store')" method="POST" />
</x-layouts.admin>

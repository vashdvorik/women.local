<x-layouts.admin :title="__('Проект')">
    <x-admin.project-form :project="$project" :editor="$editor" :action="route('admin.projects.update', $project)" method="PUT" />
</x-layouts.admin>

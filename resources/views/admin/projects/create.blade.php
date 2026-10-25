<x-layouts.admin :title="__('Новый проект')">
    <x-admin.project-form :project="$project" :editor="$editor" :action="route('admin.projects.store')" method="POST" />
</x-layouts.admin>

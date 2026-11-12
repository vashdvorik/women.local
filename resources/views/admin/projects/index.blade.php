<x-layouts.admin :title="__('Проекты')">
    <x-slot:actions>
        <a href="{{ route('admin.projects.create') }}" class="btn-primary">{{ __('Добавить проект') }}</a>
    </x-slot:actions>

    @if($projects->isEmpty())
        <div class="card text-center">
            <p class="text-reading text-ink-muted">{{ __('Проектов пока нет. Страница «Проекты» покажет только шапку.') }}</p>
            <a href="{{ route('admin.projects.create') }}" class="btn-primary mt-4">{{ __('Добавить проект') }}</a>
        </div>
    @else
        <p class="text-reading text-ink-muted">
            {{ __('Перетащите проект за значок слева (или нажмите стрелки), чтобы поменять порядок на сайте.') }}
        </p>

        <div x-data="rowReorder(@js(route('admin.projects.reorder')), @js(['saving' => __('Сохраняем порядок…'), 'saved' => __('Порядок сохранён.'), 'error' => __('Не удалось сохранить порядок. Обновите страницу и попробуйте ещё раз.')]))" class="overflow-x-auto">
            <p class="text-reading text-ink-muted min-h-[1.5rem]" role="status" aria-live="polite" x-text="message()"></p>
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="w-28">{{ __('Порядок') }}</th>
                        <th class="w-24"></th>
                        <th>{{ __('Название') }}</th>
                        <th class="w-40">{{ __('Категория') }}</th>
                        <th class="w-28">{{ __('Языки') }}</th>
                        <th class="w-24">{{ __('На сайте') }}</th>
                        <th class="w-40"></th>
                    </tr>
                </thead>
                <tbody x-ref="body" @dragover="over($event)" @drop.prevent>
                    @foreach($projects as $project)
                        <tr data-id="{{ $project->id }}" @dragstart="start($event)" @dragend="end()">
                            <td>
                                <x-admin.reorder-handle />
                            </td>
                            <td>
                                @if($project->imageUrl())
                                    <img src="{{ $project->imageUrl() }}" alt="" class="w-20 rounded-sm"
                                         style="aspect-ratio: 16/9; object-fit: cover">
                                @else
                                    <span class="grid w-20 place-items-center rounded-sm bg-surface-sunken text-ink-faint"
                                          style="aspect-ratio: 16/9">—</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.projects.edit', $project) }}" class="table-link">
                                    {{ $project->rawTranslation('ru')?->title ?: __('Без названия') }}
                                </a>
                                @if($project->url)
                                    <a href="{{ $project->url }}" target="_blank" rel="noopener noreferrer"
                                       class="ml-1.5 inline-flex align-middle text-ink-faint hover:text-accent" title="{{ $project->url }}">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17 17 7M8 7h9v9"/></svg>
                                    </a>
                                @endif
                            </td>
                            <td class="text-ink-muted">{{ $project->rawTranslation('ru')?->category ?: '—' }}</td>
                            <td class="text-caption text-ink-muted">
                                {{ $project->translations->pluck('locale')->map(fn ($l) => strtoupper($l))->join(' · ') }}
                            </td>
                            <td>
                                <x-admin.status-badge :status="$project->is_published ? 'published' : 'draft'" />
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.projects.edit', $project) }}" class="btn-quiet">{{ __('Изменить') }}</a>
                                    <x-admin.delete-button :action="route('admin.projects.destroy', $project)"
                                        :subject="$project->rawTranslation('ru')?->title ?: __('проект')" :noun="__('проект')" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-layouts.admin>

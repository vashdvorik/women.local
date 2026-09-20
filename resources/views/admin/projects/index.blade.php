<x-layouts.admin title="Проекты">
    <x-slot:actions>
        <a href="{{ route('admin.projects.create') }}" class="btn-primary">Добавить проект</a>
    </x-slot:actions>

    @if($projects->isEmpty())
        <div class="card text-center">
            <p class="text-reading text-ink-muted">Проектов пока нет. Страница «Проекты» покажет только шапку.</p>
            <a href="{{ route('admin.projects.create') }}" class="btn-primary mt-4">Добавить проект</a>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="w-24"></th>
                        <th>Название</th>
                        <th class="w-40">Категория</th>
                        <th class="w-28">Языки</th>
                        <th class="w-24">На сайте</th>
                        <th class="w-28">Порядок</th>
                        <th class="w-40"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($projects as $project)
                        <tr>
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
                                    {{ $project->rawTranslation('ru')?->title ?: 'Без названия' }}
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
                                <div class="flex gap-1">
                                    <form method="POST" action="{{ route('admin.projects.move', $project) }}">
                                        @csrf<input type="hidden" name="direction" value="up">
                                        <button class="btn-icon" @disabled($loop->first) title="Вверх" aria-label="Вверх">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 12V4M4 8l4-4 4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.projects.move', $project) }}">
                                        @csrf<input type="hidden" name="direction" value="down">
                                        <button class="btn-icon" @disabled($loop->last) title="Вниз" aria-label="Вниз">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 4v8M4 8l4 4 4-4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.projects.edit', $project) }}" class="btn-quiet">Изменить</a>
                                    <x-admin.delete-button :action="route('admin.projects.destroy', $project)"
                                        :subject="$project->rawTranslation('ru')?->title ?: 'проект'" noun="проект" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-layouts.admin>

<x-layouts.admin title="Видео">
    <x-slot:actions>
        <a href="{{ route('admin.videos.create') }}" class="btn-primary">Добавить видео</a>
    </x-slot:actions>

    @if($videos->isEmpty())
        <div class="card text-center">
            <p class="text-reading text-ink-muted">Видео пока нет.</p>
            <a href="{{ route('admin.videos.create') }}" class="btn-primary mt-4">Добавить видео</a>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="w-28"></th>
                        <th>Название</th>
                        <th class="w-40">Дата события</th>
                        <th class="w-32">Порядок</th>
                        <th class="w-40"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($videos as $video)
                        <tr>
                            <td>
                                <img src="{{ $video->coverUrl() }}" alt="" class="w-20 rounded-sm"
                                     style="aspect-ratio: 16/9; object-fit: cover">
                            </td>
                            <td>
                                <a href="{{ route('admin.videos.edit', $video) }}" class="table-link">
                                    {{ $video->rawTranslation('ru')?->title ?: $video->youtube_id }}
                                </a>
                            </td>
                            <td class="text-ink-muted">{{ $video->event_date?->format('d.m.Y') ?? '—' }}</td>
                            <td>
                                <div class="flex gap-1">
                                    <form method="POST" action="{{ route('admin.videos.move', $video) }}">
                                        @csrf<input type="hidden" name="direction" value="up">
                                        <button class="btn-icon" @disabled($loop->first) title="Вверх" aria-label="Вверх">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 12V4M4 8l4-4 4 4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.videos.move', $video) }}">
                                        @csrf<input type="hidden" name="direction" value="down">
                                        <button class="btn-icon" @disabled($loop->last) title="Вниз" aria-label="Вниз">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 4v8M4 8l4 4 4-4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.videos.edit', $video) }}" class="btn-quiet">Изменить</a>
                                    <x-admin.delete-button :action="route('admin.videos.destroy', $video)"
                                        :subject="$video->rawTranslation('ru')?->title ?: $video->youtube_id" noun="видео" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-layouts.admin>

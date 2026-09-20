<x-layouts.admin :title="$post->title">
    <x-slot:actions>
        <a href="{{ route('admin.member-posts.index') }}" class="btn-secondary">К списку</a>
    </x-slot:actions>

    <div class="form-column space-y-6">
        <div class="card space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <x-admin.post-status-badge :status="$post->status" />
                <span class="text-ui text-ink-muted">{{ $post->typeLabel() }}</span>
            </div>

            <p class="text-reading whitespace-pre-line">{{ $post->body }}</p>

            <dl class="grid gap-x-6 gap-y-1 text-ui sm:grid-cols-2">
                <div class="flex gap-2">
                    <dt class="text-ink-muted">Автор:</dt>
                    <dd>
                        @if($post->author)
                            <a href="{{ route('admin.profiles.show', $post->author) }}" class="text-accent">{{ $post->author->full_name ?: 'Без имени' }}</a>
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div class="flex gap-2">
                    <dt class="text-ink-muted">Создан:</dt>
                    <dd>{{ $post->created_at?->format('d.m.Y H:i') }}</dd>
                </div>
                <div class="flex gap-2">
                    <dt class="text-ink-muted">Дата события:</dt>
                    <dd>{{ $post->event_date?->format('d.m.Y') ?? '—' }}</dd>
                </div>
                <div class="flex gap-2">
                    <dt class="text-ink-muted">Место:</dt>
                    <dd>{{ $post->location ?: '—' }}</dd>
                </div>
                <div class="flex gap-2 sm:col-span-2">
                    <dt class="text-ink-muted">Ссылка для связи:</dt>
                    <dd class="min-w-0 break-all">
                        @if($post->contact_url)
                            <a href="{{ $post->contact_url }}" target="_blank" rel="noopener noreferrer" class="text-accent">{{ $post->contact_url }}</a>
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div class="flex gap-2">
                    <dt class="text-ink-muted">Решение:</dt>
                    <dd>{{ $post->moderated_at?->format('d.m.Y H:i') ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @unless($post->isApproved())
                <x-admin.confirm-button
                    :action="route('admin.member-posts.approve', $post)"
                    label="Одобрить"
                    trigger="btn-primary"
                    title="Одобрить публикацию?"
                    message="Она появится в кабинете у всех участниц, а они получат уведомление в Telegram." />
            @endunless
            @unless($post->isRejected())
                <x-admin.confirm-button
                    :action="route('admin.member-posts.reject', $post)"
                    label="Отклонить"
                    trigger="btn-danger"
                    title="Отклонить публикацию?"
                    message="Публикация будет скрыта от других участниц. Автор увидит пометку об отказе."
                    :danger="true" />
            @endunless
            <x-admin.delete-button
                :action="route('admin.member-posts.destroy', $post)"
                :subject="$post->title"
                noun="публикацию" />
        </div>
    </div>
</x-layouts.admin>

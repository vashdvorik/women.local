<x-layouts.admin title="Страница не найдена">
    <div class="flex flex-col items-center text-center py-24">
        <p class="text-[64px] font-bold leading-none">404</p>
        <p class="text-ui-strong font-semibold text-ink-muted mt-4">Страница не найдена</p>
        <p class="text-section font-semibold mt-2">Похоже, этот адрес больше не существует.</p>
        <div class="flex gap-3 mt-8">
            <a href="{{ route('admin.dashboard') }}" class="btn-primary">Вернуться в инфопанель</a>
            <a href="{{ url('/') }}" class="btn-secondary">Открыть сайт</a>
        </div>
    </div>
</x-layouts.admin>

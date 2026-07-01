@extends('account.layout')
@section('title', 'Мой профиль')

@section('content')
<div class="max-w-2xl">

    {{-- Header --}}
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex items-center gap-4">
            @if($accountUser->avatar_path)
            <img src="{{ Storage::url($accountUser->avatar_path) }}"
                 alt="{{ $accountUser->full_name }}"
                 class="h-16 w-16 shrink-0 rounded-2xl object-cover shadow-md">
            @else
            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl text-xl font-bold text-white shadow-md"
                 style="background:linear-gradient(135deg,#7c3aed,#4f46e5)">
                {{ mb_strtoupper(mb_substr($accountUser->full_name ?? '?', 0, 1)) }}
            </div>
            @endif
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-[#0f172a]">Мой профиль</h1>
                <p class="mt-1 text-sm text-gray-500">Информация о тебе, видимая участникам сообщества</p>
            </div>
        </div>
        <a href="{{ route('account.profile.edit') }}"
           class="inline-flex h-10 items-center gap-2 rounded-xl border border-gray-200 bg-white px-5
                  text-sm font-semibold text-[#0f172a] shadow-sm transition hover:border-brand-400 hover:text-brand-600
                  sm:shrink-0">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828A2 2 0 0110 16H8v-2a2 2 0 01.586-1.414z"/>
            </svg>
            Редактировать
        </a>
    </div>

    @if(session('success'))
    <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
        {{ session('success') }}
    </div>
    @endif

    {{-- Identity --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <h2 class="mb-5 text-xs font-semibold uppercase tracking-widest text-gray-400">Личные данные</h2>
        <div class="space-y-4">

            <div>
                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">Имя и фамилия</p>
                <p class="mt-1 text-sm font-medium text-[#0f172a]">
                    {{ $accountUser->full_name ?: '—' }}
                </p>
            </div>

            <div>
                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">Telegram</p>
                <p class="mt-1 text-sm font-medium">
                    @if($accountUser->telegram_username)
                    <a href="https://t.me/{{ $accountUser->telegram_username }}" target="_blank"
                       class="text-brand-600 hover:underline">
                        {{ '@' . $accountUser->telegram_username }}
                    </a>
                    @else
                    <span class="text-gray-400">Не указан</span>
                    @endif
                </p>
            </div>

        </div>
    </div>

    {{-- About --}}
    <div class="mt-5 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <h2 class="mb-5 text-xs font-semibold uppercase tracking-widest text-gray-400">О себе</h2>
        <div class="space-y-5">

            <div>
                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">Кто ты и чем занимаешься?</p>
                <p class="mt-1 whitespace-pre-line text-sm text-[#0f172a]">
                    {{ $accountUser->description ?: '—' }}
                </p>
            </div>

            <div>
                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">Что ждёшь от сообщества?</p>
                <p class="mt-1 whitespace-pre-line text-sm text-[#0f172a]">
                    {{ $accountUser->expectation ?: '—' }}
                </p>
            </div>

        </div>
    </div>

    {{-- Danger zone --}}
    <div class="mt-10 rounded-2xl border border-red-100 bg-red-50 p-6">
        <h2 class="mb-1 text-sm font-semibold text-red-700">Удалить профиль</h2>
        <p class="mb-4 text-sm text-red-500">Все ваши данные будут безвозвратно удалены. Это действие нельзя отменить.</p>

        <form id="delete-profile-form" action="{{ route('account.profile.delete') }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="button" onclick="confirmDelete()"
                class="inline-flex h-10 items-center gap-2 rounded-xl border border-red-300 bg-white px-5
                       text-sm font-semibold text-red-600 transition hover:bg-red-600 hover:text-white hover:border-red-600">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m2 0a1 1 0 00-1-1h-4a1 1 0 00-1 1H5"/>
                </svg>
                Удалить профиль
            </button>
        </form>
    </div>

</div>

@push('scripts')
<script>
function confirmDelete() {
    if (confirm('Вы уверены? Профиль и все данные будут удалены безвозвратно.')) {
        document.getElementById('delete-profile-form').submit();
    }
}
</script>
@endpush
@endsection

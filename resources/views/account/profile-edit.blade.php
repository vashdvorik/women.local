@extends('account.layout')
@section('title', 'Редактировать профиль')

@section('content')
<div class="max-w-2xl">

    {{-- Header --}}
    <div class="mb-8 flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#0f172a]">Редактировать профиль</h1>
            <p class="mt-1.5 text-sm text-gray-500">Изменения сразу видны другим участникам</p>
        </div>
        <a href="{{ route('account.profile') }}"
           class="inline-flex h-10 shrink-0 items-center gap-2 rounded-xl border border-gray-200 bg-white px-5
                  text-sm font-semibold text-gray-500 shadow-sm transition hover:border-gray-300 hover:text-gray-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Назад
        </a>
    </div>

    <form action="{{ route('account.profile.update') }}" method="POST" class="space-y-5">
        @csrf

        {{-- Identity --}}
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="mb-5 text-xs font-semibold uppercase tracking-widest text-gray-400">Личные данные</h2>
            <div class="space-y-5">

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-[#0f172a]">
                        Имя и фамилия <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="full_name"
                           value="{{ old('full_name', $accountUser->full_name) }}"
                           maxlength="120" required
                           placeholder="Иван Иванов"
                           class="w-full rounded-xl border px-4 py-3 text-sm text-[#0f172a] placeholder-gray-300
                                  transition duration-150 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent
                                  {{ $errors->has('full_name') ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-gray-50 focus:bg-white' }}">
                    @error('full_name')
                    <p class="mt-1.5 flex items-center gap-1 text-xs text-red-600">
                        <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                <div class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-3">
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
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="mb-5 text-xs font-semibold uppercase tracking-widest text-gray-400">О себе</h2>
            <div class="space-y-5">

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-[#0f172a]">
                        Кто ты и чем занимаешься?
                    </label>
                    <textarea name="description" rows="4" maxlength="1000"
                              placeholder="Роль, сфера, компания. Ссылки приветствуются."
                              class="w-full resize-none rounded-xl border px-4 py-3 text-sm text-[#0f172a] placeholder-gray-300
                                     transition duration-150 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent
                                     {{ $errors->has('description') ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-gray-50 focus:bg-white' }}">{{ old('description', $accountUser->description) }}</textarea>
                    <p class="mt-1.5 text-xs text-gray-400">Используется алгоритмом для подбора партнёров · до 1000 символов</p>
                    @error('description')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-[#0f172a]">
                        Что ждёшь от сообщества?
                    </label>
                    <textarea name="expectation" rows="3" maxlength="1000"
                              placeholder="Чего ищешь и чем можешь быть полезен."
                              class="w-full resize-none rounded-xl border px-4 py-3 text-sm text-[#0f172a] placeholder-gray-300
                                     transition duration-150 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent
                                     {{ $errors->has('expectation') ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-gray-50 focus:bg-white' }}">{{ old('expectation', $accountUser->expectation) }}</textarea>
                    @error('expectation')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between">
            <button type="submit"
                class="inline-flex h-11 items-center gap-2 rounded-xl px-6 text-sm font-semibold text-white
                       transition-all duration-150 hover:-translate-y-px hover:shadow-lg active:translate-y-0"
                style="background:linear-gradient(135deg,#7c3aed,#4f46e5);box-shadow:0 4px 14px rgba(124,58,237,.3)">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Сохранить изменения
            </button>
            
        </div>

    </form>

</div>
@endsection

@extends('account.layout')
@section('title', __('account.profile.title'))

@section('content')
<div class="max-w-2xl">
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex items-center gap-4">
            @if($accountUser->avatar_path)
                <img src="{{ Storage::url($accountUser->avatar_path) }}" alt="{{ $accountUser->full_name }}" class="h-16 w-16 shrink-0 rounded-2xl object-cover shadow-md">
            @else
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl text-xl font-bold text-white shadow-md" style="background:linear-gradient(135deg,#7c3aed,#4f46e5)">{{ mb_strtoupper(mb_substr($accountUser->full_name ?? '?', 0, 1)) }}</div>
            @endif
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-[#0f172a]">{{ __('account.profile.title') }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ __('account.profile.subtitle') }}</p>
            </div>
        </div>
        <a href="{{ route('account.profile.edit') }}" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-5 text-sm font-semibold text-[#0f172a] shadow-sm hover:border-brand-400 hover:text-brand-600">{{ __('account.profile.edit') }}</a>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <h2 class="mb-5 text-xs font-semibold uppercase tracking-widest text-gray-400">{{ __('account.profile.contact_info') }}</h2>
        <div class="space-y-4">
            <div><p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">{{ __('account.profile.full_name') }}</p><p class="mt-1 text-sm font-medium text-[#0f172a]">{{ $accountUser->full_name ?: '—' }}</p></div>
            <div><p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">{{ __('account.telegram') }}</p><p class="mt-1 text-sm font-medium">@if($accountUser->telegram_username)<a href="https://t.me/{{ $accountUser->telegram_username }}" target="_blank" class="text-brand-600 hover:underline">{{ '@' . $accountUser->telegram_username }}</a>@else<span class="text-gray-400">{{ __('account.not_specified') }}</span>@endif</p></div>
        </div>
    </div>

    <div class="mt-5 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <h2 class="mb-5 text-xs font-semibold uppercase tracking-widest text-gray-400">{{ __('account.profile.business_profile') }}</h2>
        <div class="space-y-5">
            <div><p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">{{ __('account.profile.description') }}</p><p class="mt-1 whitespace-pre-line text-sm text-[#0f172a]">{{ $accountUser->description ?: __('account.profile.description_empty') }}</p></div>
            <div><p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">{{ __('account.profile.expectation') }}</p><p class="mt-1 whitespace-pre-line text-sm text-[#0f172a]">{{ $accountUser->expectation ?: __('account.profile.expectation_empty') }}</p></div>
        </div>
    </div>

    <div class="mt-10 rounded-2xl border border-red-100 bg-red-50 p-6">
        <h2 class="mb-1 text-sm font-semibold text-red-700">{{ __('account.profile.delete_title') }}</h2>
        <p class="mb-4 text-sm text-red-500">{{ __('account.profile.delete_text') }}</p>
        <form id="delete-profile-form" action="{{ route('account.profile.delete') }}" method="POST">@csrf @method('DELETE')
            <button type="button" onclick="confirmDelete()" class="rounded-xl border border-red-300 bg-white px-5 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-600 hover:text-white">{{ __('account.profile.delete_button') }}</button>
        </form>
    </div>
</div>
@push('scripts')
<script>function confirmDelete(){ if(confirm(@json(__('account.profile.delete_confirm')))){ document.getElementById('delete-profile-form').submit(); } }</script>
@endpush
@endsection

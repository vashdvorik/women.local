@extends('account.layout')
@section('title', __('account.profile_edit.title'))

@section('content')
<div class="max-w-2xl">
    <div class="mb-8">
        <a href="{{ route('account.profile') }}" class="mb-4 inline-flex text-sm font-medium text-gray-400 hover:text-brand-600">{{ __('account.back') }}</a>
        <h1 class="text-2xl font-bold tracking-tight text-[#0f172a]">{{ __('account.profile_edit.title') }}</h1>
        <p class="mt-1.5 text-sm text-gray-500">{{ __('account.profile_edit.subtitle') }}</p>
    </div>
    <form action="{{ route('account.profile.update') }}" method="POST" class="space-y-5 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        @csrf
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('account.profile.full_name') }}</label>
            <input name="full_name" value="{{ old('full_name', $accountUser->full_name) }}" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm" maxlength="120">
            @error('full_name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('account.profile_edit.description_label') }}</label>
            <textarea name="description" rows="6" maxlength="1000" placeholder="{{ __('account.profile_edit.description_placeholder') }}" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm">{{ old('description', $accountUser->description) }}</textarea>
            @error('description')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('account.profile_edit.expectation_label') }}</label>
            <textarea name="expectation" rows="6" maxlength="1000" placeholder="{{ __('account.profile_edit.expectation_placeholder') }}" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm">{{ old('expectation', $accountUser->expectation) }}</textarea>
            @error('expectation')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>
        <button class="rounded-xl px-5 py-3 text-sm font-semibold text-white" style="background:linear-gradient(135deg,#7c3aed,#4f46e5)">{{ __('account.profile_edit.save') }}</button>
    </form>
</div>
@endsection

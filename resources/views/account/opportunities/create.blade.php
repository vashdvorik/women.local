@extends('account.layout')

@section('title', __('account.opportunities.new_title'))

@section('content')
    <a href="{{ route('account.opportunities.index') }}" class="mb-6 inline-flex text-sm font-bold text-teal-700 hover:text-teal-900">
        {{ __('account.back') }}
    </a>

    <div class="mb-8">
        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-teal-700">{{ __('account.nav.opportunities') }}</p>
        <h1 class="mt-2 text-3xl font-black text-slate-950">{{ __('account.opportunities.new_title') }}</h1>
        <p class="mt-2 max-w-2xl text-slate-600">{{ __('account.opportunities.new_subtitle') }}</p>
    </div>

    <form method="POST" action="{{ route('account.opportunities.store') }}" class="max-w-3xl rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
        @csrf

        <div class="space-y-5">
            <div>
                <label class="mb-2 block text-sm font-bold text-slate-800">{{ __('account.opportunities.type') }}</label>
                <select name="type" class="min-h-12 w-full rounded-2xl border border-slate-200 px-4 text-slate-900 outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100">
                    @foreach(['project' => '💼', 'meeting' => '🤝', 'event' => '📅'] as $type => $emoji)
                        <option value="{{ $type }}" @selected(old('type', 'project') === $type)>
                            {{ $emoji }} {{ __('account.types.' . $type) }}
                        </option>
                    @endforeach
                </select>
                @error('type')<p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-slate-800">{{ __('account.opportunities.title_field') }}</label>
                <input
                    name="title"
                    value="{{ old('title') }}"
                    placeholder="{{ __('account.opportunities.title_placeholder') }}"
                    class="min-h-12 w-full rounded-2xl border border-slate-200 px-4 text-slate-900 outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
                >
                @error('title')<p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-slate-800">{{ __('account.opportunities.description') }}</label>
                <textarea
                    name="body"
                    rows="8"
                    placeholder="{{ __('account.opportunities.description_placeholder') }}"
                    class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-slate-900 outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
                >{{ old('body') }}</textarea>
                @error('body')<p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-bold text-slate-800">{{ __('account.opportunities.date') }} <span class="text-slate-400">({{ __('account.optional') }})</span></label>
                    <input
                        type="date"
                        name="event_date"
                        value="{{ old('event_date') }}"
                        class="min-h-12 w-full rounded-2xl border border-slate-200 px-4 text-slate-900 outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
                    >
                    @error('event_date')<p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold text-slate-800">{{ __('account.opportunities.location') }} <span class="text-slate-400">({{ __('account.optional') }})</span></label>
                    <input
                        name="location"
                        value="{{ old('location') }}"
                        placeholder="{{ __('account.opportunities.location_placeholder') }}"
                        class="min-h-12 w-full rounded-2xl border border-slate-200 px-4 text-slate-900 outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
                    >
                    @error('location')<p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-slate-800">{{ __('account.opportunities.contact_url') }} <span class="text-slate-400">({{ __('account.optional') }})</span></label>
                <input
                    type="url"
                    name="contact_url"
                    value="{{ old('contact_url') }}"
                    placeholder="{{ __('account.opportunities.contact_placeholder') }}"
                    class="min-h-12 w-full rounded-2xl border border-slate-200 px-4 text-slate-900 outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
                >
                @error('contact_url')<p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <button class="mt-7 rounded-full bg-orange-600 px-7 py-3 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition hover:bg-orange-700">
            {{ __('account.opportunities.submit') }}
        </button>
    </form>
@endsection

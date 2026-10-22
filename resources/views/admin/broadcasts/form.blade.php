@php
    $locales = ['ru' => __('Русский'), 'en' => 'English', 'ro' => 'Română'];
    $isEdit = $broadcast->exists;
    $buttons = array_values((array) $broadcast->buttons);
    $mode = old('send_mode', $broadcast->status === \App\Models\Broadcast::STATUS_SCHEDULED ? 'later' : 'now');
    $scheduled = old('scheduled_at', $broadcast->scheduledLocal()?->format('Y-m-d\TH:i'));
    $audience = old('audience', $broadcast->audience ?: \App\Models\Broadcast::AUDIENCE_APPROVED);
    $minTime = now(\App\Models\Broadcast::TIMEZONE)->addMinutes(2)->format('Y-m-d\TH:i');
@endphp

<x-layouts.admin :title="$isEdit ? __('Рассылка') : __('Новая рассылка')">
    <form method="POST" enctype="multipart/form-data" class="max-w-3xl space-y-6"
          action="{{ $isEdit ? route('admin.broadcasts.update', $broadcast) : route('admin.broadcasts.store') }}"
          x-data="{ mode: @js($mode), audience: @js($audience), counts: @js($counts) }">
        @csrf
        @if($isEdit) @method('PUT') @endif
        <x-forms.error-summary />

        <div class="card space-y-1 text-ui text-ink-muted">
            <p>{{ __('Сообщение придёт участницам в Telegram от имени бота. Каждая получит текст на своём языке (из профиля); если перевода нет, придёт русский. Сначала отправьте пробное сообщение себе.') }}</p>
        </div>

        {{-- Кому --}}
        <section class="card space-y-3">
            <h2 class="text-section font-semibold">{{ __('Кому отправить') }}</h2>
            @foreach(\App\Models\Broadcast::AUDIENCES as $key)
                <label class="flex items-start gap-3">
                    <input type="radio" name="audience" value="{{ $key }}" x-model="audience" class="mt-1" @checked($audience === $key)>
                    <span>
                        <span class="text-ui font-medium">{{ \App\Models\Broadcast::audienceTitle($key) }}</span>
                        <span class="text-ui text-ink-muted"> — {{ __('получат: :count', ['count' => $counts[$key]]) }}</span>
                    </span>
                </label>
            @endforeach
        </section>

        {{-- Текст --}}
        <section class="card space-y-4">
            <div>
                <h2 class="text-section font-semibold">{{ __('Текст сообщения') }}</h2>
                <p class="field-hint mt-1">{!! __('Обычный текст. Можно выделять: <b>жирный</b>, <i>курсив</i>, <u>подчёркнутый</u>, <s>зачёркнутый</s> и ссылку <code>&lt;a href="https://…"&gt;текст&lt;/a&gt;</code>. Ссылки в тексте Telegram делает кликабельными сам.') !!}</p>
            </div>
            @foreach($locales as $code => $label)
                <div class="space-y-1">
                    <label for="text_{{ $code }}" class="field-label">{{ __('Текст :language', ['language' => $label]) }}@if($code === 'ru')<span class="text-danger"> *</span>@endif</label>
                    <textarea id="text_{{ $code }}" name="texts[{{ $code }}]" rows="{{ $code === 'ru' ? 7 : 5 }}" maxlength="{{ \App\Support\BroadcastText::MAX_TEXT }}"
                              class="field-input @error('texts.'.$code) field-input--invalid @enderror">{{ old('texts.'.$code, $broadcast->texts[$code] ?? '') }}</textarea>
                    @error('texts.'.$code)<p class="field-error">{{ $message }}</p>@enderror
                </div>
            @endforeach
        </section>

        {{-- Фото --}}
        <section class="card space-y-3">
            <h2 class="text-section font-semibold">{{ __('Фото') }} <span class="text-ink-faint font-normal">{{ __('необязательно') }}</span></h2>
            @if($broadcast->hasPhoto())
                <p class="text-ui text-ink-muted">{{ __('К рассылке прикреплено фото.') }}</p>
                <label class="flex items-center gap-2 text-ui">
                    <input type="checkbox" name="remove_photo" value="1" @checked(old('remove_photo'))> {{ __('Убрать фото') }}
                </label>
            @endif
            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="field-input @error('photo') field-input--invalid @enderror">
            @error('photo')<p class="field-error">{{ $message }}</p>@enderror
            <p class="field-hint">{{ __('JPG, PNG или WebP до 8 МБ. Фото придёт целиком, без обрезки, а текст станет подписью к нему: с фото текст не длиннее 1000 знаков.') }}</p>
        </section>

        {{-- Кнопки --}}
        <section class="card space-y-4">
            <div>
                <h2 class="text-section font-semibold">{{ __('Кнопки под сообщением') }} <span class="text-ink-faint font-normal">{{ __('необязательно, до :count', ['count' => \App\Models\Broadcast::MAX_BUTTONS]) }}</span></h2>
                <p class="field-hint mt-1">{{ __('Нажатие открывает ссылку. Например, «Зарегистрироваться» и адрес страницы. Подпись по-английски и по-румынски необязательна: без неё покажется русская.') }}</p>
            </div>
            @for($i = 0; $i < \App\Models\Broadcast::MAX_BUTTONS; $i++)
                @php $button = $buttons[$i] ?? []; @endphp
                <div class="space-y-2 rounded-lg border border-hairline p-3">
                    <p class="text-ui font-medium">{{ __('Кнопка :number', ['number' => $i + 1]) }}</p>
                    <x-forms.field :label="__('Адрес (https://…)')" name="buttons[{{ $i }}][url]" error="buttons.{{ $i }}.url"
                                   :value="$button['url'] ?? ''" placeholder="https://womenhub.innovation.md/…" />
                    <div class="grid gap-3 sm:grid-cols-3">
                        @foreach($locales as $code => $label)
                            <x-forms.field :label="__('Подпись :language', ['language' => $label])" name="buttons[{{ $i }}][label][{{ $code }}]"
                                           error="buttons.{{ $i }}.label.{{ $code }}"
                                           :value="$button['label'][$code] ?? ''" :placeholder="$code === 'ru' ? __('Зарегистрироваться') : null" />
                        @endforeach
                    </div>
                </div>
            @endfor
        </section>

        {{-- Когда --}}
        <section class="card space-y-3">
            <h2 class="text-section font-semibold">{{ __('Когда отправить') }}</h2>
            <label class="flex items-center gap-2 text-ui">
                <input type="radio" name="send_mode" value="now" x-model="mode"> {{ __('Сразу после нажатия «Отправить»') }}
            </label>
            <label class="flex items-center gap-2 text-ui">
                <input type="radio" name="send_mode" value="later" x-model="mode"> {{ __('По расписанию') }}
            </label>
            <div x-show="mode === 'later'" x-cloak class="space-y-1">
                <input type="datetime-local" name="scheduled_at" value="{{ $scheduled }}" min="{{ $minTime }}"
                       class="field-input !w-auto @error('scheduled_at') field-input--invalid @enderror">
                <p class="field-hint">{{ __('Время по Кишинёву. Отправка начнётся в течение минуты после этого времени.') }}</p>
                @error('scheduled_at')<p class="field-error">{{ $message }}</p>@enderror
            </div>
        </section>

        {{-- Проба --}}
        <section class="card space-y-3">
            <h2 class="text-section font-semibold">{{ __('Пробная отправка') }}</h2>
            <x-forms.field :label="__('Мой Telegram ID')" name="test_chat" error="test_chat" :value="$testChat" placeholder="123456789"
                           :hint="__('Число. Узнать его можно у бота @userinfobot. Сообщение придёт только вам (по одному на каждый заполненный язык); вы должны хотя бы раз написать нашему боту. Запоминается.')" />
            <button type="submit" name="action" value="test" class="btn-secondary">{{ __('Отправить мне для проверки') }}</button>
        </section>

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" name="action" value="send" class="btn-primary"
                    @click="if (!confirm(mode === 'later'
                        ? @js(__('Запланировать рассылку? Получательниц сейчас:')) + ' ' + counts[audience]
                        : @js(__('Отправить сообщение сейчас? Получательниц:')) + ' ' + counts[audience] + '. ' + @js(__('Отменить отправку после запуска нельзя для тех, кому уже ушло.'))) ) $event.preventDefault()">
                <span x-text="mode === 'later' ? @js(__('Запланировать')) : @js(__('Отправить всем'))"></span>
            </button>
            <button type="submit" name="action" value="draft" class="btn-secondary">{{ __('Сохранить черновик') }}</button>
            <a href="{{ route('admin.broadcasts.index') }}" class="btn-quiet">{{ __('Отмена') }}</a>
        </div>
    </form>
</x-layouts.admin>

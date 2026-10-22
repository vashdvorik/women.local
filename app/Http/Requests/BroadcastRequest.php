<?php

namespace App\Http\Requests;

use App\Models\Broadcast;
use App\Support\BroadcastText;
use App\Support\Locales;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/** Форма рассылки: текст на трёх языках, до трёх кнопок-ссылок, фото, получатели и время отправки. */
class BroadcastRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'action' => ['required', Rule::in(['draft', 'test', 'send'])],
            'audience' => ['required', Rule::in(Broadcast::AUDIENCES)],
            'send_mode' => ['nullable', Rule::in(['now', 'later'])],
            'scheduled_at' => ['nullable', 'required_if:send_mode,later', 'date_format:Y-m-d\TH:i'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'remove_photo' => ['boolean'],
            'test_chat' => ['nullable', 'required_if:action,test', 'regex:/^-?\d{4,15}$/'],
            'buttons' => ['nullable', 'array', 'max:'.Broadcast::MAX_BUTTONS],
        ];

        foreach (Locales::ALL as $locale) {
            $rules["texts.$locale"] = $locale === Locales::PRIMARY
                ? ['required', 'string', 'max:'.BroadcastText::MAX_TEXT]
                : ['nullable', 'string', 'max:'.BroadcastText::MAX_TEXT];
        }

        for ($i = 0; $i < Broadcast::MAX_BUTTONS; $i++) {
            // Telegram принимает в кнопке только полный адрес; http не пропускаем: ссылка с открытым трафиком в рассылке ни к чему.
            $rules["buttons.$i.url"] = ['nullable', 'url:https', 'max:500'];
            $rules["buttons.$i.label.ru"] = ["required_with:buttons.$i.url", 'nullable', 'string', 'max:64'];
            $rules["buttons.$i.label.en"] = ['nullable', 'string', 'max:64'];
            $rules["buttons.$i.label.ro"] = ['nullable', 'string', 'max:64'];
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'texts.ru' => __('текст по-русски'),
            'texts.en' => __('текст по-английски'),
            'texts.ro' => __('текст по-румынски'),
            'scheduled_at' => __('время отправки'),
            'test_chat' => __('Telegram ID для пробной отправки'),
            'photo' => __('фото'),
        ];
    }

    public function messages(): array
    {
        return [
            'buttons.*.url.url' => __('Адрес кнопки должен начинаться с https://'),
            'buttons.*.label.ru.required_with' => __('У кнопки с адресом нужна подпись по-русски.'),
            'test_chat.regex' => __('Telegram ID — это число, например 123456789.'),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // С фото текст становится подписью, а у неё предел короче, чем у обычного сообщения.
            $broadcast = $this->route('broadcast');
            $hasPhoto = $this->hasFile('photo') || ($broadcast instanceof Broadcast && $broadcast->hasPhoto() && ! $this->boolean('remove_photo'));

            if ($hasPhoto) {
                foreach ((array) $this->input('texts', []) as $locale => $text) {
                    if (BroadcastText::length((string) $text) > BroadcastText::MAX_CAPTION) {
                        $validator->errors()->add("texts.$locale", __('С фото текст не длиннее :max знаков (подпись к фото в Telegram короче обычного сообщения).', ['max' => BroadcastText::MAX_CAPTION]));
                    }
                }
            }

            if ($this->input('action') === 'send' && $this->input('send_mode') === 'later' && ! $validator->errors()->has('scheduled_at')) {
                if ($this->scheduledAtUtc()?->lte(now()->addMinute())) {
                    $validator->errors()->add('scheduled_at', __('Время отправки должно быть не раньше чем через минуту.'));
                }
            }
        });
    }

    /** Время отправки, введённое по Кишинёву, в UTC. */
    public function scheduledAtUtc(): ?Carbon
    {
        $value = $this->input('scheduled_at');

        return filled($value) ? Carbon::createFromFormat('Y-m-d\TH:i', (string) $value, Broadcast::TIMEZONE)->utc() : null;
    }

    /**
     * Кнопки из формы: только заполненные, с подписями на языках.
     *
     * @return list<array{url: string, label: array<string, string>}>
     */
    public function cleanButtons(): array
    {
        $buttons = [];

        foreach ((array) $this->input('buttons', []) as $button) {
            $url = trim((string) ($button['url'] ?? ''));

            if ($url === '') {
                continue;
            }

            $buttons[] = [
                'url' => $url,
                'label' => collect(Locales::ALL)
                    ->mapWithKeys(fn (string $locale) => [$locale => trim((string) ($button['label'][$locale] ?? ''))])
                    ->filter()->all(),
            ];
        }

        return array_slice($buttons, 0, Broadcast::MAX_BUTTONS);
    }

    /** @return array<string, string> */
    public function cleanTexts(): array
    {
        return collect(Locales::ALL)
            ->mapWithKeys(fn (string $locale) => [$locale => trim((string) $this->input("texts.$locale"))])
            ->all();
    }
}

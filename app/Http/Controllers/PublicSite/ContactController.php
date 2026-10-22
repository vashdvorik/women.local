<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Mail\ContactMessageMail;
use App\Models\ContactMessage;
use App\Support\ContactForm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Throwable;

/**
 * Форма «Связаться с нами» на странице «Контакты». Обращение сначала сохраняется в базе (раздел «Обращения» в админке),
 * затем уходит письмом на почту команды (config site.contacts.email). Если почта недоступна, обращение всё равно не
 * теряется: причина записывается в mail_error, а посетительница видит обычное «Сообщение отправлено».
 *
 * Защита без капчи: скрытое поле-приманка, метка времени формы (ContactForm) и не больше трёх обращений в час с одного
 * адреса. Сообщения формы — на трёх языках в самой вёрстке, контроллер ставит только признак результата.
 */
class ContactController extends Controller
{
    /** Обращений в час с одного адреса. */
    private const PER_HOUR = 3;

    public function store(Request $request): RedirectResponse
    {
        $back = redirect()->to(route('contact').'#contact-form');

        // Приманку заполняют только боты: ничего не сохраняем, но и не подсказываем, что их раскусили.
        if (filled($request->input('website'))) {
            return $back->with('contact_sent', true);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:191'],
            'phone' => ['nullable', 'string', 'max:32', 'regex:/^[0-9+\-\s().]{5,32}$/'],
            'organization' => ['nullable', 'string', 'max:191'],
            'message' => ['required', 'string', 'min:5', 'max:4000'],
        ]);

        if ($validator->fails()) {
            return $back->with('contact_error', 'invalid')
                ->withErrors($validator)
                ->withInput($request->only('name', 'email', 'phone', 'message'));
        }

        if (! ContactForm::isValid($request->input('form_token'))) {
            return $back->with('contact_error', 'token')->withInput($request->only('name', 'email', 'phone', 'message'));
        }

        $limitKey = 'contact-form:'.$request->ip();

        if (RateLimiter::tooManyAttempts($limitKey, self::PER_HOUR)) {
            return $back->with('contact_error', 'throttled')->withInput($request->only('name', 'email', 'phone', 'message'));
        }

        RateLimiter::hit($limitKey, 3600);

        $data = $validator->validated();
        $locale = in_array(app()->getLocale(), ['ru', 'en', 'ro'], true) ? app()->getLocale() : 'ru';

        $contact = ContactMessage::create([
            'name' => trim($data['name']),
            'email' => mb_strtolower(trim($data['email'])),
            'phone' => filled($data['phone'] ?? null) ? trim($data['phone']) : null,
            'organization' => filled($data['organization'] ?? null) ? trim($data['organization']) : null,
            'message' => trim($data['message']),
            'locale' => $locale,
        ]);

        try {
            Mail::to(config('site.contacts.email'))->send(new ContactMessageMail($contact));
            $contact->forceFill(['mail_sent_at' => now(), 'mail_error' => null])->save();
        } catch (Throwable $e) {
            report($e);
            Log::warning('Contact form mail failed', ['contact_message_id' => $contact->id, 'error' => $e->getMessage()]);
            $contact->forceFill(['mail_error' => mb_substr($e->getMessage(), 0, 500)])->save();
        }

        return $back->with('contact_sent', true);
    }
}

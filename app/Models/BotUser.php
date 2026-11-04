<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Plan;
use App\Mail\ResetParticipantPasswordMail;
use App\Mail\VerifyParticipantEmailMail;
use App\Support\BotMessages;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

/**
 * Участница платформы. Входит по почте и паролю или через Google (guard «participant»); Telegram — необязательное
 * подключение для уведомлений (telegram_id заполняется, когда она переходит по ссылке из кабинета).
 */
class BotUser extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword, HasFactory;
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'telegram_id',
        'telegram_username',
        'email',
        'contact_email',
        'phone',
        'locale',
        'avatar_path',
        'first_name',
        'full_name',
        'description',
        'expectation',
        'status',
        'approved_at',
        'embedding',
        'embedding_updated_at',
    ];

    // Пароль, подтверждение почты, google_id, отметка «анкета заполнена» и тариф намеренно не в $fillable: их меняют
    // только сервисы и контроллеры входа через forceFill().
    // Тариф (plan, plan_ends_at, plan_notified_stage) намеренно не в $fillable: его меняет только
    // App\Services\Subscriptions\SubscriptionService, чтобы ни форма профиля, ни бот не могли выдать себе доступ.
    protected $hidden = ['password', 'remember_token', 'email_code_hash'];

    protected $casts = [
        'telegram_id'          => 'integer',
        'email_verified_at'    => 'datetime',
        'email_code_expires_at' => 'datetime',
        'profile_completed_at' => 'datetime',
        'password'             => 'hashed',
        'approved_at'          => 'datetime',
        'cabinet_opened_at'    => 'datetime',
        'last_seen_at'         => 'datetime',
        'cabinet_reminded_at'  => 'datetime',
        'cabinet_seen'         => 'array',
        'plan_ends_at'         => 'datetime',
        'plan_notified_stage'  => 'integer',
        'embedding'            => 'array',
        'embedding_updated_at' => 'datetime',
        'is_hidden'            => 'boolean',
    ];

    /**
     * Имя и фамилия без повтора: «Иванова Анна (Иванова Анна)» показывается и сохраняется как «Иванова Анна». Такое имя
     * попадает из старых записей и профилей, где к имени дописывали его же в скобках.
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => self::tidyName($value),
            set: fn (?string $value): ?string => self::tidyName($value),
        );
    }

    public static function tidyName(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }

        $name = trim((string) preg_replace('/\s+/u', ' ', $name));

        if (preg_match('/^(.+?)\s*\(\s*(.+?)\s*\)$/u', $name, $match) === 1) {
            $words = fn (string $text): array => collect(preg_split('/[\s,]+/u', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY) ?: [])->sort()->values()->all();

            if ($words($match[1]) === $words($match[2])) {
                return trim($match[1]);
            }
        }

        return $name;
    }

    /**
     * Почта, которую показываем людям: указанная участницей «для связи», а если её нет, регистрационная. Вход в кабинет и
     * письма платформы всегда идут на регистрационную (email).
     */
    public function displayEmail(): ?string
    {
        $contact = trim((string) $this->contact_email);

        return $contact !== '' ? $contact : ($this->email ?: null);
    }

    // ------------------------------------------------------------------ вход по почте

    public function hasVerifiedEmail(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function markEmailAsVerified(): void
    {
        if ($this->email_verified_at === null) {
            $this->forceFill(['email_verified_at' => now()])->save();
        }
    }

    /** Анкета (имя и телефон) заполнена: после этого предлагаются тарифы. */
    public function isProfileComplete(): bool
    {
        return $this->profile_completed_at !== null;
    }

    public function hasTelegram(): bool
    {
        return $this->telegram_id !== null;
    }

    /** Сколько минут действует код из письма и сколько раз его можно ввести неверно. */
    public const EMAIL_CODE_MINUTES = 15;
    public const EMAIL_CODE_MAX_ATTEMPTS = 5;

    /**
     * Письмо подтверждения почты на языке, на котором участница открыла сайт: кнопка-ссылка (действует сутки) и рядом
     * 5-значный код (15 минут). Код вводят на странице «Подтвердите почту» там же, где регистрировались. Каждая отправка
     * выпускает новый код и обнуляет попытки; в базе лежит только его хеш.
     */
    public function sendEmailVerification(): void
    {
        $url = URL::temporarySignedRoute('account.verification.verify', now()->addDay(), [
            'id' => $this->getKey(),
            'hash' => sha1((string) $this->email),
        ]);

        $code = str_pad((string) random_int(0, 99999), 5, '0', STR_PAD_LEFT);

        $this->forceFill([
            'email_code_hash' => $this->emailCodeHash($code),
            'email_code_expires_at' => now()->addMinutes(self::EMAIL_CODE_MINUTES),
            'email_code_attempts' => 0,
        ])->save();

        Mail::to($this->email)->send(new VerifyParticipantEmailMail($this, $url, app()->getLocale(), $code));
    }

    /** Хеш привязан к адресу: после смены почты прежний код недействителен. */
    private function emailCodeHash(string $code): string
    {
        return hash_hmac('sha256', $this->getKey().'|'.$this->email.'|'.$code, (string) config('app.key'));
    }

    /**
     * Проверка кода из письма: 'ok' (почта подтверждена), 'wrong' (неверный, попытка засчитана), 'expired' (кода нет,
     * срок вышел или попытки закончились: нужен новый).
     */
    public function checkEmailCode(string $code): string
    {
        if ($this->email_code_hash === null
            || $this->email_code_expires_at === null
            || $this->email_code_expires_at->isPast()
            || $this->email_code_attempts >= self::EMAIL_CODE_MAX_ATTEMPTS) {
            return 'expired';
        }

        if (! hash_equals($this->email_code_hash, $this->emailCodeHash($code))) {
            $this->forceFill(['email_code_attempts' => $this->email_code_attempts + 1])->save();

            return $this->email_code_attempts >= self::EMAIL_CODE_MAX_ATTEMPTS ? 'expired' : 'wrong';
        }

        $this->forceFill(['email_code_hash' => null, 'email_code_expires_at' => null, 'email_code_attempts' => 0])->save();
        $this->markEmailAsVerified();

        return 'ok';
    }

    /** Сколько попыток ввода кода осталось. */
    public function emailCodeAttemptsLeft(): int
    {
        return max(0, self::EMAIL_CODE_MAX_ATTEMPTS - (int) $this->email_code_attempts);
    }

    public function sendPasswordResetNotification($token): void
    {
        $url = route('account.password.reset', ['token' => $token, 'email' => $this->email]);

        Mail::to($this->email)->send(new ResetParticipantPasswordMail($this, $url, app()->getLocale()));
    }

    /** Видит ли она контакты (телефон, Telegram) участниц: с тарифа Active. */
    public function canSeeContacts(): bool
    {
        return $this->hasPlan(Plan::Active);
    }

    /** Участница вошла в кабинет: запоминаем первый вход (по нему бот понимает, что напоминать о кабинете не нужно). */
    public function markCabinetOpened(): void
    {
        if ($this->cabinet_opened_at === null) {
            $this->forceFill(['cabinet_opened_at' => now()])->save();
        }
    }

    /** Участница открыла раздел кабинета ('matches', 'search', 'opportunities', 'assistant'…): по этому ставится галочка на главной. */
    public function markSeen(string $section): void
    {
        $seen = (array) $this->cabinet_seen;

        if (! in_array($section, $seen, true)) {
            $this->forceFill(['cabinet_seen' => [...$seen, $section]])->save();
        }
    }

    public function hasSeen(string $section): bool
    {
        return in_array($section, (array) $this->cabinet_seen, true);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Тариф, который действует прямо сейчас: сохранённый, пока не истёк срок, иначе None (тарифа нет).
     * Проверка по дате делается здесь, а не фоновой задачей: доступ закрывается ровно в момент окончания.
     */
    public function currentPlan(): Plan
    {
        $plan = Plan::fromStored($this->plan);

        if (! $plan->isPaid()) {
            return Plan::None;
        }

        return $this->plan_ends_at !== null && $this->plan_ends_at->isFuture() ? $plan : Plan::None;
    }

    /** Входит ли в действующий тариф всё, что даёт $min (Community, Active, Private). */
    public function hasPlan(Plan $min): bool
    {
        return $this->currentPlan()->includes($min);
    }

    /**
     * Участницы платформы: одобренные с любым действующим платным тарифом (Community, Active, Private). Каталог, подбор
     * контактов, AI-помощник и рассылки работают только по ним. Профили Community видны участницам Active и Private
     * (самим Community поиск закрыт), поэтому в каталог попадают все платные тарифы.
     */
    public function scopeMembers(Builder $query): Builder
    {
        return $query->approved()->withPlan(Plan::Community);
    }

    /**
     * Участницы, которых могут видеть и находить другие: члены сообщества (members) без отметки «скрыт». Каталог, поиск,
     * подбор контактов, AI-помощник и «новые участницы» берут только их. Скрытые (администратор, спонсор, партнёр) пользуются
     * платформой изнутри и получают рассылки (для них audience по-прежнему members), но другим не видны. Отметку ставит только
     * администратор платформы: в $fillable поля нет.
     */
    public function scopeDiscoverable(Builder $query): Builder
    {
        return $query->members()->where($query->getModel()->getTable().'.is_hidden', false);
    }

    /** Действующий тариф не ниже $min. */
    public function scopeWithPlan(Builder $query, Plan $min): Builder
    {
        $table = $query->getModel()->getTable();

        return $query
            ->whereIn("{$table}.plan", array_map(fn (Plan $plan): string => $plan->value, Plan::atLeast($min)))
            ->where("{$table}.plan_ends_at", '>', now());
    }

    /** Анкеты, ждущие решения администратора: поданные (профиль заполнен) и ещё не одобренные и не отклонённые. */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING)->whereNotNull('profile_completed_at');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /** Язык сообщений бота этой участнице: ru / en / ro, по умолчанию русский. */
    public function messageLocale(): string
    {
        return BotMessages::locale($this->locale);
    }

    /** Запоминает язык Telegram участницы, когда она пишет боту, чтобы решения и рассылки шли на нём. */
    public function rememberLocale(?string $languageCode): void
    {
        // Язык не пришёл (у Telegram он необязателен): то, что запомнено раньше, не трогаем.
        if (! filled($languageCode)) {
            return;
        }

        $locale = BotMessages::locale($languageCode);

        if ($this->locale !== $locale) {
            $this->update(['locale' => $locale]);
        }
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function loginTokens(): HasMany
    {
        return $this->hasMany(LoginToken::class, 'telegram_id', 'telegram_id');
    }

    public function planRequests(): HasMany
    {
        return $this->hasMany(PlanRequest::class);
    }
}

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
    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'telegram_id'          => 'integer',
        'email_verified_at'    => 'datetime',
        'profile_completed_at' => 'datetime',
        'password'             => 'hashed',
        'approved_at'          => 'datetime',
        'cabinet_opened_at'    => 'datetime',
        'cabinet_reminded_at'  => 'datetime',
        'cabinet_seen'         => 'array',
        'plan_ends_at'         => 'datetime',
        'plan_notified_stage'  => 'integer',
        'embedding'            => 'array',
        'embedding_updated_at' => 'datetime',
        'is_hidden'            => 'boolean',
    ];

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

    /** Письмо со ссылкой подтверждения почты (действует сутки), на языке, на котором участница открыла сайт. */
    public function sendEmailVerification(): void
    {
        $url = URL::temporarySignedRoute('account.verification.verify', now()->addDay(), [
            'id' => $this->getKey(),
            'hash' => sha1((string) $this->email),
        ]);

        Mail::to($this->email)->send(new VerifyParticipantEmailMail($this, $url, app()->getLocale()));
    }

    public function sendPasswordResetNotification($token): void
    {
        $url = route('account.password.reset', ['token' => $token, 'email' => $this->email]);

        Mail::to($this->email)->send(new ResetParticipantPasswordMail($this, $url, app()->getLocale()));
    }

    /** Видит ли она контакты (телефон, Telegram) участниц: с тарифа Community. */
    public function canSeeContacts(): bool
    {
        return $this->hasPlan(Plan::Community);
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

    /** Входит ли в действующий тариф всё, что даёт $min (Open, Community, Private). */
    public function hasPlan(Plan $min): bool
    {
        return $this->currentPlan()->includes($min);
    }

    /**
     * Участницы платформы: одобренные с любым действующим платным тарифом (Open, Community, Private). Каталог, подбор
     * контактов, ИИ-помощник и рассылки работают только по ним. Профили Open видны участницам Community и Private
     * (самим Open поиск закрыт), поэтому в каталог попадают все платные тарифы.
     */
    public function scopeMembers(Builder $query): Builder
    {
        return $query->approved()->withPlan(Plan::Open);
    }

    /**
     * Участницы, которых могут видеть и находить другие: члены сообщества (members) без отметки «скрыт». Каталог, поиск,
     * подбор контактов, ИИ-помощник и «новые участницы» берут только их. Скрытые (администратор, спонсор, партнёр) пользуются
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

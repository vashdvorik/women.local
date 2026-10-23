<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Plan;
use App\Support\BotMessages;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BotUser extends Model
{
    use HasFactory;
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'telegram_id',
        'telegram_username',
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

    // Тариф (plan, plan_ends_at, plan_notified_stage) намеренно не в $fillable: его меняет только
    // App\Services\Subscriptions\SubscriptionService, чтобы ни форма профиля, ни бот не могли выдать себе доступ.
    protected $casts = [
        'telegram_id'          => 'integer',
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
     * Тариф, который действует прямо сейчас: сохранённый, пока не истёк срок, иначе Open.
     * Проверка по дате делается здесь, а не фоновой задачей: доступ закрывается ровно в момент окончания.
     */
    public function currentPlan(): Plan
    {
        $plan = Plan::fromStored($this->plan);

        if (! $plan->isPaid()) {
            return Plan::Open;
        }

        return $this->plan_ends_at !== null && $this->plan_ends_at->isFuture() ? $plan : Plan::Open;
    }

    /** Входит ли в действующий тариф всё, что даёт $min (Community, Private). */
    public function hasPlan(Plan $min): bool
    {
        return $this->currentPlan()->includes($min);
    }

    /**
     * Участницы, чьи профили видны друг другу и кто пользуется сообществом: одобренные с действующим тарифом
     * Community или выше. Каталог, подбор контактов, ИИ-помощник и рассылки работают только по ним.
     */
    public function scopeMembers(Builder $query): Builder
    {
        return $query->approved()->withPlan(Plan::Community);
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

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
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
}

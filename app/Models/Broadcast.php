<?php

namespace App\Models;

use App\Enums\Plan;
use App\Support\Locales;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Рассылка сообщения участницам в Telegram из админки: текст на трёх языках, до трёх кнопок-ссылок, фото, получатели
 * (по тарифу) и время отправки. Отправляет задание SendBroadcast; по строке на получательницу — BroadcastDelivery.
 */
class Broadcast extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_SENDING = 'sending';
    public const STATUS_SENT = 'sent';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Группы получательниц: все; все с действующим пакетом (Community, Active, Private); по одному пакету;
     * «срок пакета закончился» (пакет был, но больше не действует). Не купившие пакет сюда не входят: без пакета кабинет
     * закрыт, Telegram они подключить не могут. Ключ 'open' у последней группы остался от прежнего бесплатного тарифа:
     * рассылки, сохранённые с ним, продолжают работать, но теперь он означает «срок закончился».
     * Рассылка идёт в Telegram, поэтому получают её только те, кто подключил бота. Одобрение анкеты на рассылки не влияет:
     * это скрытая проверка того, кому показывать профиль другим, а не право получать новости и возможности платформы.
     * Ключ 'approved' у первой группы остался от прежнего названия «все одобренные».
     */
    public const AUDIENCE_APPROVED = 'approved';
    public const AUDIENCE_MEMBERS = 'members';
    public const AUDIENCE_COMMUNITY = 'community';
    public const AUDIENCE_ACTIVE = 'active';
    public const AUDIENCE_PRIVATE = 'private';
    public const AUDIENCE_OPEN = 'open';

    public const AUDIENCES = [
        self::AUDIENCE_APPROVED,
        self::AUDIENCE_MEMBERS,
        self::AUDIENCE_COMMUNITY,
        self::AUDIENCE_ACTIVE,
        self::AUDIENCE_PRIVATE,
        self::AUDIENCE_OPEN,
    ];

    public const MAX_BUTTONS = 3;

    /** Время отправки админ вводит по Кишинёву (сайт хранит время в UTC). */
    public const TIMEZONE = 'Europe/Chisinau';

    protected $fillable = [
        'texts', 'buttons', 'audience', 'photo_path', 'photo_file_id', 'status',
        'scheduled_at', 'started_at', 'finished_at', 'total', 'sent', 'failed', 'blocked', 'created_by', 'source_type', 'source_id',
    ];

    protected function casts(): array
    {
        return [
            'texts' => 'array',
            'buttons' => 'array',
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(BroadcastDelivery::class);
    }

    /** Получательницы выбранной группы. */
    public static function audienceQuery(string $audience): Builder
    {
        $query = match ($audience) {
            self::AUDIENCE_MEMBERS => BotUser::withPlan(Plan::Community),
            // Ровно этот пакет и только действующий (у кого срок закончился, в группе «Срок пакета закончился»)
            self::AUDIENCE_COMMUNITY, self::AUDIENCE_ACTIVE, self::AUDIENCE_PRIVATE => BotUser::withPlan(Plan::Community)->where('bot_users.plan', $audience),
            self::AUDIENCE_OPEN => BotUser::query()
                ->whereNotIn('bot_users.id', BotUser::withPlan(Plan::Community)->select('bot_users.id'))
                ->whereHas('subscriptions'),
            default => BotUser::query(),
        };

        return $query->whereNotNull('bot_users.telegram_id');
    }

    public function recipients(): Builder
    {
        return self::audienceQuery($this->audience);
    }

    /** Текст на языке участницы; нет перевода — русский. */
    public function text(string $locale): string
    {
        $text = trim((string) ($this->texts[$locale] ?? ''));

        return $text !== '' ? $text : trim((string) ($this->texts[Locales::PRIMARY] ?? ''));
    }

    /**
     * Кнопки на языке участницы: подпись на её языке или русская, адрес один.
     *
     * @return list<array{text: string, url: string}>
     */
    public function buttonsFor(string $locale): array
    {
        $buttons = [];

        foreach ((array) $this->buttons as $button) {
            $url = trim((string) ($button['url'] ?? ''));
            $label = trim((string) ($button['label'][$locale] ?? '')) ?: trim((string) ($button['label'][Locales::PRIMARY] ?? ''));

            if ($url !== '' && $label !== '') {
                $buttons[] = ['text' => $label, 'url' => $url];
            }
        }

        return $buttons;
    }

    /** Языки, на которых текст заполнен (русский всегда первым). */
    public function filledLocales(): array
    {
        return array_values(array_filter(Locales::ALL, fn (string $locale) => trim((string) ($this->texts[$locale] ?? '')) !== ''));
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SCHEDULED], true);
    }

    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_SCHEDULED, self::STATUS_SENDING], true);
    }

    public function hasPhoto(): bool
    {
        return filled($this->photo_path);
    }

    public function pending(): int
    {
        return max(0, $this->total - $this->sent - $this->failed - $this->blocked);
    }

    /** Время отправки по Кишинёву, для поля формы и подписей. */
    public function scheduledLocal(): ?CarbonInterface
    {
        return $this->scheduled_at?->copy()->setTimezone(self::TIMEZONE);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => __('Черновик'),
            self::STATUS_SCHEDULED => __('Запланирована'),
            self::STATUS_SENDING => __('Отправляется'),
            self::STATUS_SENT => __('Отправлена'),
            self::STATUS_CANCELLED => __('Отменена'),
            default => $this->status,
        };
    }

    public function statusBadge(): string
    {
        return match ($this->status) {
            self::STATUS_SENT => 'badge--published',
            self::STATUS_SENDING, self::STATUS_SCHEDULED => 'badge--pending',
            self::STATUS_CANCELLED => 'badge--rejected',
            default => 'badge--draft',
        };
    }

    public function audienceLabel(): string
    {
        return self::audienceTitle($this->audience);
    }

    public static function audienceTitle(string $audience): string
    {
        return match ($audience) {
            self::AUDIENCE_MEMBERS => __('Все с действующим пакетом (Community, Active, Private)'),
            self::AUDIENCE_COMMUNITY => __('Только пакет Community'),
            self::AUDIENCE_ACTIVE => __('Только пакет Active'),
            self::AUDIENCE_PRIVATE => __('Только пакет Private'),
            self::AUDIENCE_OPEN => __('Срок пакета закончился (были участницами с пакетом)'),
            default => __('Все участницы (подключившие Telegram)'),
        };
    }
}

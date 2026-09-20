<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Пост участницы в личном кабинете (проект, встреча, событие). Не путать с
 * SiteOpportunity — «Возможности», которые редактор ведёт в админке.
 *
 * Посты проходят премодерацию: видимы всем только одобренные; автор дополнительно
 * видит свои ожидающие и отклонённые.
 */
class Opportunity extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'bot_user_id',
        'type',
        'title',
        'body',
        'event_date',
        'location',
        'contact_url',
        'status',
        'moderated_at',
    ];

    protected $casts = [
        'event_date' => 'date',
        'moderated_at' => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(BotUser::class, 'bot_user_id');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /** Одобренные всем плюс любые собственные посты автора. */
    public function scopeVisibleTo(Builder $query, BotUser $viewer): Builder
    {
        return $query->where(fn (Builder $q) => $q
            ->where('status', self::STATUS_APPROVED)
            ->orWhere('bot_user_id', $viewer->id));
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'project' => __('account.types.project'),
            'meeting' => __('account.types.meeting'),
            'event' => __('account.types.event'),
            default => $this->type,
        };
    }

    public function typeEmoji(): string
    {
        return match ($this->type) {
            'project' => '💼',
            'meeting' => '🤝',
            'event' => '📅',
            default => '📌',
        };
    }
}

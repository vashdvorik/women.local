<?php

declare(strict_types=1);

namespace App\Models;

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

    protected $casts = [
        'telegram_id'          => 'integer',
        'approved_at'          => 'datetime',
        'embedding'            => 'array',
        'embedding_updated_at' => 'datetime',
    ];

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

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function loginTokens(): HasMany
    {
        return $this->hasMany(LoginToken::class, 'telegram_id', 'telegram_id');
    }
}

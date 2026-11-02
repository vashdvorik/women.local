<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Реплика в переписке участницы с AI-менеджером кабинета (App\Services\AiManagerService). У ответа ассистента лежит ещё
 * вопрос и отметка answered: false значит «ответа не нашлось», такие вопросы админ видит в «Вопросы AI-менеджеру».
 */
class ManagerMessage extends Model
{
    public const ROLE_USER = 'user';
    public const ROLE_ASSISTANT = 'assistant';

    protected $fillable = ['bot_user_id', 'role', 'content', 'question', 'plan', 'answered', 'handled_at'];

    protected function casts(): array
    {
        return [
            'answered' => 'boolean',
            'handled_at' => 'datetime',
        ];
    }

    public function botUser(): BelongsTo
    {
        return $this->belongsTo(BotUser::class);
    }

    /** Ответы ассистента, на которые ответа не нашлось и которые админ ещё не разобрал. */
    public function scopeUnanswered(Builder $query): Builder
    {
        return $query->where('role', self::ROLE_ASSISTANT)->where('answered', false)->whereNull('handled_at');
    }
}

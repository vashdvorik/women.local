<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Обращение из формы «Связаться с нами» на странице «Контакты» (раздел «Обращения» в админке). */
class ContactMessage extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'organization', 'message', 'locale', 'read_at', 'mail_sent_at', 'mail_error'];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'mail_sent_at' => 'datetime',
        ];
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }
}

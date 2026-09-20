<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Подписчик рассылки Академии. Плоская модель: имя и почта, почта уникальна
 * (повторная подписка обновляет имя, а не плодит строки).
 */
class Subscriber extends Model
{
    protected $fillable = [
        'name',
        'email',
    ];
}

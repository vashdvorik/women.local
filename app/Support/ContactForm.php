<?php

namespace App\Support;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

/**
 * Защита формы «Связаться с нами» от ботов без капчи: скрытое поле-приманка (заполнено — бот) и метка времени, которую
 * страница кладёт в форму. Человек не успевает заполнить четыре поля быстрее нескольких секунд, а бот шлёт форму сразу;
 * метка зашифрована, подделать «давнее» время нельзя. Ограничение «не больше трёх обращений в час с одного адреса» — в
 * App\Http\Controllers\PublicSite\ContactController.
 */
final class ContactForm
{
    /** Быстрее этого человек форму не заполнит. */
    public const MIN_SECONDS = 4;

    /** Страница, открытая дольше, считается устаревшей: пусть обновит. */
    public const MAX_SECONDS = 7 * 86400;

    public static function token(): string
    {
        return Crypt::encryptString((string) time());
    }

    /** Метка настоящая и возраст формы в допустимых пределах. */
    public static function isValid(?string $token): bool
    {
        if (! is_string($token) || $token === '') {
            return false;
        }

        try {
            $age = time() - (int) Crypt::decryptString($token);
        } catch (DecryptException) {
            return false;
        }

        return $age >= self::MIN_SECONDS && $age <= self::MAX_SECONDS;
    }
}

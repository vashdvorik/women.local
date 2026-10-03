<?php

namespace App\Enums;

use App\Models\SiteSetting;

/**
 * Тариф кабинета участницы. Тарифы вложены друг в друга: Private включает всё из Community, а Community — всё из Open.
 * Срок берётся из config/subscription.php, цена (в целых рублях) — из админки, а если её там не задавали, оттуда же.
 */
enum Plan: string
{
    case Open = 'open';
    case Community = 'community';
    case Private = 'private';

    /** Чем выше число, тем больше возможностей. */
    public function rank(): int
    {
        return match ($this) {
            self::Open => 0,
            self::Community => 1,
            self::Private => 2,
        };
    }

    public function isPaid(): bool
    {
        return $this !== self::Open;
    }

    /** Есть ли в этом тарифе всё, что даёт $other. */
    public function includes(self $other): bool
    {
        return $this->rank() >= $other->rank();
    }

    /**
     * Цена за срок подписки в рублях; у Open — 0. Её меняют в админке («Подписки → Цена подписки»);
     * пока там ничего не задано, действует значение из config/subscription.php.
     */
    public function price(): int
    {
        if (! $this->isPaid()) {
            return 0;
        }

        return SiteSetting::subscriptionPrices()[$this->value] ?? (int) config("subscription.plans.{$this->value}.price", 0);
    }

    /** Цена в копейках: так её ждёт банк (RequestSum). */
    public function priceKopecks(): int
    {
        return $this->price() * 100;
    }

    /** На сколько месяцев оплачивается подписка. */
    public function months(): int
    {
        return $this->isPaid() ? (int) config("subscription.plans.{$this->value}.months", 12) : 0;
    }

    /** Название тарифа: WOMEN’S HUB OPEN / COMMUNITY / PRIVATE. */
    public function title(?string $locale = null): string
    {
        return __("subscription.plans.{$this->value}.title", [], $locale);
    }

    /** Короткое название для списков и сообщений: Open / Community / Private. */
    public function shortTitle(): string
    {
        return ucfirst($this->value);
    }

    /** Цена словами для интерфейса: «600 руб.» или «Бесплатно». */
    public function priceLabel(?string $locale = null): string
    {
        if (! $this->isPaid()) {
            return __('subscription.free', [], $locale);
        }

        // Неразрывные пробелы: «600 руб.» не рвётся на две строки.
        return number_format($this->price(), 0, ',', "\u{00A0}")."\u{00A0}".str_replace(' ', "\u{00A0}", __('subscription.currency', [], $locale));
    }

    /**
     * Тарифы, в которых есть всё, что даёт $min (включая сам $min).
     *
     * @return list<self>
     */
    public static function atLeast(self $min): array
    {
        return array_values(array_filter(self::cases(), fn (self $plan): bool => $plan->includes($min)));
    }

    /** @return list<self> */
    public static function paid(): array
    {
        return array_values(array_filter(self::cases(), fn (self $plan): bool => $plan->isPaid()));
    }

    public static function fromStored(?string $value): self
    {
        return self::tryFrom((string) $value) ?? self::Open;
    }
}

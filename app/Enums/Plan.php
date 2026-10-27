<?php

namespace App\Enums;

use App\Models\SiteSetting;

/**
 * Тариф кабинета участницы. Тарифы вложены друг в друга: Private включает всё из Community, а Community — всё из Open.
 * None — «тарифа нет» (зарегистрировалась, но ещё не купила): кабинета нет, только анкета и выбор тарифа.
 *
 *  - Open (600): кабинет с возможностями, полезной информацией и обучающими материалами; без поиска и подбора людей;
 *  - Community (5000): поиск, подбор, ИИ-помощник, каталог участниц с контактами, публикация возможностей;
 *  - Private (19 000): всё из Community и премиальные возможности.
 *
 * Срок берётся из config/subscription.php, цена (в целых рублях) — из админки, а если её там не задавали, оттуда же.
 */
enum Plan: string
{
    case None = 'none';
    case Open = 'open';
    case Community = 'community';
    case Private = 'private';

    /** Чем выше число, тем больше возможностей. */
    public function rank(): int
    {
        return match ($this) {
            self::None => 0,
            self::Open => 1,
            self::Community => 2,
            self::Private => 3,
        };
    }

    public function isPaid(): bool
    {
        return $this !== self::None;
    }

    /** Есть ли в этом тарифе всё, что даёт $other. */
    public function includes(self $other): bool
    {
        return $this->rank() >= $other->rank();
    }

    /**
     * Цена за срок подписки в рублях; у None — 0. Её меняют в админке («Подписки → Цена подписки»);
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

    /** Цена словами для интерфейса: «600 руб.» (у None пусто). */
    public function priceLabel(?string $locale = null): string
    {
        if (! $this->isPaid()) {
            return '';
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

    /** Платные тарифы по возрастанию: Open, Community, Private. @return list<self> */
    public static function paid(): array
    {
        return array_values(array_filter(self::cases(), fn (self $plan): bool => $plan->isPaid()));
    }

    public static function fromStored(?string $value): self
    {
        return self::tryFrom((string) $value) ?? self::None;
    }
}

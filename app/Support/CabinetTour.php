<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\Plan;
use App\Models\BotUser;

/**
 * Подсказки в кабинете: начинаются с поздравления с пакетом («Вы успешно перешли на пакет …»), дальше короткий тур по
 * карточкам («Далее» или «Закрыть»). Тур свой у каждого тарифа и показывается один раз на тариф (BotUser::hasSeen('tour:active')):
 *  - первый тариф: поздравление, меню и все разделы тарифа;
 *  - переход на тариф выше: поздравление и только те разделы, которых на прежнем тарифе не было.
 * Кнопка «Показать подсказки» на главной открывает полный тур без поздравления. Тексты разделов берутся из пояснений
 * плиток главной (account.home.tiles.*), поэтому в двух местах они не расходятся.
 */
final class CabinetTour
{
    /** Какие разделы объясняем и в каком порядке (остальные понятны из названия или ещё пусты). */
    private const SECTIONS = ['assistant', 'matches', 'search', 'people', 'possibilities', 'knowledge', 'manager', 'opportunities', 'profile', 'subscription'];

    /** Какие из этих разделов есть в меню на тарифе (зеркало CabinetNav::items). */
    private const BY_PLAN = [
        'community' => ['possibilities', 'knowledge', 'manager', 'opportunities', 'profile', 'subscription'],
        'active' => ['assistant', 'matches', 'search', 'people', 'possibilities', 'knowledge', 'manager', 'opportunities', 'profile'],
        'private' => ['assistant', 'matches', 'search', 'people', 'possibilities', 'knowledge', 'manager', 'opportunities', 'profile'],
    ];

    /** Ключ в bot_users.cabinet_seen: подсказки этого тарифа показаны. */
    public static function seenKey(Plan $plan): string
    {
        return 'tour:'.$plan->value;
    }

    /** Нужно ли показать подсказки сами: у участницы есть тариф и тур этого тарифа ещё не пройден. */
    public static function shouldAutoStart(BotUser $user): bool
    {
        $plan = $user->currentPlan();

        return $plan->isPaid() && ! $user->hasSeen(self::seenKey($plan));
    }

    /**
     * @param  bool  $replay  true — полный тур без поздравления (кнопка «Показать подсказки»)
     * @return list<array{target: ?string, title: string, text: string, how: string}>
     */
    public static function steps(BotUser $user, bool $replay = false): array
    {
        $plan = $user->currentPlan();
        $items = collect(CabinetNav::items($user, includeLocked: false))->keyBy('key');
        $previous = $replay ? null : self::previousPlan($user, $plan);

        $sections = array_values(array_filter(
            self::SECTIONS,
            fn (string $key): bool => $items->has($key) && in_array($key, self::BY_PLAN[$plan->value] ?? [], true),
        ));

        // Переход на тариф выше: рассказываем только о том, чего на прежнем тарифе не было
        if ($previous !== null) {
            $sections = array_values(array_diff($sections, self::BY_PLAN[$previous->value] ?? []));
        }

        $steps = [];

        if (! $replay) {
            if ($previous === null) {
                $text = __('account.tour.congrats_first', ['plan' => $plan->title()]);
            } elseif ($sections === []) {
                // Новых разделов нет (например, Active → Private): поздравляем и отправляем читать про привилегии
                $text = __('account.tour.congrats_plain', ['plan' => $plan->title()]).' '.__('account.tour.congrats_details');
            } else {
                $text = __('account.tour.congrats_upgrade', ['plan' => $plan->title()]);
            }

            $steps[] = ['target' => null, 'title' => __('account.tour.congrats_title'), 'text' => $text, 'how' => ''];
        }

        if ($previous === null) {
            $steps[] = ['target' => '[data-tour-nav]', 'title' => __('account.tour.menu_title'), 'text' => __('account.tour.menu_text'), 'how' => ''];
            $steps[] = ['target' => '[data-tour-home="plan"]', 'title' => __('account.tour.home_plan_title'), 'text' => __('account.tour.home_plan_text'), 'how' => ''];
            $steps[] = ['target' => '[data-tour-home="feed"]', 'title' => __('account.tour.home_feed_title'), 'text' => __('account.tour.home_feed_text'), 'how' => ''];
        }

        foreach ($sections as $key) {
            $steps[] = [
                'target' => '[data-tour="'.$key.'"]',
                'title' => $items[$key]['label'],
                'text' => __("account.home.tiles.{$key}.what"),
                'how' => __("account.home.tiles.{$key}.how"),
            ];
        }

        return $steps;
    }

    /** Самый высокий из прежних тарифов, на котором подсказки уже показывались (ниже текущего), либо null. */
    private static function previousPlan(BotUser $user, Plan $current): ?Plan
    {
        $seen = array_values(array_filter(
            Plan::paid(),
            fn (Plan $plan): bool => $plan->rank() < $current->rank() && $user->hasSeen(self::seenKey($plan)),
        ));

        return $seen === [] ? null : end($seen);
    }
}

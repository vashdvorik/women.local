<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\Plan;
use App\Http\Controllers\Account\SiteOpportunityController;
use App\Models\BotUser;
use App\Models\Event;
use App\Models\LearningVideo;
use App\Models\Opportunity;
use App\Services\MatchingService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Данные главной страницы кабинета (account.index). Сверху имя, пакет со сроком и заполненность профиля; затем короткие
 * плитки разделов (навигация), рекомендации, «Что нового» и ближайшие события горизонтальными лентами. Community видит
 * меньше (рекомендации и участницы открыты с Active) и приглашение улучшить пакет. Подсказки при первом входе: CabinetTour.
 */
final class CabinetHome
{
    /** За сколько дней до конца пакета предлагаем продлить. */
    private const RENEW_DAYS = 30;

    /** Поля анкеты, по которым считается заполненность: ключ → (подпись подсказки в lang account.home.profile_missing.*). */
    private const PROFILE_FIELDS = ['avatar_path', 'description', 'expectation', 'phone', 'telegram'];

    /**
     * @return array<string, mixed>
     */
    public static function build(BotUser $user, string $locale): array
    {
        $plan = $user->currentPlan();
        $anyPlan = $plan->isPaid();
        $premium = $user->hasPlan(Plan::Active);

        return [
            'name' => BotMessages::firstName($user->full_name),
            'paid' => $premium,
            'plan' => self::plan($user, $plan),
            'profile' => self::profile($user),
            // Плитки — те же разделы, что в меню, кроме самой главной: короткие, для быстрого перехода.
            'tiles' => array_values(array_filter(CabinetNav::items($user), fn (array $item): bool => $item['key'] !== 'home')),
            'recs' => $premium ? self::recommendations($user) : null,
            'news' => $anyPlan ? self::news() : [],
            'events' => $anyPlan ? self::events($user, $locale) : [],
            // Что откроет Active: названия закрытых разделов (для Community)
            'locked' => $premium ? [] : array_values(array_map(fn (array $i): string => $i['label'], array_filter(CabinetNav::items($user), fn (array $i): bool => $i['locked']))),
        ];
    }

    /** @return array{key: string, title: string, ends_at: ?string, days_left: ?int, renew: bool, active: bool} */
    private static function plan(BotUser $user, Plan $plan): array
    {
        $ends = $plan->isPaid() ? $user->plan_ends_at : null;
        $days = $ends !== null ? max(0, (int) now()->startOfDay()->diffInDays($ends->copy()->startOfDay(), false)) : null;

        return [
            'key' => $plan->value,
            'title' => $plan->isPaid() ? __('subscription.plans.'.$plan->value.'.title') : '',
            'ends_at' => $ends?->locale(app()->getLocale())->isoFormat('D MMMM YYYY'),
            'days_left' => $days,
            'renew' => $days !== null && $days <= self::RENEW_DAYS,
            'active' => $plan->isPaid(),
        ];
    }

    /**
     * Заполненность анкеты в процентах и первое, чего не хватает.
     *
     * @return array{percent: int, next: ?string}
     */
    private static function profile(BotUser $user): array
    {
        $filled = [
            'avatar_path' => filled($user->avatar_path),
            'description' => filled($user->description),
            'expectation' => filled($user->expectation),
            'phone' => filled($user->phone),
            'telegram' => filled($user->telegram_id) || filled($user->telegram_username),
        ];

        $next = null;
        foreach (self::PROFILE_FIELDS as $field) {
            if (! $filled[$field]) {
                $next = $field;
                break;
            }
        }

        return ['percent' => (int) round(count(array_filter($filled)) / count($filled) * 100), 'next' => $next];
    }

    /**
     * Подбор AI для участницы: до пяти человек. Сбой подбора (нет ключа AI и т. п.) главную не ломает.
     *
     * @return array{people: list<array<string, mixed>>}
     */
    private static function recommendations(BotUser $user): array
    {
        try {
            $matches = app(MatchingService::class)->topMatches($user, 5);
        } catch (Throwable) {
            $matches = collect();
        }

        return [
            'people' => $matches->map(fn (array $match): array => [
                'name' => $match['user']->full_name ?: __('account.not_specified'),
                'text' => Str::limit((string) ($match['user']->description ?: $match['user']->expectation), 90),
                'avatar' => $match['user']->avatar_path ? Storage::url($match['user']->avatar_path) : null,
                'initial' => mb_strtoupper(mb_substr((string) ($match['user']->full_name ?: '?'), 0, 1)),
                'score' => max(0, min(100, (int) round((float) $match['score'] * 100))),
                'href' => route('account.people.show', $match['user']),
            ])->values()->all(),
        ];
    }

    /**
     * «Что нового»: свежие возможности и материалы обучения (карточки, новости кабинета и видео), новые сверху.
     *
     * @return list<array<string, mixed>>
     */
    private static function news(): array
    {
        // Одинаковы для всех участниц: берутся из общего кеша (сбрасывается правками в админке)
        return collect(self::shared(app()->getLocale())['news'])->sortByDesc('at')->take(10)->values()->all();
    }

    /**
     * Ближайшие события: новости платформы с датой впереди и инициативы участниц с датой события, по возрастанию даты.
     *
     * @return list<array<string, mixed>>
     */
    private static function events(BotUser $user, string $locale): array
    {
        $items = collect(self::shared($locale)['events']);

        $initiatives = Opportunity::query()->visibleTo($user)->approved()->whereDate('event_date', '>=', today())->orderBy('event_date')->limit(8)->get();

        foreach ($initiatives as $initiative) {
            $items->push([
                'title' => $initiative->title,
                'date' => $initiative->event_date->copy()->locale($locale)->isoFormat('D MMMM'),
                'place' => (string) $initiative->location,
                'image' => null,
                'href' => route('account.opportunities.index'),
                'external' => false,
                'at' => $initiative->event_date->getTimestamp(),
            ]);
        }

        return $items->sortBy('at')->take(8)->values()->all();
    }

    /**
     * Часть главной, общая для всех участниц: свежие возможности, обучение, видео и ближайшие новости платформы.
     * Лежит в кеше публичных страниц (PublicCache): правка этих материалов в админке его сбрасывает.
     *
     * @return array{news: list<array<string, mixed>>, events: list<array<string, mixed>>}
     */
    private static function shared(string $locale): array
    {
        return PublicCache::remember('cabinet.home.'.$locale, function () use ($locale): array {
            $kind = fn (string $key): string => __('account.home.kind.'.$key);
            $news = collect();

            foreach (SiteOpportunityController::latest('cabinet', 4) as $card) {
                $news->push($card + ['kind' => $kind('possibility')]);
            }
            foreach (SiteOpportunityController::latest('learning', 3) as $card) {
                $news->push($card + ['kind' => $kind('learning')]);
            }
            foreach (LearningVideo::query()->published()->with('translations')->latest()->limit(2)->get() as $video) {
                $news->push([
                    'kind' => $kind('video'),
                    'title' => $video->field('title') ?: $video->youtube_id,
                    'excerpt' => Str::limit((string) $video->field('description'), 90),
                    'image' => $video->coverUrl(),
                    'href' => route('account.knowledge'),
                    'date' => '',
                    'at' => $video->created_at?->getTimestamp() ?? 0,
                ]);
            }

            $events = collect();

            foreach (Event::query()->published()->whereDate('starts_at', '>=', today())->with('translations')->orderBy('starts_at')->limit(8)->get() as $event) {
                $link = $event->cardLink();

                $events->push([
                    'title' => (string) $event->field('title'),
                    'date' => $event->dateLabel($locale),
                    'place' => '',
                    'image' => $event->imageUrl(),
                    'href' => $link['href'] ?? null,
                    'external' => (bool) ($link['external'] ?? false),
                    'at' => $event->starts_at->getTimestamp(),
                ]);
            }

            return ['news' => $news->all(), 'events' => $events->all()];
        });
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Subscriptions;

use App\Enums\Plan;
use App\Mail\PlanPurchaseOpenedMail;
use App\Mail\PlanRequestMail;
use App\Models\BotUser;
use App\Models\PlanRequest;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Закрытая покупка тарифа. Администратор закрывает тариф переключателем («Подписки»): вместо «Купить» участница видит
 * «Оставить заявку». Менеджер звонит и открывает покупку этой участнице лично (заявка получает статус unlocked), после
 * чего у неё появляется обычная кнопка «Купить». Оплата закрывает заявку (purchased).
 */
final class PlanPurchase
{
    public function isClosed(Plan $plan): bool
    {
        return in_array($plan->value, SiteSetting::closedPlans(), true);
    }

    /** Может ли участница купить тариф прямо сейчас: он открыт для всех или менеджер открыл его лично ей. */
    public function canBuy(BotUser $user, Plan $plan): bool
    {
        if (! $plan->isPaid()) {
            return false;
        }

        return ! $this->isClosed($plan) || $this->unlockedRequest($user, $plan) !== null;
    }

    /** Заявка, по которой менеджер открыл покупку этого тарифа участнице. */
    public function unlockedRequest(BotUser $user, Plan $plan): ?PlanRequest
    {
        return $user->planRequests()->where('plan', $plan->value)->where('status', PlanRequest::STATUS_UNLOCKED)->latest('id')->first();
    }

    /** Заявка участницы по тарифу, которая ещё в работе (ждёт звонка или звонок был). */
    public function pendingRequest(BotUser $user, Plan $plan): ?PlanRequest
    {
        return $user->planRequests()->where('plan', $plan->value)
            ->whereIn('status', [PlanRequest::STATUS_NEW, PlanRequest::STATUS_CONTACTED])
            ->latest('id')->first();
    }

    /**
     * Участница оставляет заявку на закрытый тариф. Вторая заявка по тому же тарифу, пока первая в работе, не создаётся.
     * Менеджеру уходит письмо; сбой почты заявку не отменяет: она всё равно видна в админке.
     */
    public function request(BotUser $user, Plan $plan, ?string $comment = null): PlanRequest
    {
        $existing = $this->pendingRequest($user, $plan);

        if ($existing !== null) {
            return $existing;
        }

        $request = $user->planRequests()->create([
            'plan' => $plan->value,
            'status' => PlanRequest::STATUS_NEW,
            'comment' => filled($comment) ? trim((string) $comment) : null,
        ]);

        $manager = SiteSetting::planRequestEmail();

        if ($manager !== '') {
            try {
                Mail::to($manager)->send(new PlanRequestMail($request->load('botUser')));
            } catch (\Throwable $e) {
                Log::warning('Не удалось отправить менеджеру письмо о заявке на тариф', ['request' => $request->id, 'error' => $e->getMessage()]);
            }
        }

        return $request;
    }

    /** Менеджер открыл покупку: у участницы появляется кнопка «Купить». Письмо участнице уходит по желанию менеджера. */
    public function unlock(PlanRequest $request, bool $notify = false): void
    {
        $request->forceFill([
            'status' => PlanRequest::STATUS_UNLOCKED,
            'unlocked_at' => now(),
            'contacted_at' => $request->contacted_at ?? now(),
        ])->save();

        if ($notify && $request->botUser?->email) {
            try {
                Mail::to($request->botUser->email)->send(new PlanPurchaseOpenedMail($request->botUser, $request->planEnum()));
            } catch (\Throwable $e) {
                Log::warning('Не удалось отправить участнице письмо об открытой покупке', ['request' => $request->id, 'error' => $e->getMessage()]);
            }
        }
    }

    /** Менеджер открыл покупку без заявки (участница позвонила сама): создаём заявку сразу в статусе unlocked. */
    public function unlockFor(BotUser $user, Plan $plan, bool $notify = false): PlanRequest
    {
        $request = $this->pendingRequest($user, $plan) ?? $user->planRequests()->create([
            'plan' => $plan->value,
            'status' => PlanRequest::STATUS_NEW,
        ]);

        $this->unlock($request, $notify);

        return $request->refresh();
    }

    /** Тариф куплен или выдан: заявки на него и на ниже стоящие больше не нужны. */
    public function markPurchased(BotUser $user, Plan $plan): void
    {
        $user->planRequests()
            ->whereIn('status', PlanRequest::OPEN_STATUSES)
            ->whereIn('plan', array_map(fn (Plan $p): string => $p->value, array_filter(Plan::paid(), fn (Plan $p): bool => $plan->includes($p))))
            ->update(['status' => PlanRequest::STATUS_PURCHASED, 'purchased_at' => now()]);
    }
}

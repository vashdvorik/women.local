<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlanRequest;
use App\Services\Subscriptions\PlanPurchase;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * «Заявки на тариф» — лагерь «Кабинеты участниц»: заявки на закрытые тарифы («Оставить заявку» вместо «Купить»).
 * Менеджер звонит по телефону из анкеты и открывает участнице покупку лично; после этого у неё появляется кнопка «Купить».
 */
class PlanRequestController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $status = in_array($status, [...PlanRequest::statuses(), 'all'], true) ? $status : '';

        $requests = PlanRequest::query()
            ->with('botUser')
            ->when($status === '', fn ($q) => $q->open())
            ->when(in_array($status, PlanRequest::statuses(), true), fn ($q) => $q->where('status', $status))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $counts = PlanRequest::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.plan-requests.index', [
            'requests' => $requests,
            'status' => $status,
            'counts' => [
                '' => $counts->only(PlanRequest::OPEN_STATUSES)->sum(),
                PlanRequest::STATUS_NEW => (int) ($counts[PlanRequest::STATUS_NEW] ?? 0),
                PlanRequest::STATUS_UNLOCKED => (int) ($counts[PlanRequest::STATUS_UNLOCKED] ?? 0),
                PlanRequest::STATUS_PURCHASED => (int) ($counts[PlanRequest::STATUS_PURCHASED] ?? 0),
                PlanRequest::STATUS_DECLINED => (int) ($counts[PlanRequest::STATUS_DECLINED] ?? 0),
                'all' => (int) $counts->sum(),
            ],
        ]);
    }

    /** Менеджер позвонил: заявка уходит из «новых», но покупка ещё закрыта. */
    public function contacted(PlanRequest $planRequest): RedirectResponse
    {
        if ($planRequest->status === PlanRequest::STATUS_NEW) {
            $planRequest->forceFill(['status' => PlanRequest::STATUS_CONTACTED, 'contacted_at' => now()])->save();
        }

        return back()->with('success', __('Отмечено: позвонили.'));
    }

    /** Открывает участнице кнопку «Купить» на этот тариф. По желанию менеджера ей уходит письмо. */
    public function unlock(Request $request, PlanRequest $planRequest, PlanPurchase $purchase): RedirectResponse
    {
        $purchase->unlock($planRequest, $request->boolean('notify'));

        return back()->with('success', __('Покупка открыта: у участницы появилась кнопка «Купить».'));
    }

    /** Закрывает заявку без покупки; открытая покупка по ней снова закрывается. */
    public function decline(PlanRequest $planRequest): RedirectResponse
    {
        $planRequest->forceFill(['status' => PlanRequest::STATUS_DECLINED])->save();

        return back()->with('success', __('Заявка закрыта.'));
    }

    public function note(Request $request, PlanRequest $planRequest): RedirectResponse
    {
        $data = $request->validate(['manager_note' => ['nullable', 'string', 'max:1000']], [], ['manager_note' => __('заметка')]);

        $planRequest->forceFill(['manager_note' => filled($data['manager_note'] ?? null) ? trim($data['manager_note']) : null])->save();

        return back()->with('success', __('Заметка сохранена.'));
    }
}

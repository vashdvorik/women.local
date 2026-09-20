<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BotUser;
use App\Models\Opportunity;
use Illuminate\Contracts\View\View;

/** Инфопанель лагеря «Кабинеты участниц»: очередь модерации и быстрые переходы. */
class CabinetDashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.cabinets.dashboard', [
            'pendingProfiles' => BotUser::pending()->count(),
            'approvedProfiles' => BotUser::approved()->count(),
            'pendingPosts' => Opportunity::pending()->count(),
        ]);
    }
}

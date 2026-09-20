<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PublishStatus;
use App\Http\Controllers\Controller;
use App\Models\BotUser;
use App\Models\Opportunity;
use App\Models\Post;
use App\Models\SiteOpportunity;
use App\Models\Subscriber;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /**
     * Инфопанель лагеря «Внешний сайт»: быстрые действия + короткая инструкция «как пользоваться»
     * (AGENTS.md §10). Если в кабинетах участниц что-то ждёт решения, об этом напоминает плашка:
     * очередь модерации живёт в другом лагере и не должна теряться.
     */
    public function index(): View
    {
        $draftCount = Post::where('status', PublishStatus::Draft)->count()
            + SiteOpportunity::where('status', PublishStatus::Draft)->count();

        $subscriberCount = Subscriber::count();

        $pendingProfiles = BotUser::pending()->count();
        $pendingPosts = Opportunity::pending()->count();

        return view('admin.dashboard', compact('draftCount', 'subscriberCount', 'pendingProfiles', 'pendingPosts'));
    }
}

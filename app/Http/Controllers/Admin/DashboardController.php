<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PublishStatus;
use App\Http\Controllers\Controller;
use App\Models\BotUser;
use App\Models\ContactMessage;
use App\Models\ManagerMessage;
use App\Models\Opportunity;
use App\Models\Payment;
use App\Models\PlanRequest;
use App\Models\Post;
use App\Models\SiteOpportunity;
use App\Models\Subscriber;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /**
     * Единая инфопанель админки: что требует внимания (очереди участниц, заявок, платежей, вопросов и обращений),
     * быстрые действия и короткая инструкция «как пользоваться» (AGENTS.md §10).
     */
    public function index(): View
    {
        $draftCount = Post::where('status', PublishStatus::Draft)->count()
            + SiteOpportunity::where('status', PublishStatus::Draft)->count();

        return view('admin.dashboard', [
            'draftCount' => $draftCount,
            'subscriberCount' => Subscriber::count(),
            'approvedProfiles' => BotUser::approved()->count(),
            // Очереди: у каждой своя карточка со ссылкой на нужный список
            'queues' => [
                ['label' => __('Инициативы участниц ждут решения'), 'count' => Opportunity::pending()->count(), 'url' => route('admin.member-posts.index', ['status' => 'pending']), 'open' => __('Открыть очередь инициатив')],
                ['label' => __('Новые заявки на пакет'), 'count' => PlanRequest::where('status', PlanRequest::STATUS_NEW)->count(), 'url' => route('admin.plan-requests.index'), 'open' => __('Позвонить участницам')],
                ['label' => __('Платежи на разбор'), 'count' => Payment::where('status', Payment::STATUS_VERIFYING)->where('updated_at', '<=', now()->subMinutes(15))->count(), 'url' => route('admin.payments.index'), 'open' => __('Разобрать платежи')],
                ['label' => __('Вопросы AI-менеджеру без ответа'), 'count' => ManagerMessage::unanswered()->count(), 'url' => route('admin.manager-questions.index'), 'open' => __('Посмотреть вопросы')],
                ['label' => __('Непрочитанные обращения'), 'count' => ContactMessage::unread()->count(), 'url' => route('admin.contact-messages.index'), 'open' => __('Открыть обращения')],
            ],
        ]);
    }
}

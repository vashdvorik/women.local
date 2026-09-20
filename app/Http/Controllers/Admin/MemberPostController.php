<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\NotifyOpportunity;
use App\Models\Opportunity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Премодерация постов участниц из кабинета (проект, встреча, событие).
 * Одобренный пост виден всем в кабинете и уходит участницам уведомлением в Telegram.
 */
class MemberPostController extends Controller
{
    private const STATUSES = [
        Opportunity::STATUS_PENDING,
        Opportunity::STATUS_APPROVED,
        Opportunity::STATUS_REJECTED,
    ];

    private const TYPES = ['project', 'meeting', 'event'];

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $status = in_array($status, self::STATUSES, true) ? $status : '';
        $type = $request->string('type')->toString();
        $type = in_array($type, self::TYPES, true) ? $type : '';
        $search = trim($request->string('q')->toString());

        $posts = Opportunity::query()
            ->with('author')
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($type !== '', fn ($q) => $q->where('type', $type))
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('title', 'like', "%{$search}%")
                ->orWhereHas('author', fn ($a) => $a->where('full_name', 'like', "%{$search}%"))))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = Opportunity::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.member-posts.index', compact('posts', 'status', 'type', 'search', 'counts'));
    }

    public function show(Opportunity $post): View
    {
        return view('admin.member-posts.show', ['post' => $post->load('author')]);
    }

    public function approve(Opportunity $post): RedirectResponse
    {
        if (! $post->isApproved()) {
            $post->update(['status' => Opportunity::STATUS_APPROVED, 'moderated_at' => now()]);

            NotifyOpportunity::dispatch($post);
        }

        return back()->with('success', 'Публикация одобрена. Участницы получат уведомление в Telegram.');
    }

    public function reject(Opportunity $post): RedirectResponse
    {
        $post->update(['status' => Opportunity::STATUS_REJECTED, 'moderated_at' => now()]);

        return back()->with('success', 'Публикация отклонена и скрыта от других участниц.');
    }

    public function destroy(Opportunity $post): RedirectResponse
    {
        $post->delete();

        return redirect()->route('admin.member-posts.index')->with('success', 'Публикация удалена.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileRequest;
use App\Models\BotUser;
use App\Services\ProfileModeration;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Модерация профилей участниц из Telegram-бота (раньше — Filament BotUserResource).
 */
class ProfileController extends Controller
{
    private const STATUSES = [
        BotUser::STATUS_PENDING,
        BotUser::STATUS_APPROVED,
        BotUser::STATUS_REJECTED,
    ];

    private const SORTABLE = ['full_name', 'status', 'created_at'];

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $status = in_array($status, self::STATUSES, true) ? $status : '';
        $search = trim($request->string('q')->toString());
        $sort = in_array($request->string('sort')->toString(), self::SORTABLE, true)
            ? $request->string('sort')->toString()
            : 'created_at';
        $dir = $request->string('dir', $sort === 'created_at' ? 'desc' : 'asc')->toString() === 'asc' ? 'asc' : 'desc';

        $profiles = BotUser::query()
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('full_name', 'like', "%{$search}%")
                ->orWhere('telegram_username', 'like', "%{$search}%")))
            ->orderBy($sort, $dir)
            ->paginate(25)
            ->withQueryString();

        $counts = BotUser::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.profiles.index', [
            'profiles' => $profiles,
            'status' => $status,
            'search' => $search,
            'counts' => $counts,
        ]);
    }

    public function show(BotUser $profile): View
    {
        return view('admin.profiles.show', ['profile' => $profile]);
    }

    public function edit(BotUser $profile): View
    {
        return view('admin.profiles.edit', ['profile' => $profile]);
    }

    public function update(ProfileRequest $request, BotUser $profile): RedirectResponse
    {
        $profile->update($request->validated());

        return redirect()->route('admin.profiles.show', $profile)->with('success', 'Профиль сохранён.');
    }

    public function approve(BotUser $profile, ProfileModeration $moderation): RedirectResponse
    {
        $moderation->approve($profile);

        return back()->with('success', 'Профиль участницы одобрен.');
    }

    public function reject(BotUser $profile, ProfileModeration $moderation): RedirectResponse
    {
        $moderation->reject($profile);

        return back()->with('success', 'Профиль участницы отклонён.');
    }

    public function destroy(BotUser $profile): RedirectResponse
    {
        $profile->delete();

        return redirect()->route('admin.profiles.index')->with('success', 'Профиль удалён.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ], [], ['ids' => 'профили'])['ids'];

        $deleted = BotUser::query()->whereIn('id', $ids)->delete();

        return redirect()->route('admin.profiles.index')->with('success', "Удалено профилей: {$deleted}.");
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Обращения из формы «Связаться с нами» на странице «Контакты». Открытое обращение считается прочитанным. */
class ContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->toString();
        $onlyUnread = $request->boolean('unread');

        $messages = ContactMessage::query()
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('organization', 'like', "%{$search}%")
                ->orWhere('message', 'like', "%{$search}%")))
            ->when($onlyUnread, fn ($q) => $q->unread())
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.contact-messages.index', compact('messages', 'search', 'onlyUnread'));
    }

    public function show(ContactMessage $contactMessage): View
    {
        if (! $contactMessage->isRead()) {
            $contactMessage->forceFill(['read_at' => now()])->save();
        }

        return view('admin.contact-messages.show', ['contact' => $contactMessage]);
    }

    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->delete();

        return redirect()->route('admin.contact-messages.index')->with('success', __('Обращение удалено.'));
    }
}

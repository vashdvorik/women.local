<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ManagesFlatCards;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventRequest;
use App\Models\Event;
use App\Support\Blocks;
use App\Support\CardTone;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    use ManagesFlatCards;

    private const TEXT_FIELDS = ['type', 'date_label', 'title', 'description'];

    public function index(): View
    {
        $events = Event::with('translations')->ordered()->get();

        return view('admin.events.index', compact('events'));
    }

    public function create(): View
    {
        return view('admin.events.create', ['event' => new Event(['tone' => CardTone::DEFAULT])]);
    }

    public function store(EventRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $event = Event::create([
                'image_path' => Blocks::normalizePath($request->validated('image_path')),
                'tone' => $request->validated('tone'),
                'starts_at' => $request->validated('starts_at'),
                'url' => $request->validated('url'),
                'is_published' => $request->boolean('is_published'),
                'position' => $this->nextPosition(Event::class),
            ]);

            $this->syncFlatTranslations($event, $request->validated('translations', []), self::TEXT_FIELDS);
        });

        return redirect()->route('admin.events.index')->with('success', 'Новость добавлена.');
    }

    public function edit(Event $event): View
    {
        $event->load('translations');

        return view('admin.events.edit', compact('event'));
    }

    public function update(EventRequest $request, Event $event): RedirectResponse
    {
        DB::transaction(function () use ($request, $event) {
            $event->update([
                'image_path' => Blocks::normalizePath($request->validated('image_path')),
                'tone' => $request->validated('tone'),
                'starts_at' => $request->validated('starts_at'),
                'url' => $request->validated('url'),
                'is_published' => $request->boolean('is_published'),
            ]);

            $this->syncFlatTranslations($event, $request->validated('translations', []), self::TEXT_FIELDS);
        });

        return redirect()->route('admin.events.index')->with('success', 'Новость сохранена.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $event->delete();

        return redirect()->route('admin.events.index')->with('success', 'Новость удалена.');
    }

    public function move(Request $request, Event $event): RedirectResponse
    {
        $this->moveByPosition($event, $request->string('direction')->toString());

        return redirect()->route('admin.events.index');
    }
}

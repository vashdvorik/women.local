<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ResolveSlug;
use App\Http\Controllers\Concerns\CreatesBroadcastDraft;
use App\Http\Controllers\Concerns\ManagesFlatCards;
use App\Http\Controllers\Concerns\SyncsPageContent;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventRequest;
use App\Models\Event;
use App\Support\Blocks;
use App\Support\CardTone;
use App\Support\EventEditorData;
use App\Support\Locales;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    use CreatesBroadcastDraft;
    use ManagesFlatCards;
    use SyncsPageContent;

    private const TEXT_FIELDS = ['type', 'date_label', 'title', 'description'];

    public function __construct(private readonly ResolveSlug $slugger)
    {
    }

    public function index(): View
    {
        $events = Event::with('translations')->ordered()->get();

        return view('admin.events.index', compact('events'));
    }

    public function create(): View
    {
        $event = new Event(['tone' => CardTone::DEFAULT]);

        return view('admin.events.create', ['event' => $event, 'editor' => EventEditorData::make($event)]);
    }

    public function store(EventRequest $request): RedirectResponse
    {
        $event = DB::transaction(function () use ($request) {
            $translations = $request->validated('translations', []);

            $event = Event::create([
                'slug' => $this->slugFor(new Event(), $request),
                'image_path' => Blocks::normalizePath($request->validated('image_path')),
                'tone' => $request->validated('tone'),
                'starts_at' => $request->validated('starts_at'),
                'url' => $request->validated('url'),
                'is_published' => $request->boolean('is_published'),
                'position' => $this->nextPosition(Event::class),
            ]);

            $this->syncFlatTranslations($event, $translations, self::TEXT_FIELDS);
            $this->syncContent($event, $translations);

            return $event;
        });

        return redirect()->route('admin.events.index')->with('success', trim(__('Новость добавлена.').' '.$this->broadcastDraftNote($request, $event)));
    }

    public function edit(Event $event): View
    {
        $event->load('translations');

        return view('admin.events.edit', ['event' => $event, 'editor' => EventEditorData::make($event)]);
    }

    public function update(EventRequest $request, Event $event): RedirectResponse
    {
        DB::transaction(function () use ($request, $event) {
            $translations = $request->validated('translations', []);

            $event->update([
                'slug' => $this->slugFor($event, $request),
                'image_path' => Blocks::normalizePath($request->validated('image_path')),
                'tone' => $request->validated('tone'),
                'starts_at' => $request->validated('starts_at'),
                'url' => $request->validated('url'),
                'is_published' => $request->boolean('is_published'),
            ]);

            $this->syncFlatTranslations($event, $translations, self::TEXT_FIELDS);
            $this->syncContent($event, $translations);
        });

        return redirect()->route('admin.events.index')->with('success', trim(__('Новость сохранена.').' '.$this->broadcastDraftNote($request, $event)));
    }

    public function destroy(Event $event): RedirectResponse
    {
        $event->delete();

        return redirect()->route('admin.events.index')->with('success', __('Новость удалена.'));
    }

    public function move(Request $request, Event $event): RedirectResponse
    {
        $this->moveByPosition($event, $request->string('direction')->toString());

        return redirect()->route('admin.events.index');
    }

    /**
     * Адрес страницы: введённый вручную или из русского названия; осмысленный адрес, однажды заданный,
     * не перезаписывается. Название обрезается: после транслитерации оно длиннее, а колонка ограничена.
     */
    private function slugFor(Event $event, EventRequest $request): string
    {
        $title = (string) $request->validated('translations.ru.title', '');

        return $this->slugger->handle($event, $request->validated('slug'), mb_substr($title, 0, 80));
    }
}

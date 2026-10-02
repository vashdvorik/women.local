<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ResolveSlug;
use App\Http\Controllers\Concerns\ManagesFlatCards;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventRequest;
use App\Models\Event;
use App\Support\Blocks;
use App\Support\CardTone;
use App\Support\EventEditorData;
use App\Support\Locales;
use App\Support\TranslationStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    use ManagesFlatCards;

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
        DB::transaction(function () use ($request) {
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
        });

        return redirect()->route('admin.events.index')->with('success', __('Новость добавлена.'));
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

        return redirect()->route('admin.events.index')->with('success', __('Новость сохранена.'));
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

    /**
     * Текст страницы новости. Структура блоков и картинки берутся из русской версии, в переводах
     * сохраняется только их текст (Blocks::mergeTranslation): так же устроены публикации.
     *
     * @param  array<string, array<string, mixed>>  $input  translations из запроса, по языкам
     */
    private function syncContent(Event $event, array $input): void
    {
        $event->load('translations');

        $primary = Blocks::canonical($input[Locales::PRIMARY]['content'] ?? [], Blocks::ARTICLE_KINDS);

        foreach (Locales::ALL as $locale) {
            $content = $locale === Locales::PRIMARY
                ? $primary
                : Blocks::mergeTranslation($primary, $input[$locale]['content'] ?? []);

            $row = $event->translations->firstWhere('locale', $locale);

            if ($row) {
                $row->update(['content' => $content ?: null]);

                continue;
            }

            // У языка нет карточки (название и описание пустые): строку создаём, только если в блоках
            // есть переведённый текст, иначе перевод, набранный в редакторе текста, потерялся бы.
            if ($locale !== Locales::PRIMARY && TranslationStatus::hasText($content)) {
                $event->translations()->create(['locale' => $locale, 'content' => $content]);
            }
        }
    }
}

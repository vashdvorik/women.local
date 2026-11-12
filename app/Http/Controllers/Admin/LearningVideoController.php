<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LearningVideoRequest;
use App\Models\LearningVideo;
use App\Support\Blocks;
use App\Support\Locales;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * «Кабинеты участниц → Обучающие видео»: ролики YouTube для раздела «Обучение» в кабинете. Порядок задаётся перетаскиванием;
 * выключенное видео («показывать» снято) остаётся черновиком, участницы его не видят.
 */
class LearningVideoController extends Controller
{
    public function index(): View
    {
        $videos = LearningVideo::with('translations')->ordered()->get();

        return view('admin.learning-videos.index', compact('videos'));
    }

    /** Порядок видео в кабинете: приходит списком id в новом порядке (перетаскивание и кнопки в списке). */
    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:learning_videos,id'],
        ]);

        LearningVideo::applyOrder($data['ids']);

        return response()->json(['ok' => true]);
    }

    public function create(): View
    {
        return view('admin.learning-videos.create', ['video' => new LearningVideo(['is_published' => true])]);
    }

    public function store(LearningVideoRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $video = LearningVideo::create([
                'youtube_id' => $request->youtubeId,
                'youtube_url' => $request->validated('youtube_url'),
                'cover_path' => Blocks::normalizePath($request->validated('cover_path')),
                'is_published' => $request->boolean('is_published'),
                // Новое видео встаёт первым: позиция на единицу меньше самой первой (колонка знаковая, ≤ 0 допустимо).
                'position' => (LearningVideo::min('position') ?? 1) - 1,
            ]);

            $this->syncTexts($video, $request->validated('translations', []));
        });

        return redirect()->route('admin.learning-videos.index')->with('success', __('Видео добавлено.'));
    }

    public function edit(LearningVideo $learningVideo): View
    {
        $learningVideo->load('translations');

        return view('admin.learning-videos.edit', ['video' => $learningVideo]);
    }

    public function update(LearningVideoRequest $request, LearningVideo $learningVideo): RedirectResponse
    {
        DB::transaction(function () use ($request, $learningVideo) {
            $learningVideo->update([
                'youtube_id' => $request->youtubeId,
                'youtube_url' => $request->validated('youtube_url'),
                'cover_path' => Blocks::normalizePath($request->validated('cover_path')),
                'is_published' => $request->boolean('is_published'),
            ]);

            $this->syncTexts($learningVideo, $request->validated('translations', []));
        });

        return redirect()->route('admin.learning-videos.index')->with('success', __('Видео сохранено.'));
    }

    /** Включить или скрыть видео одним нажатием из списка. */
    public function toggle(LearningVideo $learningVideo): RedirectResponse
    {
        $learningVideo->update(['is_published' => ! $learningVideo->is_published]);

        return redirect()->route('admin.learning-videos.index')
            ->with('success', $learningVideo->is_published ? __('Видео показывается участницам.') : __('Видео скрыто от участниц.'));
    }

    public function destroy(LearningVideo $learningVideo): RedirectResponse
    {
        $learningVideo->delete();

        return redirect()->route('admin.learning-videos.index')->with('success', __('Видео удалено.'));
    }

    /** Порядок меняется кнопками вверх/вниз. */
    public function move(Request $request, LearningVideo $learningVideo): RedirectResponse
    {
        $direction = $request->string('direction')->toString();

        $neighbour = LearningVideo::query()
            ->when($direction === 'up',
                fn ($q) => $q->where('position', '<', $learningVideo->position)->orderByDesc('position'),
                fn ($q) => $q->where('position', '>', $learningVideo->position)->orderBy('position'))
            ->first();

        if ($neighbour) {
            DB::transaction(function () use ($learningVideo, $neighbour) {
                [$learningVideo->position, $neighbour->position] = [$neighbour->position, $learningVideo->position];
                $learningVideo->save();
                $neighbour->save();
            });
        }

        return redirect()->route('admin.learning-videos.index');
    }

    private function syncTexts(LearningVideo $video, array $translations): void
    {
        $video->loadMissing('translations');

        foreach (Locales::ALL as $locale) {
            $title = trim((string) ($translations[$locale]['title'] ?? '')) ?: null;
            $description = trim((string) ($translations[$locale]['description'] ?? '')) ?: null;
            $existing = $video->translations->firstWhere('locale', $locale);

            // Язык без названия и описания не хранится: участнице покажется русский текст.
            if ($locale !== Locales::PRIMARY && $title === null && $description === null) {
                $existing?->delete();

                continue;
            }

            $video->translations()->updateOrCreate(
                ['learning_video_id' => $video->id, 'locale' => $locale],
                ['title' => $title, 'description' => $description],
            );
        }
    }
}

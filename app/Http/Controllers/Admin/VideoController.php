<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\VideoRequest;
use App\Models\Video;
use App\Support\Locales;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VideoController extends Controller
{
    public function index(): View
    {
        $videos = Video::with('translations')->orderBy('position')->orderBy('id')->get();

        return view('admin.videos.index', compact('videos'));
    }

    public function create(): View
    {
        return view('admin.videos.create', ['video' => new Video()]);
    }

    public function store(VideoRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $video = Video::create([
                'youtube_id' => $request->youtubeId,
                'youtube_url' => $request->validated('youtube_url'),
                'event_date' => $request->validated('event_date'),
                'cover_path' => \App\Support\Blocks::normalizePath($request->validated('cover_path')),
                // Новое видео встаёт в конец сортировки (AGENTS.md §15).
                'position' => (Video::max('position') ?? 0) + 1,
            ]);

            $this->syncTitles($video, $request->validated('translations', []));
        });

        return redirect()->route('admin.videos.index')->with('success', 'Видео добавлено.');
    }

    public function edit(Video $video): View
    {
        $video->load('translations');

        return view('admin.videos.edit', compact('video'));
    }

    public function update(VideoRequest $request, Video $video): RedirectResponse
    {
        DB::transaction(function () use ($request, $video) {
            $video->update([
                'youtube_id' => $request->youtubeId,
                'youtube_url' => $request->validated('youtube_url'),
                'event_date' => $request->validated('event_date'),
                'cover_path' => \App\Support\Blocks::normalizePath($request->validated('cover_path')),
            ]);

            $this->syncTitles($video, $request->validated('translations', []));
        });

        return redirect()->route('admin.videos.index')->with('success', 'Видео сохранено.');
    }

    public function destroy(Video $video): RedirectResponse
    {
        $video->delete();

        return redirect()->route('admin.videos.index')->with('success', 'Видео удалено.');
    }

    /** Порядок меняется кнопками вверх/вниз. */
    public function move(Request $request, Video $video): RedirectResponse
    {
        $direction = $request->string('direction')->toString();

        $neighbour = Video::query()
            ->when($direction === 'up',
                fn ($q) => $q->where('position', '<', $video->position)->orderByDesc('position'),
                fn ($q) => $q->where('position', '>', $video->position)->orderBy('position'))
            ->first();

        if ($neighbour) {
            DB::transaction(function () use ($video, $neighbour) {
                [$video->position, $neighbour->position] = [$neighbour->position, $video->position];
                $video->save();
                $neighbour->save();
            });
        }

        return redirect()->route('admin.videos.index');
    }

    private function syncTitles(Video $video, array $translations): void
    {
        $video->loadMissing('translations');

        foreach (Locales::ALL as $locale) {
            $title = trim((string) ($translations[$locale]['title'] ?? '')) ?: null;
            $existing = $video->translations->firstWhere('locale', $locale);

            if ($locale !== Locales::PRIMARY && $title === null) {
                $existing?->delete();

                continue;
            }

            $video->translations()->updateOrCreate(
                ['video_id' => $video->id, 'locale' => $locale],
                ['title' => $title],
            );
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TagRequest;
use App\Models\Tag;
use App\Support\Locales;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class TagController extends Controller
{
    public function index(): View
    {
        $tags = Tag::with('translations')
            ->withCount(['posts', 'opportunities'])
            ->get()
            ->sortBy(fn (Tag $t) => mb_strtolower($t->rawTranslation('ru')?->name ?? ''))
            ->values();

        return view('admin.tags.index', compact('tags'));
    }

    public function create(): View
    {
        return view('admin.tags.create', ['tag' => new Tag(['color' => '#0066cc'])]);
    }

    public function store(TagRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $tag = Tag::create(['color' => strtolower($request->validated('color'))]);
            $this->syncNames($tag, $request->validated('names'));
        });

        return redirect()->route('admin.tags.index')->with('success', 'Тег создан.');
    }

    public function edit(Tag $tag): View
    {
        $tag->load('translations');

        return view('admin.tags.edit', compact('tag'));
    }

    public function update(TagRequest $request, Tag $tag): RedirectResponse
    {
        DB::transaction(function () use ($request, $tag) {
            $tag->update(['color' => strtolower($request->validated('color'))]);
            $this->syncNames($tag, $request->validated('names'));
        });

        return redirect()->route('admin.tags.index')->with('success', 'Тег сохранён.');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        // Удаление тега не удаляет новости и возможности — tag_id обнуляется через nullOnDelete.
        $tag->delete();

        return redirect()->route('admin.tags.index')->with('success', 'Тег удалён.');
    }

    private function syncNames(Tag $tag, array $names): void
    {
        $tag->loadMissing('translations');

        foreach (Locales::ALL as $locale) {
            $tag->translations()->updateOrCreate(
                ['tag_id' => $tag->id, 'locale' => $locale],
                ['name' => trim($names[$locale])],
            );
        }
    }
}

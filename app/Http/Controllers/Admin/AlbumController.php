<?php

namespace App\Http\Controllers\Admin;

use App\Actions\SaveAlbum;
use App\Http\Controllers\Controller;
use App\Http\Requests\AlbumRequest;
use App\Models\Album;
use App\Support\AlbumEditorData;
use App\Support\ArticleList;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AlbumController extends Controller
{
    public function index(Request $request): View
    {
        $albums = ArticleList::for(Album::query(), $request)->withQueryString();

        return view('admin.albums.index', [
            'albums' => $albums,
            'search' => $request->string('q')->toString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.albums.create', ['editor' => AlbumEditorData::make(new Album())]);
    }

    public function store(AlbumRequest $request, SaveAlbum $saver): RedirectResponse
    {
        // Новый альбом создаётся опубликованным (AGENTS.md §15); «Сохранить
        // черновик» присылает intent=unpublish.
        $intent = $request->string('intent', 'publish')->toString();
        $album = $saver->handle(null, $request->validated(), $intent);

        return redirect()->route('admin.albums.edit', $album)->with('success',
            $album->status === \App\Enums\PublishStatus::Draft ? 'Черновик сохранён.' : 'Фотоальбом опубликован.');
    }

    public function edit(Album $album): View
    {
        $album->load('translations');

        return view('admin.albums.edit', [
            'album' => $album,
            'editor' => AlbumEditorData::make($album),
        ]);
    }

    public function update(AlbumRequest $request, Album $album, SaveAlbum $saver): RedirectResponse
    {
        $intent = $request->string('intent', 'save')->toString();
        $album = $saver->handle($album, $request->validated(), $intent);

        return redirect()->route('admin.albums.edit', $album)->with('success', match ($intent) {
            'publish' => 'Фотоальбом опубликован.',
            'unpublish' => 'Фотоальбом снят с публикации.',
            default => 'Изменения сохранены.',
        });
    }

    public function destroy(Album $album): RedirectResponse
    {
        $album->delete();

        return redirect()->route('admin.albums.index')->with('success', 'Фотоальбом удалён.');
    }
}

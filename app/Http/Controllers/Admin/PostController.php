<?php

namespace App\Http\Controllers\Admin;

use App\Actions\PublishArticle;
use App\Actions\SaveArticle;
use App\Http\Controllers\Concerns\PublishesArticle;
use App\Http\Controllers\Controller;
use App\Http\Requests\PostRequest;
use App\Models\Post;
use App\Support\ArticleEditorData;
use App\Support\ArticleList;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    use PublishesArticle;

    public function index(Request $request): View
    {
        $posts = ArticleList::for(Post::query(), $request, ['translations', 'tag.translations'])
            ->withQueryString();

        return view('admin.posts.index', [
            'posts' => $posts,
            'sort' => $request->string('sort', 'recent')->toString(),
            'search' => $request->string('q')->toString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.posts.create', [
            'editor' => ArticleEditorData::make(new Post()),
        ]);
    }

    public function store(PostRequest $request, SaveArticle $saver, PublishArticle $publisher): RedirectResponse
    {
        $post = $saver->handle(Post::class, null, $request->validated());

        return $this->finishArticle($post, $request, $publisher, 'admin.posts.edit', 'Новость', created: true);
    }

    public function edit(Post $post): View
    {
        $post->load('translations');

        return view('admin.posts.edit', [
            'post' => $post,
            'editor' => ArticleEditorData::make($post),
        ]);
    }

    public function update(PostRequest $request, Post $post, SaveArticle $saver, PublishArticle $publisher): RedirectResponse
    {
        $post = $saver->handle(Post::class, $post, $request->validated());

        return $this->finishArticle($post, $request, $publisher, 'admin.posts.edit', 'Новость', created: false);
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Новость удалена.');
    }
}

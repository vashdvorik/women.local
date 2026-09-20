<?php

namespace App\Http\Controllers\Admin;

use App\Actions\PublishArticle;
use App\Actions\SaveArticle;
use App\Http\Controllers\Concerns\PublishesArticle;
use App\Http\Controllers\Controller;
use App\Http\Requests\SiteOpportunityRequest;
use App\Models\SiteOpportunity;
use App\Support\ArticleEditorData;
use App\Support\ArticleList;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SiteOpportunityController extends Controller
{
    use PublishesArticle;

    public function index(Request $request): View
    {
        $opportunities = ArticleList::for(SiteOpportunity::query(), $request, ['translations', 'tag.translations'])
            ->withQueryString();

        return view('admin.opportunities.index', [
            'opportunities' => $opportunities,
            'sort' => $request->string('sort', 'recent')->toString(),
            'search' => $request->string('q')->toString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.opportunities.create', [
            'editor' => ArticleEditorData::make(new SiteOpportunity()),
        ]);
    }

    public function store(SiteOpportunityRequest $request, SaveArticle $saver, PublishArticle $publisher): RedirectResponse
    {
        $opportunity = $saver->handle(SiteOpportunity::class, null, $request->validated());

        return $this->finishArticle($opportunity, $request, $publisher, 'admin.opportunities.edit', 'Возможность', created: true);
    }

    public function edit(SiteOpportunity $opportunity): View
    {
        $opportunity->load('translations');

        return view('admin.opportunities.edit', [
            'opportunity' => $opportunity,
            'editor' => ArticleEditorData::make($opportunity),
        ]);
    }

    public function update(SiteOpportunityRequest $request, SiteOpportunity $opportunity, SaveArticle $saver, PublishArticle $publisher): RedirectResponse
    {
        $opportunity = $saver->handle(SiteOpportunity::class, $opportunity, $request->validated());

        return $this->finishArticle($opportunity, $request, $publisher, 'admin.opportunities.edit', 'Возможность', created: false);
    }

    public function destroy(SiteOpportunity $opportunity): RedirectResponse
    {
        $opportunity->delete();

        return redirect()
            ->route('admin.opportunities.index')
            ->with('success', 'Возможность удалена.');
    }
}

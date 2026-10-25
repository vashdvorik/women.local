<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ResolveSlug;
use App\Http\Controllers\Concerns\SyncsPageContent;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest;
use App\Models\Project;
use App\Support\Blocks;
use App\Support\EventEditorData;
use App\Support\Locales;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    use SyncsPageContent;

    public function __construct(private readonly ResolveSlug $slugger)
    {
    }

    public function index(): View
    {
        $projects = Project::with('translations')->orderBy('position')->orderBy('id')->get();

        return view('admin.projects.index', compact('projects'));
    }

    public function create(): View
    {
        $project = new Project();

        return view('admin.projects.create', ['project' => $project, 'editor' => EventEditorData::make($project)]);
    }

    public function store(ProjectRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $translations = $request->validated('translations', []);

            $project = Project::create([
                'slug' => $this->slugFor(new Project(), $request),
                'image_path' => Blocks::normalizePath($request->validated('image_path')),
                'url' => $request->validated('url'),
                'is_published' => $request->boolean('is_published'),
                'position' => (Project::max('position') ?? 0) + 1,
            ]);

            $this->syncTranslations($project, $translations);
            $this->syncContent($project, $translations);
        });

        return redirect()->route('admin.projects.index')->with('success', __('Проект добавлен.'));
    }

    public function edit(Project $project): View
    {
        $project->load('translations');

        return view('admin.projects.edit', ['project' => $project, 'editor' => EventEditorData::make($project)]);
    }

    public function update(ProjectRequest $request, Project $project): RedirectResponse
    {
        DB::transaction(function () use ($request, $project) {
            $translations = $request->validated('translations', []);

            $project->update([
                'slug' => $this->slugFor($project, $request),
                'image_path' => Blocks::normalizePath($request->validated('image_path')),
                'url' => $request->validated('url'),
                'is_published' => $request->boolean('is_published'),
            ]);

            $this->syncTranslations($project, $translations);
            $this->syncContent($project, $translations);
        });

        return redirect()->route('admin.projects.index')->with('success', __('Проект сохранён.'));
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()->route('admin.projects.index')->with('success', __('Проект удалён.'));
    }

    public function move(Request $request, Project $project): RedirectResponse
    {
        $direction = $request->string('direction')->toString();

        $neighbour = Project::query()
            ->when($direction === 'up',
                fn ($q) => $q->where('position', '<', $project->position)->orderByDesc('position'),
                fn ($q) => $q->where('position', '>', $project->position)->orderBy('position'))
            ->first();

        if ($neighbour) {
            DB::transaction(function () use ($project, $neighbour) {
                [$project->position, $neighbour->position] = [$neighbour->position, $project->position];
                $project->save();
                $neighbour->save();
            });
        }

        return redirect()->route('admin.projects.index');
    }

    /**
     * Адрес страницы: введённый вручную или из русского названия; осмысленный адрес, однажды заданный, не перезаписывается
     * (его уже могли разослать). У проекта без текста страницы адрес тоже хранится: страница появится, когда будет текст.
     */
    private function slugFor(Project $project, ProjectRequest $request): string
    {
        $title = (string) $request->validated('translations.ru.title', '');

        return $this->slugger->handle($project, $request->validated('slug'), mb_substr($title, 0, 80));
    }

    /** Русские title/category/text сохраняются; ro/en — только если заполнены. Текст страницы проекта (content) не затрагивается. */
    private function syncTranslations(Project $project, array $translations): void
    {
        $project->loadMissing('translations');

        foreach (Locales::ALL as $locale) {
            $fields = [
                'title' => trim((string) ($translations[$locale]['title'] ?? '')) ?: null,
                'category' => trim((string) ($translations[$locale]['category'] ?? '')) ?: null,
                'text' => trim((string) ($translations[$locale]['text'] ?? '')) ?: null,
            ];

            // Пустую карточку на ro/en убираем, но не вместе со страницей проекта: её текст в админке пока не редактируется
            $existing = $project->translations->firstWhere('locale', $locale);

            if ($locale !== Locales::PRIMARY && ! array_filter($fields) && blank($existing?->content)) {
                $existing?->delete();

                continue;
            }

            $project->translations()->updateOrCreate(
                ['project_id' => $project->id, 'locale' => $locale],
                $fields,
            );
        }
    }
}

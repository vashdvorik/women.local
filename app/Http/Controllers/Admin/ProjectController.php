<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest;
use App\Models\Project;
use App\Support\Blocks;
use App\Support\Locales;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    public function index(): View
    {
        $projects = Project::with('translations')->orderBy('position')->orderBy('id')->get();

        return view('admin.projects.index', compact('projects'));
    }

    public function create(): View
    {
        return view('admin.projects.create', ['project' => new Project()]);
    }

    public function store(ProjectRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $project = Project::create([
                'image_path' => Blocks::normalizePath($request->validated('image_path')),
                'url' => $request->validated('url'),
                'is_published' => $request->boolean('is_published'),
                'position' => (Project::max('position') ?? 0) + 1,
            ]);

            $this->syncTranslations($project, $request->validated('translations', []));
        });

        return redirect()->route('admin.projects.index')->with('success', 'Проект добавлен.');
    }

    public function edit(Project $project): View
    {
        $project->load('translations');

        return view('admin.projects.edit', compact('project'));
    }

    public function update(ProjectRequest $request, Project $project): RedirectResponse
    {
        DB::transaction(function () use ($request, $project) {
            $project->update([
                'image_path' => Blocks::normalizePath($request->validated('image_path')),
                'url' => $request->validated('url'),
                'is_published' => $request->boolean('is_published'),
            ]);

            $this->syncTranslations($project, $request->validated('translations', []));
        });

        return redirect()->route('admin.projects.index')->with('success', 'Проект сохранён.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()->route('admin.projects.index')->with('success', 'Проект удалён.');
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

    /** Русские title/category/text сохраняются; ro/en — только если заполнены. */
    private function syncTranslations(Project $project, array $translations): void
    {
        $project->loadMissing('translations');

        foreach (Locales::ALL as $locale) {
            $fields = [
                'title' => trim((string) ($translations[$locale]['title'] ?? '')) ?: null,
                'category' => trim((string) ($translations[$locale]['category'] ?? '')) ?: null,
                'text' => trim((string) ($translations[$locale]['text'] ?? '')) ?: null,
            ];

            if ($locale !== Locales::PRIMARY && ! array_filter($fields)) {
                $project->translations->firstWhere('locale', $locale)?->delete();

                continue;
            }

            $project->translations()->updateOrCreate(
                ['project_id' => $project->id, 'locale' => $locale],
                $fields,
            );
        }
    }
}

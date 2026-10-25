<?php

namespace App\Http\Controllers\PublicSite;

use App\Models\Project;
use App\Support\Locales;
use App\Support\PublicCache;
use App\Support\PublicCards;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * «Проекты»: карточки проектов, введённые в админке; порядок задаётся стрелками. У проекта со страницей карточка
 * ведёт на /projects/{адрес}: рассказ блоками и фотографии рядами.
 */
class ProjectsController extends ContentPageController
{
    public function __invoke(): View
    {
        // Карточки лежат в кеше публичных страниц (App\Support\PublicCache), а не читаются из базы на каждый заход.
        $cards = PublicCache::remember('projects.cards', fn () => Project::with('translations')
            ->where('is_published', true)
            ->orderBy('position')
            ->orderBy('id')
            ->get()
            ->map(fn (Project $project) => PublicCards::project($project))
            ->all());

        return $this->listing('projects', [
            // Надписи над заголовком нет: слово «Платформа» там было лишним.
            'eyebrow' => $this->tri('', '', ''),
            'title' => $this->tri(
                'Проекты и инициативы для женщин',
                'Projects and Initiatives for Women',
                'Proiecte și inițiative pentru femei'
            ),
            'intro' => $this->tri(
                'Образовательные программы, поддержка предпринимательства, цифровые инициативы и совместные проекты.',
                'Educational programmes, entrepreneurship support, digital initiatives and joint projects.',
                'Programe educaționale, sprijin pentru antreprenoriat, inițiative digitale și proiecte comune.'
            ),
        ], $cards);
    }

    /** Страница проекта. Снятый с публикации проект видит только администратор (предпросмотр). */
    public function show(Request $request, string $project): View
    {
        return $this->materialPage(
            $request,
            'projects',
            $this->tri('Проекты', 'Projects', 'Proiecte'),
            $project,
            fn (bool $withDrafts) => Project::with('translations')
                ->where('slug', $project)
                ->when(! $withDrafts, fn ($q) => $q->where('is_published', true))
                ->first(),
            fn (Project $item) => $this->articleData($item),
        );
    }

    /** @return array<string, mixed> */
    private function articleData(Project $project): array
    {
        $card = PublicCards::project($project);

        return [
            'title' => $card['title'],
            'excerpt' => $card['excerpt'],
            'image' => $card['image'],
            'badge' => null,
            'badge_style' => null,
            'meta' => PublicCards::field($project, 'category'),
            'blocks' => collect(Locales::ALL)->mapWithKeys(fn (string $l) => [$l => $project->renderBlocks($l)])->all(),
            'back' => [
                'href' => route('projects'),
                'label' => $this->tri('Все проекты', 'All projects', 'Toate proiectele'),
            ],
            'draft' => ! $project->is_published,
        ];
    }
}

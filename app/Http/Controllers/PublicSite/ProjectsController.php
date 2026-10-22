<?php

namespace App\Http\Controllers\PublicSite;

use App\Models\Project;
use App\Support\PublicCache;
use App\Support\PublicCards;
use Illuminate\Contracts\View\View;

/** «Проекты»: карточки проектов, введённые в админке; порядок задаётся стрелками. */
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
            'eyebrow' => $this->tri('Платформа', 'Platform', 'Platformă'),
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
}

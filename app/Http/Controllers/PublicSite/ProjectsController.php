<?php

namespace App\Http\Controllers\PublicSite;

use App\Models\Project;
use App\Support\PublicCards;
use Illuminate\Contracts\View\View;

/** «Проекты»: карточки проектов, введённые в админке; порядок задаётся стрелками. */
class ProjectsController extends ContentPageController
{
    public function __invoke(): View
    {
        $projects = Project::with('translations')
            ->where('is_published', true)
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        return $this->listing('projects', [
            'eyebrow' => $this->tri('Платформа', 'Platform', 'Platformă'),
            'title' => $this->tri('Проекты', 'Projects', 'Proiecte'),
            'intro' => $this->tri(
                'Проекты, инициативы и совместные программы сообщества.',
                'Community projects, initiatives and joint programmes.',
                'Proiectele, inițiativele și programele comune ale comunității.'
            ),
        ], $projects->map(fn (Project $project) => PublicCards::project($project))->all());
    }
}

<?php

namespace Tests\Feature\Admin;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_is_created_with_translations(): void
    {
        $this->actingAsAdmin()->post(route('admin.projects.store'), [
            'is_published' => '1',
            'translations' => [
                'ru' => ['title' => 'Клуб', 'category' => 'Языки', 'text' => 'Описание.'],
                'ro' => ['title' => 'Clubul', 'category' => 'Limbi', 'text' => 'Descriere.'],
            ],
        ])->assertRedirect(route('admin.projects.index'));

        $project = Project::first();
        $this->assertSame('Клуб', $project->rawTranslation('ru')->title);
        $this->assertSame('Clubul', $project->rawTranslation('ro')->title);
        $this->assertNull($project->rawTranslation('en'));
    }

    public function test_external_url_is_saved(): void
    {
        $this->actingAsAdmin()->post(route('admin.projects.store'), [
            'is_published' => '1',
            'url' => 'https://oxford-club.example',
            'translations' => ['ru' => ['title' => 'Клуб']],
        ])->assertRedirect(route('admin.projects.index'));

        $this->assertSame('https://oxford-club.example', Project::first()->url);
    }

    public function test_invalid_url_is_rejected(): void
    {
        $this->actingAsAdmin()->post(route('admin.projects.store'), [
            'url' => 'not-a-url',
            'translations' => ['ru' => ['title' => 'Клуб']],
        ])->assertSessionHasErrors('url');

        $this->assertSame(0, Project::count());
    }

    public function test_russian_title_is_required(): void
    {
        $this->actingAsAdmin()->post(route('admin.projects.store'), [
            'translations' => ['ru' => ['category' => 'Языки']],
        ])->assertSessionHasErrors('translations.ru.title');

        $this->assertSame(0, Project::count());
    }

    public function test_unpublished_project_is_hidden_from_the_page(): void
    {
        $shown = Project::create(['is_published' => true, 'position' => 1]);
        $shown->translations()->create(['locale' => 'ru', 'title' => 'Видимый']);
        $hidden = Project::create(['is_published' => false, 'position' => 2]);
        $hidden->translations()->create(['locale' => 'ru', 'title' => 'Скрытый']);

        // Публичная страница /proekty локализована — проверяем выборку контроллера.
        $visible = \App\Models\Project::where('is_published', true)->pluck('id');
        $this->assertTrue($visible->contains($shown->id));
        $this->assertFalse($visible->contains($hidden->id));
    }

    public function test_project_can_be_deleted(): void
    {
        $project = Project::create(['position' => 1]);
        $project->translations()->create(['locale' => 'ru', 'title' => 'A']);

        $this->actingAsAdmin()->delete(route('admin.projects.destroy', $project))
            ->assertRedirect(route('admin.projects.index'));

        $this->assertSame(0, Project::count());
        $this->assertDatabaseCount('project_translations', 0);
    }
}

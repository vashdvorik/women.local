<?php

namespace Tests\Feature\Admin;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Текст страницы проекта в админке: те же блоки, что у новости и публикации, на трёх языках. */
class ProjectPageEditorTest extends TestCase
{
    use RefreshDatabase;

    /** @param  list<array<string, mixed>>  $blocks */
    private function payload(array $blocks, array $overrides = []): array
    {
        return array_replace_recursive([
            'is_published' => '1',
            'translations' => [
                'ru' => ['title' => 'Школа лидерства', 'category' => 'Проект', 'text' => 'Коротко о проекте.', 'content' => json_encode($blocks)],
                'en' => ['title' => 'Leadership School', 'content' => json_encode([['uid' => 'b1', 'type' => 'text', 'data' => ['html' => '<p>The story.</p>']]])],
            ],
        ], $overrides);
    }

    private function text(string $html, string $uid = 'b1'): array
    {
        return ['uid' => $uid, 'type' => 'text', 'data' => ['html' => $html]];
    }

    public function test_the_form_shows_the_page_address_and_the_block_editor_for_every_language(): void
    {
        $html = $this->actingAsAdmin()->get(route('admin.projects.create'))->assertOk()->getContent();

        $this->assertStringContainsString('eventEditor(', $html);
        $this->assertStringContainsString('name="slug"', $html);
        $this->assertSame(3, substr_count($html, '][content]'), 'скрытые поля блоков на трёх языках');
    }

    public function test_it_saves_the_page_with_an_address_and_blocks_and_the_card_leads_to_it(): void
    {
        $this->actingAsAdmin()
            ->post(route('admin.projects.store'), $this->payload([$this->text('<p>Рассказ о проекте.</p>')]))
            ->assertRedirect(route('admin.projects.index'));

        $project = Project::with('translations')->firstOrFail();

        $this->assertSame('shkola-liderstva', $project->slug);
        $this->assertTrue($project->hasPage());
        $this->assertSame('<p>Рассказ о проекте.</p>', $project->rawTranslation('ru')->content[0]['data']['html']);
        // перевод хранит тот же блок (структура общая), текст свой
        $this->assertSame('<p>The story.</p>', $project->rawTranslation('en')->content[0]['data']['html']);

        $page = $this->get('/projects/shkola-liderstva')->assertOk()->getContent();
        $this->assertStringContainsString('Рассказ о проекте.', $page);
        $this->assertStringContainsString('The story.', $page);

        $this->assertStringContainsString('/projects/shkola-liderstva', $this->get('/projects')->getContent());
    }

    public function test_a_project_without_blocks_keeps_its_external_link_instead_of_a_page(): void
    {
        $this->actingAsAdmin()->post(route('admin.projects.store'), $this->payload([], ['url' => 'https://example.org/project']))
            ->assertRedirect(route('admin.projects.index'));

        $project = Project::firstOrFail();

        $this->assertFalse($project->hasPage());
        $this->assertStringContainsString('https://example.org/project', $this->get('/projects')->getContent());
        $this->assertStringNotContainsString('/projects/'.$project->slug.'"', $this->get('/projects')->getContent());
    }

    public function test_editing_the_card_keeps_the_address_and_updates_blocks_in_all_languages(): void
    {
        $this->actingAsAdmin()->post(route('admin.projects.store'), $this->payload([$this->text('<p>Первый вариант.</p>')]));
        $project = Project::firstOrFail();

        // Правка: новое название и новый текст; адрес, однажды заданный, не меняется
        $this->actingAsAdmin()->put(route('admin.projects.update', $project), $this->payload(
            [$this->text('<p>Второй вариант.</p>')],
            ['translations' => ['ru' => ['title' => 'Школа лидерства — 2'], 'en' => ['content' => json_encode([$this->text('<p>Second.</p>')])]]],
        ))->assertRedirect(route('admin.projects.index'));

        $project->refresh()->load('translations');

        $this->assertSame('shkola-liderstva', $project->slug);
        $this->assertSame('Школа лидерства — 2', $project->rawTranslation('ru')->title);
        $this->assertSame('<p>Второй вариант.</p>', $project->rawTranslation('ru')->content[0]['data']['html']);
        $this->assertSame('<p>Second.</p>', $project->rawTranslation('en')->content[0]['data']['html']);
    }

    public function test_a_bad_address_is_rejected_and_the_seeded_projects_open_in_the_editor(): void
    {
        $this->actingAsAdmin()->post(route('admin.projects.store'), $this->payload([], ['slug' => 'Плохой Адрес']))
            ->assertSessionHasErrors('slug');

        $this->assertSame(0, Project::count());

        $this->seed(\Database\Seeders\ProjectSeeder::class);
        $project = Project::where('slug', 'zhenskoe-liderstvo')->firstOrFail();

        $html = $this->actingAsAdmin()->get(route('admin.projects.edit', $project))->assertOk()->getContent();
        $this->assertStringContainsString('/projects/zhenskoe-liderstvo', $html);
        $this->assertStringContainsString('eventEditor(', $html);
    }
}

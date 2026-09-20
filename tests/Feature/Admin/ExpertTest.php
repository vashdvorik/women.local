<?php

namespace Tests\Feature\Admin;

use App\Models\Expert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpertTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_replace_recursive([
            'is_published' => '1',
            'tone' => 'teal',
            'translations' => [
                'ru' => ['name' => 'Каролина Бугаян', 'role' => 'Президент AFAM', 'description' => 'Развивает предпринимательство.', 'tags' => 'Эксперт, Партнёрства'],
            ],
        ], $overrides);
    }

    public function test_index_and_forms_render(): void
    {
        $expert = Expert::create(['tone' => 'pink', 'position' => 1]);
        $expert->translations()->create(['locale' => 'ru', 'name' => 'Ирина Эксперт']);

        $this->actingAsAdmin()->get(route('admin.experts.index'))->assertOk()->assertSee('Ирина Эксперт');
        $this->actingAsAdmin()->get(route('admin.experts.create'))->assertOk();
        $this->actingAsAdmin()->get(route('admin.experts.edit', $expert))->assertOk()->assertSee('Ирина Эксперт');
    }

    public function test_expert_is_created_with_russian_only_and_appended_to_the_end(): void
    {
        Expert::create(['position' => 5]);

        $this->actingAsAdmin()->post(route('admin.experts.store'), $this->payload())
            ->assertRedirect(route('admin.experts.index'));

        $expert = Expert::orderByDesc('id')->first();
        $this->assertSame(6, $expert->position);
        $this->assertSame('teal', $expert->tone);
        $this->assertTrue($expert->is_published);
        $this->assertSame('Каролина Бугаян', $expert->rawTranslation('ru')->name);
        $this->assertSame(['Эксперт', 'Партнёрства'], $expert->rawTranslation('ru')->tags);
        $this->assertNull($expert->rawTranslation('ro'));
        $this->assertNull($expert->rawTranslation('en'));
    }

    public function test_secondary_languages_are_saved_only_when_filled_and_removed_when_cleared(): void
    {
        $this->actingAsAdmin()->post(route('admin.experts.store'), $this->payload([
            'translations' => ['en' => ['name' => 'Carolina Bugaiyan', 'tags' => 'Expert; Partnerships']],
        ]));

        $expert = Expert::firstOrFail();
        $this->assertSame('Carolina Bugaiyan', $expert->rawTranslation('en')->name);
        $this->assertSame(['Expert', 'Partnerships'], $expert->rawTranslation('en')->tags);

        $this->actingAsAdmin()->put(route('admin.experts.update', $expert), $this->payload([
            'translations' => ['en' => ['name' => '', 'tags' => '']],
        ]))->assertRedirect(route('admin.experts.index'));

        $this->assertNull($expert->fresh()->load('translations')->rawTranslation('en'));
    }

    public function test_field_falls_back_to_russian_when_a_translation_is_missing(): void
    {
        $this->actingAsAdmin()->post(route('admin.experts.store'), $this->payload());

        $expert = Expert::firstOrFail()->load('translations');

        $this->assertSame('Каролина Бугаян', $expert->field('name', 'en'));
        $this->assertSame('Президент AFAM', $expert->field('role', 'ro'));
    }

    public function test_russian_name_is_required(): void
    {
        $this->actingAsAdmin()->post(route('admin.experts.store'), $this->payload([
            'translations' => ['ru' => ['name' => '']],
        ]))->assertSessionHasErrors('translations.ru.name');

        $this->assertSame(0, Expert::count());
    }

    public function test_unknown_tone_is_rejected(): void
    {
        $this->actingAsAdmin()->post(route('admin.experts.store'), $this->payload(['tone' => 'neon']))
            ->assertSessionHasErrors('tone');

        $this->assertSame(0, Expert::count());
    }

    public function test_photo_path_is_normalised_and_foreign_paths_are_dropped(): void
    {
        $this->actingAsAdmin()->post(route('admin.experts.store'), $this->payload(['photo_path' => '2026/09/portrait.webp']));
        $this->assertSame('2026/09/portrait.webp', Expert::firstOrFail()->photo_path);
        $this->assertSame('/uploads/2026/09/portrait.webp', Expert::firstOrFail()->photoUrl());

        $this->actingAsAdmin()->put(route('admin.experts.update', Expert::firstOrFail()), $this->payload(['photo_path' => '../../.env']));
        $this->assertNull(Expert::firstOrFail()->photo_path);
    }

    public function test_only_published_experts_are_in_the_published_scope_in_manual_order(): void
    {
        $b = Expert::create(['is_published' => true, 'position' => 2]);
        $hidden = Expert::create(['is_published' => false, 'position' => 1]);
        $a = Expert::create(['is_published' => true, 'position' => 1]);

        $this->assertSame([$a->id, $b->id], Expert::published()->ordered()->pluck('id')->all());
        $this->assertFalse(Expert::published()->pluck('id')->contains($hidden->id));
    }

    public function test_move_swaps_positions_with_the_neighbour_and_stops_at_the_edges(): void
    {
        $first = Expert::create(['position' => 1]);
        $second = Expert::create(['position' => 2]);
        $third = Expert::create(['position' => 3]);

        $this->actingAsAdmin()->post(route('admin.experts.move', $second), ['direction' => 'up'])
            ->assertRedirect(route('admin.experts.index'));

        $this->assertSame([$second->id, $first->id, $third->id], Expert::ordered()->pluck('id')->all());

        $this->actingAsAdmin()->post(route('admin.experts.move', $second), ['direction' => 'up']);
        $this->assertSame([$second->id, $first->id, $third->id], Expert::ordered()->pluck('id')->all());

        $this->actingAsAdmin()->post(route('admin.experts.move', $third), ['direction' => 'down']);
        $this->assertSame([$second->id, $first->id, $third->id], Expert::ordered()->pluck('id')->all());
    }

    public function test_expert_can_be_deleted_with_translations(): void
    {
        $expert = Expert::create(['position' => 1]);
        $expert->translations()->create(['locale' => 'ru', 'name' => 'A']);

        $this->actingAsAdmin()->delete(route('admin.experts.destroy', $expert))
            ->assertRedirect(route('admin.experts.index'));

        $this->assertSame(0, Expert::count());
        $this->assertDatabaseCount('expert_translations', 0);
    }
}

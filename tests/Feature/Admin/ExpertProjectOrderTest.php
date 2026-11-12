<?php

namespace Tests\Feature\Admin;

use App\Models\Expert;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Ручной порядок экспертов и проектов: перетаскивание строк в админке, как у видео и альбомов. */
class ExpertProjectOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_experts_list_has_the_drag_handles_and_the_order_is_saved(): void
    {
        $a = Expert::create(['position' => 1]);
        $b = Expert::create(['position' => 2]);
        $c = Expert::create(['position' => 3]);

        $html = $this->actingAsAdmin()->get(route('admin.experts.index'))->assertOk()->getContent();
        $this->assertStringContainsString('x-data="rowReorder(', $html);
        $this->assertStringContainsString(route('admin.experts.reorder'), str_replace('\/', '/', $html));
        $this->assertSame(3, substr_count($html, 'data-move="up"'));

        $this->actingAsAdmin()->postJson(route('admin.experts.reorder'), ['ids' => [$c->id, $a->id]])->assertOk();

        $this->assertSame([$c->id, $a->id, $b->id], Expert::ordered()->pluck('id')->all());
        $this->assertSame([1, 2, 3], Expert::ordered()->pluck('position')->all());
    }

    public function test_projects_list_has_the_drag_handles_and_the_order_is_saved(): void
    {
        $a = Project::create(['slug' => 'a', 'position' => 1]);
        $b = Project::create(['slug' => 'b', 'position' => 2]);
        $c = Project::create(['slug' => 'c', 'position' => 3]);

        $html = $this->actingAsAdmin()->get(route('admin.projects.index'))->assertOk()->getContent();
        $this->assertStringContainsString('x-data="rowReorder(', $html);
        $this->assertStringContainsString(route('admin.projects.reorder'), str_replace('\/', '/', $html));
        $this->assertSame(3, substr_count($html, 'data-move="up"'));

        $this->actingAsAdmin()->postJson(route('admin.projects.reorder'), ['ids' => [$b->id, $c->id, $a->id]])->assertOk();

        $this->assertSame([$b->id, $c->id, $a->id], Project::orderBy('position')->pluck('id')->all());
    }

    public function test_the_requests_are_validated_and_closed_to_guests(): void
    {
        foreach (['admin.experts.reorder', 'admin.projects.reorder'] as $route) {
            $this->actingAsAdmin()->postJson(route($route), ['ids' => [999]])->assertStatus(422);
            $this->actingAsAdmin()->postJson(route($route), [])->assertStatus(422);
        }

        auth()->logout();
        $this->postJson(route('admin.experts.reorder'), ['ids' => [1]])->assertStatus(401);
        $this->postJson(route('admin.projects.reorder'), ['ids' => [1]])->assertStatus(401);
    }
}

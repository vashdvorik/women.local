<?php

namespace Tests\Feature;

use App\Models\SiteOpportunity;
use App\Models\Tag;
use App\Support\Locales;
use Database\Seeders\SiteOpportunitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** «Возможности» сайта, присланные заказчиком: заводятся сидером на трёх языках, опубликованными, с обложкой, тегом и сроком. */
class SiteOpportunitySeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('uploads');
    }

    public function test_it_creates_five_published_opportunities_with_covers_tags_and_deadlines(): void
    {
        $this->seed(SiteOpportunitySeeder::class);

        $this->assertSame(5, SiteOpportunity::count());
        $this->assertSame(5, SiteOpportunity::published()->count());

        foreach (SiteOpportunity::with('translations', 'tag')->get() as $opportunity) {
            Storage::disk('uploads')->assertExists($opportunity->cover_path);
            $this->assertNotNull($opportunity->tag_id, $opportunity->slug);

            foreach (Locales::ALL as $locale) {
                $row = $opportunity->translations->firstWhere('locale', $locale);
                $this->assertNotNull($row, "{$opportunity->slug} {$locale}");
                $this->assertNotSame('', trim((string) $row->title));
                $this->assertNotSame('', trim((string) $row->excerpt));
                $this->assertNotEmpty($row->content, "{$opportunity->slug} {$locale}");
            }

            // структура блоков одна на все языки
            $uids = fn (string $locale) => array_column($opportunity->translations->firstWhere('locale', $locale)->content, 'uid');
            $this->assertSame($uids('ru'), $uids('en'));
            $this->assertSame($uids('ru'), $uids('ro'));
        }

        // Сроки: у четырёх есть дата, у программы поддержки (без срока) нет
        $this->assertSame(4, SiteOpportunity::whereNotNull('deadline_at')->count());
        $this->assertSame('2026-10-30', SiteOpportunity::where('slug', 'like', 'startapy-%')->first()->deadline_at->toDateString());
        $this->assertNull(SiteOpportunity::where('slug', 'programma-podderzhki-zhenskogo-predprinimatelstva')->first()->deadline_at);

        // Четыре тега: «Гранты» общий у двух возможностей
        $this->assertSame(4, Tag::count());
    }

    public function test_it_is_idempotent_and_never_overwrites_edits(): void
    {
        $this->seed(SiteOpportunitySeeder::class);

        $opportunity = SiteOpportunity::where('slug', 'programma-podderzhki-zhenskogo-predprinimatelstva')->first();
        $opportunity->translations()->where('locale', 'ru')->update(['title' => 'Правка из админки']);

        $this->seed(SiteOpportunitySeeder::class);

        $this->assertSame(5, SiteOpportunity::count());
        $this->assertSame(4, Tag::count());
        $this->assertSame('Правка из админки', $opportunity->fresh()->translations->firstWhere('locale', 'ru')->title);
    }

    public function test_the_public_page_lists_them_newest_first_and_the_source_images_exist(): void
    {
        $this->seed(SiteOpportunitySeeder::class);

        $html = $this->get('/opportunities')->assertOk()->getContent();
        $this->assertStringContainsString('Программа поддержки женского предпринимательства', $html);
        $this->assertLessThan(
            strpos($html, 'Онлайн-курс по «зелёной» экономике'),
            strpos($html, 'Программа поддержки женского предпринимательства'),
            'новые выше'
        );

        foreach (['support-program', 'star-venture', 'schuman-traineeships', '3d-heritage', 'green-economy-course'] as $name) {
            $this->assertFileExists(public_path("themes/public/miro/images/opportunities/{$name}.webp"));
        }

        $this->get('/opportunities/stazhirovki-evropejskogo-parlamenta-programma-shumana')->assertOk()->assertSee('31 октября 2026 года.');
    }
}

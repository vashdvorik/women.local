<?php

namespace Tests\Feature;

use App\Support\Participants;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * «Участницы платформы» на главной — карусель по общему списку участниц (тот же, что в каталоге /members).
 * Требование к скорости: страница не тянет оригиналы фото — только миниатюры, и все лениво.
 */
class PublicParticipantsCarouselTest extends TestCase
{
    use RefreshDatabase;

    private const IMAGES = 'themes/public/miro/images';

    /** Разметка секции #participants целиком. */
    private function section(): string
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertSame(1, preg_match('#<section class="[^"]*" id="participants">(.*?)</section>#s', $html, $m), 'секции #participants нет на главной');

        return $m[1];
    }

    public function test_landing_shows_every_participant_from_the_shared_catalog_in_a_carousel(): void
    {
        $section = $this->section();

        $this->assertSame(count(Participants::all()), substr_count($section, 'class="miro-participant-card"'));
        $this->assertGreaterThan(24, substr_count($section, 'class="miro-participant-card"'), 'в карусели не только прежние 24 карточки');

        foreach (['data-participants-track', 'data-participants-prev', 'data-participants-next', 'data-participants-dots', 'aria-live="polite"'] as $part) {
            $this->assertStringContainsString($part, $section, $part);
        }

        // Кнопка «Все участницы» к каталогу осталась.
        $this->assertStringContainsString(route('members'), $section);

        // Скрипт карусели подключён и грузится отложенно.
        $this->get('/')->assertSee('js/participants.js', false);
        $this->assertFileExists(public_path('themes/public/miro/js/participants.js'));
    }

    public function test_landing_and_catalog_read_the_same_list(): void
    {
        $catalog = $this->get(route('members'))->assertOk()->getContent();
        $this->assertSame(1, preg_match('#id="miro-participants-remaining">(.*?)</script>#s', $catalog, $m));

        // Каталог: 24 карточки в разметке + остальные в JSON; вместе — весь общий список.
        $this->assertSame(count(Participants::all()) - 24, count(json_decode($m[1], true)));

        $section = $this->section();
        foreach (array_slice(Participants::all(), 0, 3) as $participant) {
            $this->assertStringContainsString($participant['name']['en'], $section);
        }
    }

    public function test_participants_with_photos_come_first_and_those_with_initials_last(): void
    {
        $section = $this->section();

        $lastPhoto = strrpos($section, '<img class="miro-participant-card__avatar"');
        $firstInitials = strpos($section, 'miro-participant-card__avatar--placeholder');

        $this->assertNotFalse($lastPhoto);
        $this->assertNotFalse($firstInitials, 'у части участниц нет фото — для них рисуются инициалы');
        $this->assertGreaterThan($lastPhoto, $firstInitials);

        $carousel = Participants::forCarousel();
        $this->assertCount(count(Participants::all()), $carousel);
        $this->assertNotNull($carousel[0]['photo']);
        $this->assertNull($carousel[array_key_last($carousel)]['photo']);
    }

    public function test_carousel_loads_small_lazy_thumbnails_and_never_the_original_photos(): void
    {
        $section = $this->section();

        preg_match_all('#<img class="miro-participant-card__avatar"[^>]*>#', $section, $images);
        $this->assertNotEmpty($images[0]);

        foreach ($images[0] as $tag) {
            // Миниатюра WebP, а не оригинал: аватар рисуется 56 px, оригинал ~225×300.
            $this->assertMatchesRegularExpression('#src="[^"]*/participants/thumb/[a-z0-9-]+\.webp"#', $tag, $tag);
            // Все фото ленивые: секция далеко от начала страницы. Размеры заданы — вёрстка не прыгает.
            foreach (['loading="lazy"', 'decoding="async"', 'width="56"', 'height="56"', 'alt=""'] as $attr) {
                $this->assertStringContainsString($attr, $tag, $tag);
            }
        }

        $this->assertStringNotContainsString('.jpg', $section, 'оригиналы фото на главную не попадают');
    }

    public function test_every_participant_photo_has_a_small_square_thumbnail(): void
    {
        $withPhoto = array_filter(Participants::all(), fn (array $p) => $p['photo'] !== null);
        $this->assertNotEmpty($withPhoto);

        foreach ($withPhoto as $participant) {
            $source = public_path(self::IMAGES.'/'.$participant['photo']);
            $thumb = public_path(self::IMAGES.'/'.Participants::thumb($participant['photo']));

            $this->assertFileExists($source, $participant['photo']);
            $this->assertFileExists($thumb, "нет миниатюры для {$participant['photo']} — php artisan participants:thumbnails");

            [$width, $height] = getimagesize($thumb);
            $this->assertSame([Participants::THUMB_SIZE, Participants::THUMB_SIZE], [$width, $height], $thumb);
            $this->assertLessThan(filesize($source), filesize($thumb), "миниатюра не легче оригинала: {$participant['photo']}");
            $this->assertLessThan(20 * 1024, filesize($thumb), "миниатюра тяжелее 20 КБ: {$participant['photo']}");
        }
    }

    public function test_every_participant_is_described_in_all_three_languages(): void
    {
        foreach (Participants::all() as $i => $participant) {
            foreach (['name', 'tag', 'summary'] as $field) {
                foreach (['ru', 'en', 'ro'] as $locale) {
                    $this->assertNotEmpty($participant[$field][$locale] ?? null, "участница #{$i}: нет {$field}.{$locale}");
                }
            }
        }
    }

    public function test_snap_points_match_the_page_size_at_every_breakpoint(): void
    {
        // Страница = --cols * --rows карточек; точка привязки — каждая N-я карточка. Если числа разойдутся,
        // свайп начнёт останавливаться посреди страницы, а стрелки — промахиваться.
        $css = (string) file_get_contents(public_path('themes/public/miro/css/landing.css'));

        preg_match_all('/--cols: (\d+); --rows: (\d+);/', $css, $sizes, PREG_SET_ORDER);
        preg_match_all('/:nth-child\((\d+)n \+ 1\) \{ scroll-snap-align: start;/', $css, $snaps);

        $pages = array_map(fn ($size) => (int) $size[1] * (int) $size[2], $sizes);

        $this->assertSame([16, 8, 4], $pages, 'десктоп 4×4, планшет 2×4, телефон 1×4');
        $this->assertSame($pages, array_map('intval', $snaps[1]));
    }
}

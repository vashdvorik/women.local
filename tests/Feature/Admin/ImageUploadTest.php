<?php

namespace Tests\Feature\Admin;

use App\Models\ImageSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_uploaded_image_becomes_a_webp_on_the_uploads_disk(): void
    {
        Storage::fake('uploads');

        $response = $this->actingAsAdmin()->post(route('admin.uploads.store'), [
            'image' => UploadedFile::fake()->image('photo.jpg', 3000, 2000),
            'slot' => 'cover',
        ]);

        $response->assertOk();
        $path = $response->json('path');

        $this->assertMatchesRegularExpression('#^\d{4}/\d{2}/[A-Za-z0-9]+\.webp$#', $path);
        Storage::disk('uploads')->assertExists($path);
    }

    public function test_user_crop_rectangle_is_applied(): void
    {
        Storage::fake('uploads');

        $response = $this->actingAsAdmin()->post(route('admin.uploads.store'), [
            'image' => UploadedFile::fake()->image('photo.jpg', 4000, 3000),
            'slot' => 'image', // 16:9
            'crop' => ['x' => 500, 'y' => 400, 'width' => 3200, 'height' => 1800],
        ]);

        $response->assertOk();
        Storage::disk('uploads')->assertExists($response->json('path'));
    }

    public function test_malformed_crop_is_rejected(): void
    {
        Storage::fake('uploads');

        $this->actingAsAdmin()->post(route('admin.uploads.store'), [
            'image' => UploadedFile::fake()->image('photo.jpg'),
            'slot' => 'image',
            'crop' => ['x' => 0, 'y' => 0, 'width' => 0, 'height' => 0],
        ])->assertSessionHasErrors('crop.width');
    }

    public function test_unknown_slot_is_rejected(): void
    {
        Storage::fake('uploads');

        $this->actingAsAdmin()->post(route('admin.uploads.store'), [
            'image' => UploadedFile::fake()->image('photo.jpg'),
            'slot' => 'banner',
        ])->assertSessionHasErrors('slot');
    }

    public function test_non_image_file_is_rejected(): void
    {
        Storage::fake('uploads');

        $this->actingAsAdmin()->post(route('admin.uploads.store'), [
            'image' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
            'slot' => 'cover',
        ])->assertSessionHasErrors('image');
    }

    public function test_guests_cannot_upload(): void
    {
        $this->post(route('admin.uploads.store'), [])->assertRedirect(route('login'));
    }

    // ---------------------------------------------------------------- фото целиком и перекадрирование

    /** Размеры файла на диске: [ширина, высота, тип]. @return array{int, int, int} */
    private function imageSize(string $disk, string $path): array
    {
        return array_slice(getimagesizefromstring(Storage::disk($disk)->get($path)), 0, 3);
    }

    /** PNG из двух половин: слева красная, справа синяя, чтобы по цвету кадра видеть, какая часть фото в нём. */
    private function twoHalves(int $width = 2000, int $height = 1000): UploadedFile
    {
        $image = imagecreatetruecolor($width, $height);
        imagefilledrectangle($image, 0, 0, intdiv($width, 2) - 1, $height, imagecolorallocate($image, 255, 0, 0));
        imagefilledrectangle($image, intdiv($width, 2), 0, $width, $height, imagecolorallocate($image, 0, 0, 255));

        ob_start();
        imagepng($image);

        return UploadedFile::fake()->createWithContent('halves.png', (string) ob_get_clean());
    }

    /** Цвет центра готового кадра: [красный, синий]. @return array{int, int} */
    private function centreColour(string $path): array
    {
        $image = imagecreatefromstring(Storage::disk('uploads')->get($path));
        $rgb = imagecolorat($image, intdiv(imagesx($image), 2), intdiv(imagesy($image), 2));

        return [($rgb >> 16) & 255, $rgb & 255];
    }

    public function test_the_whole_photo_is_kept_compressed_and_privately_while_the_site_gets_only_the_cropped_frame(): void
    {
        Storage::fake('uploads');

        $response = $this->actingAsAdmin()->post(route('admin.uploads.store'), [
            'image' => UploadedFile::fake()->image('photo.jpg', 4000, 3000),
            'slot' => 'image', // 16:9, 1280×720
            'crop' => ['x' => 500, 'y' => 400, 'width' => 3200, 'height' => 1800],
        ])->assertOk();

        $path = $response->json('path');
        $source = ImageSource::query()->where('path', $path)->firstOrFail();

        // Готовый кадр для сайта ровно по слоту.
        $this->assertSame([1280, 720, IMAGETYPE_WEBP], $this->imageSize('uploads', $path));

        // Сжатое фото целиком: не обрезано, длинная сторона по настройке (2400), WebP, а не файл на 5–8 МБ.
        $this->assertSame([2400, 1800, IMAGETYPE_WEBP], $this->imageSize('originals', $source->source_path));
        $this->assertSame([2400, 1800], [$source->source_width, $source->source_height]);

        // Рамка пересчитана в пиксели сжатого фото (коэффициент 0,6).
        $this->assertEqualsWithDelta(300, $source->crop_x, 1);
        $this->assertEqualsWithDelta(240, $source->crop_y, 1);
        $this->assertEqualsWithDelta(1920, $source->crop_width, 1);
        $this->assertEqualsWithDelta(1080, $source->crop_height, 1);
        $this->assertSame('image', $source->slot);

        // Фото целиком лежит на закрытом диске, а в публичной папке его нет.
        Storage::disk('uploads')->assertMissing($source->source_path);
        $this->assertNotSame($path, $source->source_path);
    }

    public function test_a_small_photo_is_not_enlarged_and_a_missing_crop_means_the_centre(): void
    {
        Storage::fake('uploads');

        $response = $this->actingAsAdmin()->post(route('admin.uploads.store'), [
            'image' => UploadedFile::fake()->image('small.jpg', 1000, 800),
            'slot' => 'image', // 16:9
        ])->assertOk();

        $source = ImageSource::query()->where('path', $response->json('path'))->firstOrFail();

        $this->assertSame([1000, 800], [$source->source_width, $source->source_height]);
        // Самая большая рамка 16:9 по центру фото 1000×800: 1000×563 со смещением по высоте.
        $this->assertSame([0, 118, 1000, 563], [$source->crop_x, $source->crop_y, $source->crop_width, $source->crop_height]);
    }

    public function test_source_info_gives_the_cropper_everything_it_needs(): void
    {
        Storage::fake('uploads');

        $path = $this->actingAsAdmin()->post(route('admin.uploads.store'), [
            'image' => UploadedFile::fake()->image('photo.jpg', 2000, 1000),
            'slot' => 'event',
            'crop' => ['x' => 100, 'y' => 50, 'width' => 1600, 'height' => 900],
        ])->json('path');

        $source = ImageSource::query()->where('path', $path)->firstOrFail();

        $this->actingAsAdmin()->getJson(route('admin.uploads.source-info', ['path' => $path]))
            ->assertOk()
            ->assertJson([
                'path' => $path,
                'source_url' => route('admin.uploads.source', $source),
                'width' => 2000,
                'height' => 1000,
                'crop' => ['x' => 100, 'y' => 50, 'width' => 1600, 'height' => 900],
                'slot' => 'event',
                'ratio' => [1280, 720],
            ]);
    }

    public function test_a_picture_uploaded_before_this_feature_cannot_be_recropped_and_the_admin_is_told_so(): void
    {
        $this->actingAsAdmin()->getJson(route('admin.uploads.source-info', ['path' => '2026/09/old-picture.webp']))
            ->assertNotFound()
            ->assertJsonPath('message', 'У этой картинки нет сохранённого фото целиком: перекадрировать её нельзя. Загрузите её заново.');

        // Мусор и чужой формат пути не доходят до базы и тоже дают «нет».
        foreach (['', '../../.env', 'https://evil.test/a.webp', '2026/09/photo.png'] as $path) {
            $this->actingAsAdmin()->getJson(route('admin.uploads.source-info', ['path' => $path]))->assertNotFound();
        }
    }

    public function test_the_whole_photo_is_served_to_the_admin_only(): void
    {
        Storage::fake('uploads');

        $path = $this->actingAsAdmin()->post(route('admin.uploads.store'), [
            'image' => UploadedFile::fake()->image('photo.jpg', 2000, 1000),
            'slot' => 'cover',
        ])->json('path');

        $source = ImageSource::query()->where('path', $path)->firstOrFail();

        $response = $this->actingAsAdmin()->get(route('admin.uploads.source', $source))->assertOk();
        $this->assertSame('image/webp', $response->headers->get('Content-Type'));
        $this->assertSame([2000, 1000, IMAGETYPE_WEBP], array_slice(getimagesizefromstring($response->streamedContent()), 0, 3));

        // Гость и участница кабинета фото целиком не получают.
        auth()->logout();
        $this->get(route('admin.uploads.source', $source))->assertRedirect(route('login'));
        $this->getJson(route('admin.uploads.source-info', ['path' => $path]))->assertUnauthorized();

        // Файл пропал с диска: честный 404, а не ошибка сервера.
        Storage::disk('originals')->delete($source->source_path);
        $this->actingAsAdmin()->get(route('admin.uploads.source', $source))->assertNotFound();
    }

    public function test_recrop_cuts_a_new_frame_from_the_stored_photo_and_keeps_the_old_one_working(): void
    {
        Storage::fake('uploads');

        // Кадр из левой (красной) половины.
        $first = $this->actingAsAdmin()->post(route('admin.uploads.store'), [
            'image' => $this->twoHalves(),
            'slot' => 'avatar', // 1:1, 400×400
            'crop' => ['x' => 0, 'y' => 0, 'width' => 1000, 'height' => 1000],
        ])->json('path');

        $this->assertSame([400, 400, IMAGETYPE_WEBP], $this->imageSize('uploads', $first));
        [$red, $blue] = $this->centreColour($first);
        $this->assertGreaterThan(200, $red, 'в первом кадре красная половина');
        $this->assertLessThan(60, $blue);

        $firstRow = ImageSource::query()->where('path', $first)->firstOrFail();

        // Меняем рамку на правую (синюю) половину: файл заново не загружается.
        $response = $this->actingAsAdmin()->postJson(route('admin.uploads.recrop'), [
            'path' => $first,
            'crop' => ['x' => 1000, 'y' => 0, 'width' => 1000, 'height' => 1000],
        ])->assertOk();

        $second = $response->json('path');
        $this->assertNotSame($first, $second);
        $this->assertSame('/uploads/'.$second, $response->json('url'));
        $this->assertSame([400, 400, IMAGETYPE_WEBP], $this->imageSize('uploads', $second));
        [$red, $blue] = $this->centreColour($second);
        $this->assertLessThan(60, $red, 'во втором кадре синяя половина');
        $this->assertGreaterThan(200, $blue);

        // Новая запись берёт то же сжатое фото; старые кадр и запись целы (форма могла не сохраниться).
        $secondRow = ImageSource::query()->where('path', $second)->firstOrFail();
        $this->assertSame($firstRow->source_path, $secondRow->source_path);
        $this->assertSame([1000, 0, 1000, 1000], [$secondRow->crop_x, $secondRow->crop_y, $secondRow->crop_width, $secondRow->crop_height]);
        Storage::disk('uploads')->assertExists($first);
        $this->assertTrue($firstRow->fresh()->is($firstRow));
        $this->assertSame(2, ImageSource::count());
        $this->assertSame(1, count(Storage::disk('originals')->allFiles()), 'фото целиком одно на оба кадра');

        // И новую рамку можно менять дальше.
        $this->actingAsAdmin()->postJson(route('admin.uploads.recrop'), [
            'path' => $second,
            'crop' => ['x' => 500, 'y' => 0, 'width' => 1000, 'height' => 1000],
        ])->assertOk();
    }

    public function test_every_admin_form_with_pictures_has_the_change_crop_button_and_shows_script_messages(): void
    {
        // Кнопка «Изменить кадр» есть у каждой ячейки картинки; сообщения скриптов (например, «перекадрировать нельзя»)
        // выводятся общим блоком уведомлений, а не пропадают.
        $forms = [
            route('admin.events.create') => 7,     // обложка и блоки текста на трёх языках
            route('admin.posts.create') => 7,
            route('admin.albums.create') => 3,     // обложка, картинка и ячейка галереи
            route('admin.videos.create') => 1,
            route('admin.experts.create') => 1,
            route('admin.projects.create') => 1,
        ];

        foreach ($forms as $url => $buttons) {
            $html = $this->actingAsAdmin()->get($url)->assertOk()->getContent();

            $this->assertSame($buttons, substr_count($html, 'title="Изменить кадр"'), $url);
            $this->assertStringContainsString('x-on:flash.window', $html, $url);
            $this->assertStringContainsString('data-script-toast', $html, $url);
        }
    }

    public function test_recrop_rejects_a_frame_outside_the_photo_an_unknown_picture_and_guests(): void
    {
        Storage::fake('uploads');

        $path = $this->actingAsAdmin()->post(route('admin.uploads.store'), [
            'image' => UploadedFile::fake()->image('photo.jpg', 2000, 1000),
            'slot' => 'cover',
        ])->json('path');

        $count = ImageSource::count();

        $this->actingAsAdmin()->postJson(route('admin.uploads.recrop'), [
            'path' => $path,
            'crop' => ['x' => 1500, 'y' => 0, 'width' => 1000, 'height' => 500],
        ])->assertStatus(422)->assertJsonPath('message', 'Рамка выходит за границы фото.');

        $this->actingAsAdmin()->postJson(route('admin.uploads.recrop'), [
            'path' => $path,
            'crop' => ['x' => 0, 'y' => 0, 'width' => 0, 'height' => 0],
        ])->assertStatus(422);

        $this->actingAsAdmin()->postJson(route('admin.uploads.recrop'), [
            'path' => '2026/09/never-uploaded.webp',
            'crop' => ['x' => 0, 'y' => 0, 'width' => 100, 'height' => 100],
        ])->assertNotFound();

        $this->assertSame($count, ImageSource::count(), 'отказ ничего не создаёт');

        auth()->logout();
        $this->post(route('admin.uploads.recrop'), ['path' => $path])->assertRedirect(route('login'));
    }
}

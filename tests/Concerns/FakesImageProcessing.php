<?php

namespace Tests\Concerns;

use App\Actions\StoreUploadedImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Лёгкая замена конвейера картинок для тестов сидеров. Настоящий StoreUploadedImage (кадр → WebP) тратит около 0,3 с
 * на снимок, а сидеры новостей грузят сотню фото: полный прогон занимал бы полминуты в каждом тесте. Заглушка кладёт
 * на подменный диск крошечный файл и запоминает, что у неё просили (слот, прямоугольник кадра, исходник).
 * Настоящую обработку проверяет отдельный тест на одной новости.
 */
trait FakesImageProcessing
{
    /** @var list<array{slot: string, crop: ?array<string, int>, source: string}> */
    protected array $imageCalls = [];

    protected function fakeImageProcessing(): void
    {
        $calls = &$this->imageCalls;

        $this->app->instance(StoreUploadedImage::class, new class($calls) extends StoreUploadedImage
        {
            public function __construct(private array &$calls) {}

            public function handle(UploadedFile $file, string $slot, ?array $crop = null): string
            {
                $this->calls[] = ['slot' => $slot, 'crop' => $crop, 'source' => $file->getRealPath()];

                $path = now()->format('Y/m').'/'.Str::lower(Str::random(12)).'.webp';
                Storage::disk('uploads')->put($path, 'fake-webp');

                return $path;
            }
        });
    }
}

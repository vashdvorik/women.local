<?php

namespace Tests\Feature\Admin;

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
}

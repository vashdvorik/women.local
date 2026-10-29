<?php

declare(strict_types=1);

namespace Tests\Feature\Account;

use App\Jobs\ComputeUserEmbedding;
use App\Models\BotUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Фото профиля в кабинете: участница меняет его сама (раньше оно бралось только из Telegram при регистрации). */
class ProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Bus::fake([ComputeUserEmbedding::class]);
    }

    /** @return array<string, mixed> */
    private function sessionOf(BotUser $user): array
    {
        return $this->participantSession($user);
    }

    /** @return array<string, string> поля формы, которые нужны всегда */
    private function fields(BotUser $user): array
    {
        return ['full_name' => $user->full_name ?: 'Анна Петрова', 'description' => 'Описание', 'expectation' => 'Запрос'];
    }

    public function test_the_edit_form_can_send_a_file_and_shows_the_current_photo(): void
    {
        $user = BotUser::factory()->active()->create(['avatar_path' => 'avatars/old.jpg']);
        Storage::disk('public')->put('avatars/old.jpg', 'x');

        $html = $this->withSession($this->sessionOf($user))->get(route('account.profile.edit'))->assertOk()->getContent();

        $this->assertStringContainsString('enctype="multipart/form-data"', $html);
        $this->assertStringContainsString('name="photo"', $html);
        $this->assertStringContainsString('name="remove_photo"', $html, 'есть фото — есть и «Убрать фото»');
        $this->assertStringContainsString('avatars/old.jpg', $html);
    }

    public function test_a_new_photo_is_cut_to_a_square_and_replaces_the_old_one(): void
    {
        $user = BotUser::factory()->active()->create(['avatar_path' => 'avatars/telegram.jpg']);
        Storage::disk('public')->put('avatars/telegram.jpg', 'old');

        $this->withSession($this->sessionOf($user))
            ->post(route('account.profile.update'), $this->fields($user) + ['photo' => UploadedFile::fake()->image('me.jpg', 1200, 800)])
            ->assertRedirect(route('account.profile'))
            ->assertSessionHasNoErrors();

        $path = $user->fresh()->avatar_path;

        $this->assertNotSame('avatars/telegram.jpg', $path, 'новое имя файла: у старого адреса мог остаться кеш браузера');
        $this->assertStringStartsWith('avatars/'.$user->telegram_id.'-', $path);
        Storage::disk('public')->assertExists($path);
        Storage::disk('public')->assertMissing('avatars/telegram.jpg');

        [$width, $height] = getimagesizefromstring(Storage::disk('public')->get($path));
        $this->assertSame([512, 512], [$width, $height]);
    }

    public function test_the_photo_can_be_removed(): void
    {
        $user = BotUser::factory()->active()->create(['avatar_path' => 'avatars/me.jpg']);
        Storage::disk('public')->put('avatars/me.jpg', 'x');

        $this->withSession($this->sessionOf($user))
            ->post(route('account.profile.update'), $this->fields($user) + ['remove_photo' => '1'])
            ->assertRedirect(route('account.profile'));

        $this->assertNull($user->fresh()->avatar_path);
        Storage::disk('public')->assertMissing('avatars/me.jpg');
    }

    public function test_saving_the_profile_without_a_file_keeps_the_photo(): void
    {
        $user = BotUser::factory()->active()->create(['avatar_path' => 'avatars/me.jpg']);
        Storage::disk('public')->put('avatars/me.jpg', 'x');

        $this->withSession($this->sessionOf($user))
            ->post(route('account.profile.update'), $this->fields($user))
            ->assertRedirect(route('account.profile'));

        $this->assertSame('avatars/me.jpg', $user->fresh()->avatar_path);
        Storage::disk('public')->assertExists('avatars/me.jpg');
    }

    public function test_only_images_up_to_eight_megabytes_are_accepted(): void
    {
        $user = BotUser::factory()->active()->create(['avatar_path' => null]);

        $notAnImage = UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf');
        $tooBig = UploadedFile::fake()->image('big.jpg', 800, 800)->size(9000);

        foreach ([$notAnImage, $tooBig] as $file) {
            $this->withSession($this->sessionOf($user))
                ->from(route('account.profile.edit'))
                ->post(route('account.profile.update'), $this->fields($user) + ['photo' => $file])
                ->assertSessionHasErrors('photo');
        }

        $this->assertNull($user->fresh()->avatar_path);
    }

    public function test_the_profile_page_shows_the_two_fields_one_below_the_other(): void
    {
        // Раньше поля стояли в две колонки: длинный текст растягивал левую карточку, правая оставалась наполовину пустой.
        $css = (string) file_get_contents(public_path('themes/account/fortuntwo/css/theme.css'));

        $this->assertMatchesRegularExpression('/\.fortun-profile-fields\s*\{[^}]*grid-template-columns:\s*minmax\(0,\s*1fr\);/', $css);
        $this->assertDoesNotMatchRegularExpression('/\.fortun-profile-fields\s*\{[^}]*repeat\(2/', $css);
    }
}

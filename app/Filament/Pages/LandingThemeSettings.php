<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class LandingThemeSettings extends Page
{
    protected string $view = 'filament.pages.landing-theme-settings';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-swatch';

    protected static ?string $navigationLabel = 'Тема лендинга';

    protected static ?string $title = 'Тема лендинга';

    protected static ?int $navigationSort = 3;

    public string $theme = 'classic';

    public function mount(): void
    {
        $this->theme = SiteSetting::landingTheme();
    }

    public function save(): void
    {
        SiteSetting::setLandingTheme($this->theme);

        Notification::make()
            ->title('Тема лендинга сохранена')
            ->body('Изменение уже применяется на публичной странице.')
            ->success()
            ->send();
    }

    /**
     * @return array<string, string>
     */
    public function themes(): array
    {
        return SiteSetting::LANDING_THEMES;
    }
}

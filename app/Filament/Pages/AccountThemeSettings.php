<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class AccountThemeSettings extends Page
{
    protected string $view = 'filament.pages.account-theme-settings';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-computer-desktop';

    protected static ?string $navigationLabel = 'Тема кабинета участницы';

    protected static ?string $title = 'Тема кабинета участницы';

    protected static ?int $navigationSort = 4;

    public string $theme = 'classic';

    public function mount(): void
    {
        $this->theme = SiteSetting::accountTheme();
    }

    public function save(): void
    {
        SiteSetting::setAccountTheme($this->theme);

        Notification::make()
            ->title('Тема кабинета сохранена')
            ->body('Изменение применяется к кабинету участницы и не меняет публичный лендинг.')
            ->success()
            ->send();
    }

    /**
     * @return array<string, string>
     */
    public function themes(): array
    {
        return SiteSetting::ACCOUNT_THEMES;
    }
}

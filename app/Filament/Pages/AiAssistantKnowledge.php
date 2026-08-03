<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class AiAssistantKnowledge extends Page
{
    protected string $view = 'filament.pages.ai-assistant-knowledge';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'База знаний AI-помощника';
    protected static ?string $title = 'База знаний AI-помощника';
    protected static \UnitEnum|string|null $navigationGroup = 'Интеграции';
    protected static ?int $navigationSort = 6;

    public string $rules = '';
    public string $ru = '';
    public string $en = '';
    public string $ro = '';

    public function mount(): void
    {
        $knowledge = SiteSetting::aiAssistantKnowledge();
        $this->rules = $knowledge['rules'];
        $this->ru = $knowledge['ru'];
        $this->en = $knowledge['en'];
        $this->ro = $knowledge['ro'];
    }

    public function save(): void
    {
        $this->validate([
            'rules' => ['nullable', 'string', 'max:20000'],
            'ru' => ['nullable', 'string', 'max:30000'],
            'en' => ['nullable', 'string', 'max:30000'],
            'ro' => ['nullable', 'string', 'max:30000'],
        ]);

        SiteSetting::setAiAssistantKnowledge([
            'rules' => $this->rules,
            'ru' => $this->ru,
            'en' => $this->en,
            'ro' => $this->ro,
        ]);

        Notification::make()->title('База знаний сохранена')->success()->send();
    }
}

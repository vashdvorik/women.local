<?php

declare(strict_types=1);

namespace App\Filament\Resources\BotUserResource\Pages;

use App\Filament\Resources\BotUserResource;
use Filament\Resources\Pages\ListRecords;

class ListBotUsers extends ListRecords
{
    protected static string $resource = BotUserResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}

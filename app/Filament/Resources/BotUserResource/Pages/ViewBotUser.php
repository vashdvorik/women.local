<?php

declare(strict_types=1);

namespace App\Filament\Resources\BotUserResource\Pages;

use App\Filament\Resources\BotUserResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBotUser extends ViewRecord
{
    protected static string $resource = BotUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

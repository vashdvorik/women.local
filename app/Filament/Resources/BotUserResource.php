<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\BotUserResource\Pages;
use App\Models\BotUser;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;
use App\Telegram\TelegramKeyboards;
use Illuminate\Support\Facades\Log;
use Nutgram\Laravel\Facades\Telegram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardRemove;

class BotUserResource extends Resource
{
    protected static ?string $model = BotUser::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Заявки';

    protected static ?string $modelLabel = 'Заявка';

    protected static ?string $pluralModelLabel = 'Заявки';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Telegram')->schema([
                TextInput::make('telegram_id')
                    ->label('Telegram ID')
                    ->disabled(),
                TextInput::make('telegram_username')
                    ->label('Username')
                    ->disabled(),
            ])->columns(2),

            Section::make('Анкета')->schema([
                TextInput::make('full_name')
                    ->label('Имя и фамилия'),
                Textarea::make('description')
                    ->label('О себе')
                    ->rows(3),
                Textarea::make('expectation')
                    ->label('Ожидания и польза')
                    ->rows(3),
            ]),

            Section::make('Статус')->schema([
                Select::make('status')
                    ->label('Статус')
                    ->options([
                        BotUser::STATUS_PENDING  => 'Ожидает',
                        BotUser::STATUS_APPROVED => 'Одобрен',
                        BotUser::STATUS_REJECTED => 'Отклонён',
                    ]),
                DateTimePicker::make('approved_at')
                    ->label('Дата одобрения')
                    ->disabled(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Имя')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('telegram_username')
                    ->label('Username')
                    ->formatStateUsing(fn (?string $state): string => $state ? "@{$state}" : '—')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Статус')
                    ->colors([
                        'warning' => BotUser::STATUS_PENDING,
                        'success' => BotUser::STATUS_APPROVED,
                        'danger'  => BotUser::STATUS_REJECTED,
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        BotUser::STATUS_PENDING  => 'Ожидает',
                        BotUser::STATUS_APPROVED => 'Одобрен',
                        BotUser::STATUS_REJECTED => 'Отклонён',
                        default                  => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Подал заявку')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        BotUser::STATUS_PENDING  => 'Ожидает',
                        BotUser::STATUS_APPROVED => 'Одобрен',
                        BotUser::STATUS_REJECTED => 'Отклонён',
                    ]),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Одобрить')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (BotUser $record): bool => ! $record->isApproved())
                    ->requiresConfirmation()
                    ->action(function (BotUser $record): void {
                        $record->update([
                            'status'      => BotUser::STATUS_APPROVED,
                            'approved_at' => now(),
                        ]);

                        self::sendApprovalMessage($record);

                        Notification::make()
                            ->title('Заявка одобрена')
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Отклонить')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (BotUser $record): bool => ! $record->isRejected())
                    ->requiresConfirmation()
                    ->action(function (BotUser $record): void {
                        $wasApproved = $record->isApproved();

                        $record->update(['status' => BotUser::STATUS_REJECTED]);

                        if ($wasApproved) {
                            self::sendAccessRevokedMessage($record);
                        } else {
                            self::sendRejectionMessage($record);
                        }

                        Notification::make()
                            ->title('Заявка отклонена')
                            ->warning()
                            ->send();
                    }),

                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBotUsers::route('/'),
            'view'  => Pages\ViewBotUser::route('/{record}'),
            'edit'  => Pages\EditBotUser::route('/{record}/edit'),
        ];
    }

    private static function sendApprovalMessage(BotUser $record): void
    {
        $firstName = explode(' ', (string) $record->full_name)[0];

        $inlineKeyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('С чего начать? →', callback_data: 'start_guide')
            );

        $mainMenu = TelegramKeyboards::mainMenu();

        try {
            Telegram::sendMessage(
                chat_id: $record->telegram_id,
                text: "🎉 {$firstName}, добро пожаловать в Инспайр!\n\nТы принят в сообщество активных молодых людей.\n\nЭто значит, что у тебя есть доступ:\n\n✅ Полный доступ в закрытый чат сообщества\n✅ Доступ к закрытым обучающим материалам академии\n✅ AI-нетворкинг — когда заполнишь профиль.",
                reply_markup: $mainMenu,
            );

            Telegram::sendMessage(
                chat_id: $record->telegram_id,
                text: "С чего начать?",
                reply_markup: $inlineKeyboard,
            );
        } catch (\Throwable $e) {
            Log::error('Telegram: не удалось отправить сообщение об одобрении', [
                'telegram_id' => $record->telegram_id,
                'error'       => $e->getMessage(),
            ]);
        }
    }

    private static function sendRejectionMessage(BotUser $record): void
    {
        $firstName = explode(' ', (string) $record->full_name)[0];

        try {
            Telegram::sendMessage(
                chat_id: $record->telegram_id,
                text: "😔 {$firstName}, к сожалению твоя заявка не была одобрена.\n\nЕсли у тебя есть вопросы или ты хочешь узнать причину — напиши напрямую: @lesnichenkoP",
            );
        } catch (\Throwable $e) {
            Log::error('Telegram: не удалось отправить сообщение об отклонении', [
                'telegram_id' => $record->telegram_id,
                'error'       => $e->getMessage(),
            ]);
        }
    }

    private static function sendAccessRevokedMessage(BotUser $record): void
    {
        try {
            Telegram::sendMessage(
                chat_id: $record->telegram_id,
                text: "🔒 Ваш доступ был закрыт.\n\nЕсли у тебя есть вопросы или ты хочешь узнать причину — напиши напрямую: @lesnichenkoP",
                reply_markup: ReplyKeyboardRemove::make(remove_keyboard: true),
            );
        } catch (\Throwable $e) {
            Log::error('Telegram: не удалось отправить сообщение об отзыве доступа', [
                'telegram_id' => $record->telegram_id,
                'error'       => $e->getMessage(),
            ]);
        }
    }
}
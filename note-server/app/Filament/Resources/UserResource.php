<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Account Info')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                    ]),

                Forms\Components\Section::make('Security')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->maxLength(255)
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context) => $context === 'create')
                            ->helperText(fn(string $context) => $context === 'edit' ? 'Leave blank to keep current password.' : null),
                    ]),

                Forms\Components\Section::make('Access')
                    ->schema([
                        Forms\Components\Toggle::make('is_banned')
                            ->label('Banned')
                            ->helperText('Banned users cannot authenticate against the API.')
                            ->onColor('danger')
                            ->offColor('success'),
                    ])
                    ->visibleOn(['create', 'edit']),

                Forms\Components\Section::make('Verification')
                    ->schema([
                        Forms\Components\Toggle::make('is_verified')
                            ->label('Email verified')
                            ->default(fn($record) => $record?->email_verified_at !== null)
                            ->dehydrated(false)
                            ->afterStateHydrated(function (Forms\Components\Toggle $component, $record) {
                                $component->state($record?->email_verified_at !== null);
                            })
                            ->live()
                            ->afterStateUpdated(function (bool $state, $record) {
                                if (! $record) {
                                    return;
                                }

                                $record->email_verified_at = $state ? now() : null;
                                $record->save();
                            })
                            ->visibleOn('edit'),

                        Forms\Components\Placeholder::make('created_at')
                            ->label('Created At')
                            ->content(fn($record) => $record?->created_at?->toDateTimeString())
                            ->visibleOn('edit'),

                        Forms\Components\Placeholder::make('pages_count')
                            ->label('Pages')
                            ->content(fn($record) => $record ? $record->pages()->withoutGlobalScopes()->count() : 0)
                            ->visibleOn('edit'),

                        Forms\Components\Placeholder::make('favorites_count')
                            ->label('Favorites')
                            ->content(fn($record) => $record ? $record->favoritePages()->count() : 0)
                            ->visibleOn('edit'),
                    ])
                    ->visibleOn('edit'),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Verified')
                    ->boolean()
                    ->getStateUsing(fn(User $record) => $record->email_verified_at !== null),
                Tables\Columns\IconColumn::make('is_banned')
                    ->label('Banned')
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('success'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
                TextColumn::make('tokens')
                    ->label('Tokens')
                    ->getStateUsing(fn(User $record) => implode(', ', $record->tokens->pluck('name')->toArray())),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('email_verified_at')
                    ->label('Email verified')
                    ->nullable(),
                Tables\Filters\TernaryFilter::make('is_banned')
                    ->label('Banned'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('resendVerification')
                    ->label('Resend verification')
                    ->icon('heroicon-o-envelope')
                    ->color('gray')
                    ->visible(fn(User $record) => $record->email_verified_at === null && (Auth::user()?->can('users.verify') ?? false))
                    ->action(function (User $record) {
                        $record->sendEmailVerificationNotification();

                        activity()
                            ->causedBy(Auth::user())
                            ->performedOn($record)
                            ->log('Resent verification email');

                        Notification::make()
                            ->title('Verification email sent')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('revokeTokens')
                    ->label('Revoke tokens')
                    ->icon('heroicon-o-shield-exclamation')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn(User $record) => $record->tokens->isNotEmpty() && (Auth::user()?->can('users.revoke-tokens') ?? false))
                    ->action(function (User $record) {
                        $record->tokens->each->delete();

                        activity()
                            ->causedBy(Auth::user())
                            ->performedOn($record)
                            ->log('Revoked all tokens');

                        Notification::make()
                            ->title('All tokens revoked')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('toggleBan')
                    ->label(fn(User $record) => $record->is_banned ? 'Unban' : 'Ban')
                    ->icon(fn(User $record) => $record->is_banned ? 'heroicon-o-lock-open' : 'heroicon-o-lock-closed')
                    ->color(fn(User $record) => $record->is_banned ? 'success' : 'danger')
                    ->requiresConfirmation()
                    ->visible(fn() => Auth::user()?->can('users.ban') ?? false)
                    ->action(function (User $record) {
                        $record->update(['is_banned' => ! $record->is_banned]);

                        activity()
                            ->causedBy(Auth::user())
                            ->performedOn($record)
                            ->log($record->is_banned ? 'Banned user' : 'Unbanned user');

                        Notification::make()
                            ->title($record->is_banned ? 'User banned' : 'User unbanned')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->authorize(fn() => Auth::user()?->can('users.delete') ?? false),

                Tables\Actions\BulkAction::make('verifyEmail')
                    ->label('Mark as verified')
                    ->icon('heroicon-o-check-badge')
                    ->visible(fn() => Auth::user()?->can('users.verify') ?? false)
                    ->action(fn($records) => $records->each(function (User $record) {
                        $record->update(['email_verified_at' => now()]);
                        activity()->causedBy(Auth::user())->performedOn($record)->log('Marked as verified (bulk)');
                    }))
                    ->deselectRecordsAfterCompletion(),

                Tables\Actions\BulkAction::make('revokeTokens')
                    ->label('Revoke tokens')
                    ->icon('heroicon-o-shield-exclamation')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn() => Auth::user()?->can('users.revoke-tokens') ?? false)
                    ->action(fn($records) => $records->each(function (User $record) {
                        $record->tokens->each->delete();
                        activity()->causedBy(Auth::user())->performedOn($record)->log('Revoked all tokens (bulk)');
                    }))
                    ->deselectRecordsAfterCompletion(),

                Tables\Actions\BulkAction::make('ban')
                    ->label('Ban')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn() => Auth::user()?->can('users.ban') ?? false)
                    ->action(fn($records) => $records->each(function (User $record) {
                        $record->update(['is_banned' => true]);
                        activity()->causedBy(Auth::user())->performedOn($record)->log('Banned user (bulk)');
                    }))
                    ->deselectRecordsAfterCompletion(),

                Tables\Actions\BulkAction::make('unban')
                    ->label('Unban')
                    ->icon('heroicon-o-lock-open')
                    ->color('success')
                    ->visible(fn() => Auth::user()?->can('users.ban') ?? false)
                    ->action(fn($records) => $records->each(function (User $record) {
                        $record->update(['is_banned' => false]);
                        activity()->causedBy(Auth::user())->performedOn($record)->log('Unbanned user (bulk)');
                    }))
                    ->deselectRecordsAfterCompletion(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PagesRelationManager::class,
            RelationManagers\FavoritePagesRelationManager::class,
            RelationManagers\ActivityLogRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}

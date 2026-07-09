<?php

namespace App\Filament\Widgets;

use App\Models\Page;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->icon('heroicon-o-users'),

            Stat::make('Verified', User::whereNotNull('email_verified_at')->count())
                ->icon('heroicon-o-check-badge')
                ->color('success'),

            Stat::make('Unverified', User::whereNull('email_verified_at')->count())
                ->icon('heroicon-o-exclamation-circle')
                ->color('warning'),

            Stat::make('New This Week', User::where('created_at', '>=', now()->subWeek())->count())
                ->icon('heroicon-o-arrow-trending-up'),

            Stat::make('New This Month', User::where('created_at', '>=', now()->subMonth())->count())
                ->icon('heroicon-o-calendar'),

            Stat::make('Total Pages', Page::withoutGlobalScopes()->count())
                ->icon('heroicon-o-document-text'),
        ];
    }
}

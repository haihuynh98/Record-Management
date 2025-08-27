<?php

namespace App\Filament\Widgets;

use App\Models\Profile;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StatsOverview extends BaseWidget
{
    public static function canView(): bool
    {
        return auth()->user()->can('view_dashboard_charts');
    }

    protected function getStats(): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $thisYear = Carbon::now()->startOfYear();

        return [
            Stat::make('Tổng hồ sơ hôm nay', Profile::whereDate('created_at', $today)->count())
                ->description('Hồ sơ được tạo trong ngày')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 17]),

            Stat::make('Tổng hồ sơ tháng này', Profile::where('created_at', '>=', $thisMonth)->count())
                ->description('Hồ sơ được tạo trong tháng')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info')
                ->chart([17, 16, 14, 15, 14, 13, 12]),

            Stat::make('Tổng hồ sơ năm nay', Profile::where('created_at', '>=', $thisYear)->count())
                ->description('Hồ sơ được tạo trong năm')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('warning')
                ->chart([15, 4, 10, 2, 12, 4, 12]),

            Stat::make('Người dùng hoạt động', User::whereHas('roles', function ($query) {
                    $query->where('name', 'creator');
                })->count())
                ->description('Số người tạo hồ sơ')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->chart([3, 4, 3, 5, 4, 3, 4]),
        ];
    }
}

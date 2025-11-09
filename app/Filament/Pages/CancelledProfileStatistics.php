<?php

namespace App\Filament\Pages;

use App\Models\Profile;
use App\Models\User;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CancelledProfileStatistics extends Page implements HasTable
{
    use InteractsWithTable;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Thống kê hồ sơ hủy';
    protected static ?string $title = 'Thống kê hồ sơ hủy';
    protected static ?string $slug = 'cancelled-profile-statistics';
    protected static ?string $navigationGroup = 'Báo cáo';
    protected static ?int $navigationSort = 6;

    protected static string $view = 'filament.pages.cancelled-profile-statistics';

    public static function getShieldPermissionPrefix(): string
    {
        return 'cancelled_profile_statistics';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('username')
                    ->label('Tên người dùng')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('today_count')
                    ->label('Hôm nay')
                    ->alignCenter()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->label('Tổng')
                    ]),
                TextColumn::make('week_count')
                    ->label('Tuần này')
                    ->alignCenter()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->label('Tổng')
                    ]),
                TextColumn::make('month_count')
                    ->label('Tháng này')
                    ->alignCenter()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->label('Tổng')
                    ]),
                TextColumn::make('last_month_count')
                    ->label('Tháng trước')
                    ->alignCenter()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->label('Tổng')
                    ]),
                TextColumn::make('year_count')
                    ->label('Năm nay')
                    ->alignCenter()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->label('Tổng')
                    ]),
            ])
            ->paginated(false);
    }

    protected function getTableQuery(): Builder
    {
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();
        $yearStart = Carbon::now()->startOfYear();

        return User::query()
            ->whereHas('roles', function ($query) {
                $query->where('name', 'creator');
            })
            ->withCount([
                'profiles as today_count' => function ($query) use ($today) {
                    $query->whereDate('created_at', $today)
                          ->where('status', 3);
                },
                'profiles as week_count' => function ($query) use ($weekStart) {
                    $query->where('created_at', '>=', $weekStart)
                          ->where('status', 3);
                },
                'profiles as month_count' => function ($query) use ($monthStart) {
                    $query->where('created_at', '>=', $monthStart)
                          ->where('status', 3);
                },
                'profiles as last_month_count' => function ($query) use ($lastMonthStart, $lastMonthEnd) {
                    $query->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
                          ->where('status', 3);
                },
                'profiles as year_count' => function ($query) use ($yearStart) {
                    $query->where('created_at', '>=', $yearStart)
                          ->where('status', 3);
                }
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}


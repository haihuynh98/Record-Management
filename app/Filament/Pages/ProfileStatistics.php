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

class ProfileStatistics extends Page implements HasTable
{
    use InteractsWithTable;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Thống kê hồ sơ';
    protected static ?string $title = 'Thống kê hồ sơ';
    protected static ?string $slug = 'profile-statistics';
    protected static ?string $navigationGroup = 'Báo cáo';
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.profile-statistics';

    public static function getShieldPermissionPrefix(): string
    {
        return 'profile_statistics';
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
        $yearStart = Carbon::now()->startOfYear();

        return User::query()
            ->whereHas('roles', function ($query) {
                $query->where('name', 'creator');
            })
            ->withCount([
                'profiles as today_count' => function ($query) use ($today) {
                    $query->whereDate('created_at', $today)
                          ->where('status', '!=', 3);
                },
                'profiles as week_count' => function ($query) use ($weekStart) {
                    $query->where('created_at', '>=', $weekStart)
                          ->where('status', '!=', 3);
                },
                'profiles as month_count' => function ($query) use ($monthStart) {
                    $query->where('created_at', '>=', $monthStart)
                          ->where('status', '!=', 3);
                },
                'profiles as year_count' => function ($query) use ($yearStart) {
                    $query->where('created_at', '>=', $yearStart)
                          ->where('status', '!=', 3);
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

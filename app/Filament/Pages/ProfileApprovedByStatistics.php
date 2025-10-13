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

class ProfileApprovedByStatistics extends Page implements HasTable
{
    use InteractsWithTable;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';
    protected static ?string $navigationLabel = 'Thống kê người duyệt';
    protected static ?string $title = 'Thống kê người duyệt';
    protected static ?string $slug = 'profile-approved-by-statistics';
    protected static ?string $navigationGroup = 'Báo cáo';
    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.profile-approved-by-statistics';

    public static function getShieldPermissionPrefix(): string
    {
        return 'profile_approved_by_statistics';
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
                // Exclude users with 'creator' role, only show users with different roles
                $query->where('name', '!=', 'creator');
            })
            ->whereHas('approvedProfiles') // Only show users who have approved at least one profile
            ->withCount([
                'approvedProfiles as today_count' => function ($query) use ($today) {
                    $query->whereDate('created_at', $today)
                          ->whereNotIn('status', [2, 3]);
                },
                'approvedProfiles as week_count' => function ($query) use ($weekStart) {
                    $query->where('created_at', '>=', $weekStart)
                          ->whereNotIn('status', [2, 3]);
                },
                'approvedProfiles as month_count' => function ($query) use ($monthStart) {
                    $query->where('created_at', '>=', $monthStart)
                          ->whereNotIn('status', [2, 3]);
                },
                'approvedProfiles as last_month_count' => function ($query) use ($lastMonthStart, $lastMonthEnd) {
                    $query->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
                          ->whereNotIn('status', [2, 3]);
                },
                'approvedProfiles as year_count' => function ($query) use ($yearStart) {
                    $query->where('created_at', '>=', $yearStart)
                          ->whereNotIn('status', [2, 3]); 
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

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
                    ->formatStateUsing(function ($state, $record) {
                        $count = $record->today_count ?? 0;
                        $amount = $record->today_amount ?? 0;
                        return view('filament.components.statistics-cell', [
                            'count' => $count,
                            'amount' => $amount
                        ]);
                    })
                    ->html()
                    ->alignCenter(),
                TextColumn::make('week_count')
                    ->label('Tuần này')
                    ->formatStateUsing(function ($state, $record) {
                        $count = $record->week_count ?? 0;
                        $amount = $record->week_amount ?? 0;
                        return view('filament.components.statistics-cell', [
                            'count' => $count,
                            'amount' => $amount
                        ]);
                    })
                    ->html()
                    ->alignCenter(),
                TextColumn::make('month_count')
                    ->label('Tháng này')
                    ->formatStateUsing(function ($state, $record) {
                        $count = $record->month_count ?? 0;
                        $amount = $record->month_amount ?? 0;
                        return view('filament.components.statistics-cell', [
                            'count' => $count,
                            'amount' => $amount
                        ]);
                    })
                    ->html()
                    ->alignCenter(),
                TextColumn::make('year_count')
                    ->label('Năm nay')
                    ->formatStateUsing(function ($state, $record) {
                        $count = $record->year_count ?? 0;
                        $amount = $record->year_amount ?? 0;
                        return view('filament.components.statistics-cell', [
                            'count' => $count,
                            'amount' => $amount
                        ]);
                    })
                    ->html()
                    ->alignCenter(),
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
                    $query->whereDate('created_at', $today);
                },
                'profiles as week_count' => function ($query) use ($weekStart) {
                    $query->where('created_at', '>=', $weekStart);
                },
                'profiles as month_count' => function ($query) use ($monthStart) {
                    $query->where('created_at', '>=', $monthStart);
                },
                'profiles as year_count' => function ($query) use ($yearStart) {
                    $query->where('created_at', '>=', $yearStart);
                }
            ])
            ->withSum([
                'profiles as today_amount' => function ($query) use ($today) {
                    $query->whereDate('created_at', $today);
                }
            ], 'amount')
            ->withSum([
                'profiles as week_amount' => function ($query) use ($weekStart) {
                    $query->where('created_at', '>=', $weekStart);
                }
            ], 'amount')
            ->withSum([
                'profiles as month_amount' => function ($query) use ($monthStart) {
                    $query->where('created_at', '>=', $monthStart);
                }
            ], 'amount')
            ->withSum([
                'profiles as year_amount' => function ($query) use ($yearStart) {
                    $query->where('created_at', '>=', $yearStart);
                }
            ], 'amount');
    }

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}

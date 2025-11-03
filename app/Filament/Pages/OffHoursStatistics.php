<?php

namespace App\Filament\Pages;

use App\Models\Profile;
use App\Models\SystemSetting;
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

class OffHoursStatistics extends Page implements HasTable
{
    use InteractsWithTable;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-moon';
    protected static ?string $navigationLabel = 'Thống kê Ngoài giờ';
    protected static ?string $title = 'Thống kê Ngoài giờ';
    protected static ?string $slug = 'off-hours-statistics';
    protected static ?string $navigationGroup = 'Thống kê ngoài giờ';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.off-hours-statistics';

    public static function getShieldPermissionPrefix(): string
    {
        return 'off_hours_statistics';
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

        $startTime = SystemSetting::getOffHoursStart();
        $endTime = SystemSetting::getOffHoursEnd();

        if (!$startTime || !$endTime) {
            // Nếu chưa cấu hình, trả về query rỗng
            return User::query()->whereRaw('1 = 0');
        }

        return User::query()
            ->whereHas('roles', function ($query) {
                $query->where('name', 'creator');
            })
            ->withCount([
                'profiles as today_count' => function ($query) use ($today, $startTime, $endTime) {
                    $this->applyOffHoursFilter($query, $today, $today, $startTime, $endTime, 'created_at');
                    $query->whereNotIn('status', [2, 3]);
                },
                'profiles as week_count' => function ($query) use ($weekStart, $startTime, $endTime) {
                    $this->applyOffHoursFilterForRange($query, $weekStart, Carbon::now(), $startTime, $endTime, 'created_at');
                    $query->whereNotIn('status', [2, 3]);
                },
                'profiles as month_count' => function ($query) use ($monthStart, $startTime, $endTime) {
                    $this->applyOffHoursFilterForRange($query, $monthStart, Carbon::now(), $startTime, $endTime, 'created_at');
                    $query->whereNotIn('status', [2, 3]);
                },
                'profiles as last_month_count' => function ($query) use ($lastMonthStart, $lastMonthEnd, $startTime, $endTime) {
                    $this->applyOffHoursFilterForRange($query, $lastMonthStart, $lastMonthEnd, $startTime, $endTime, 'created_at');
                    $query->whereNotIn('status', [2, 3]);
                },
                'profiles as year_count' => function ($query) use ($yearStart, $startTime, $endTime) {
                    $this->applyOffHoursFilterForRange($query, $yearStart, Carbon::now(), $startTime, $endTime, 'created_at');
                    $query->whereNotIn('status', [2, 3]);
                }
            ]);
    }

    /**
     * Apply off-hours filter for a single date
     */
    protected function applyOffHoursFilter(Builder $query, Carbon $date, Carbon $endDate, string $startTime, string $endTime, string $dateColumn = 'created_at'): void
    {
        [$startHour, $startMinute] = explode(':', $startTime);
        [$endHour, $endMinute] = explode(':', $endTime);

        if ($endTime < $startTime) {
            // Qua đêm: từ start đến 23:59:59 hôm nay và từ 00:00:00 đến end hôm sau
            $query->where(function ($q) use ($date, $startTime, $endTime, $dateColumn) {
                // Hôm nay: từ start đến 23:59:59
                $q->where(function ($subQ) use ($date, $startTime, $dateColumn) {
                    $subQ->whereDate($dateColumn, $date)
                         ->whereTime($dateColumn, '>=', $startTime);
                })
                // Hôm sau: từ 00:00:00 đến end
                ->orWhere(function ($subQ) use ($date, $endTime, $dateColumn) {
                    $nextDay = $date->copy()->addDay();
                    $subQ->whereDate($dateColumn, $nextDay)
                         ->whereTime($dateColumn, '<=', $endTime);
                });
            });
        } else {
            // Trong cùng ngày
            $query->whereDate($dateColumn, $date)
                  ->whereTime($dateColumn, '>=', $startTime)
                  ->whereTime($dateColumn, '<=', $endTime);
        }
    }

    /**
     * Apply off-hours filter for a date range
     */
    protected function applyOffHoursFilterForRange(Builder $query, Carbon $startDate, Carbon $endDate, string $startTime, string $endTime, string $dateColumn = 'created_at'): void
    {
        if ($endTime < $startTime) {
            // Qua đêm: từ start đến 23:59:59 hôm nay và từ 00:00:00 đến end hôm sau
            $query->where(function ($q) use ($startDate, $endDate, $startTime, $endTime, $dateColumn) {
                $currentDate = $startDate->copy();
                
                while ($currentDate->lte($endDate)) {
                    // Hôm nay: từ start đến 23:59:59
                    $q->orWhere(function ($subQ) use ($currentDate, $startTime, $dateColumn) {
                        $subQ->whereDate($dateColumn, $currentDate->format('Y-m-d'))
                             ->whereTime($dateColumn, '>=', $startTime);
                    });
                    
                    // Hôm sau: từ 00:00:00 đến end
                    $nextDate = $currentDate->copy()->addDay();
                    if ($nextDate->lte($endDate)) {
                        $q->orWhere(function ($subQ) use ($nextDate, $endTime, $dateColumn) {
                            $subQ->whereDate($dateColumn, $nextDate->format('Y-m-d'))
                                 ->whereTime($dateColumn, '<=', $endTime);
                        });
                    }
                    
                    $currentDate->addDay();
                }
            });
        } else {
            // Trong cùng ngày: từ start đến end trong khoảng ngày
            $query->where(function ($q) use ($startDate, $endDate, $startTime, $endTime, $dateColumn) {
                $currentDate = $startDate->copy();
                
                while ($currentDate->lte($endDate)) {
                    $q->orWhere(function ($subQ) use ($currentDate, $startTime, $endTime, $dateColumn) {
                        $subQ->whereDate($dateColumn, $currentDate->format('Y-m-d'))
                             ->whereTime($dateColumn, '>=', $startTime)
                             ->whereTime($dateColumn, '<=', $endTime);
                    });
                    
                    $currentDate->addDay();
                }
            });
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}


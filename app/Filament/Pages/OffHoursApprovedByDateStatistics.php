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

class OffHoursApprovedByDateStatistics extends Page implements HasTable
{
    use InteractsWithTable;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Thống kê người duyệt ngoài giờ theo ngày';
    protected static ?string $title = 'Thống kê người duyệt ngoài giờ theo ngày';
    protected static ?string $slug = 'off-hours-approved-by-date-statistics';
    protected static ?string $navigationGroup = 'Thống kê ngoài giờ';
    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.off-hours-approved-by-date-statistics';

    public $startDate;
    public $endDate;

    public static function getShieldPermissionPrefix(): string
    {
        return 'off_hours_approved_by_date_statistics';
    }

    public function mount(): void
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
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
                TextColumn::make('profile_count')
                    ->label('Số lượng hồ sơ đã duyệt')
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
        $startDate = $this->startDate ?? Carbon::now()->startOfMonth();
        $endDate = $this->endDate ?? Carbon::now()->endOfMonth();

        $startTime = SystemSetting::getOffHoursStart();
        $endTime = SystemSetting::getOffHoursEnd();

        if (!$startTime || !$endTime) {
            // Nếu chưa cấu hình, trả về query rỗng
            return User::query()->whereRaw('1 = 0');
        }

        return User::query()
            ->whereHas('roles', function ($query) {
                // Exclude users with 'creator' role, only show users with different roles
                $query->where('name', '!=', 'creator');
            })
            ->whereHas('approvedProfiles') // Only show users who have approved at least one profile
            ->withCount([
                'approvedProfiles as profile_count' => function ($query) use ($startDate, $endDate, $startTime, $endTime) {
                    $this->applyOffHoursFilterForRange($query, Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay(), $startTime, $endTime, 'created_at');
                    $query->whereNotIn('status', [2, 3]);
                }
            ]);
    }

    /**
     * Apply off-hours filter for a date range
     */
    protected function applyOffHoursFilterForRange(Builder $query, Carbon $startDate, Carbon $endDate, string $startTime, string $endTime, string $dateColumn = 'created_at'): void
    {
        if ($endTime < $startTime) {
            // Qua đêm: từ start đến 23:59:59 hôm nay và từ 00:00:00 đến end hôm sau
            $query->where(function ($q) use ($startDate, $endDate, $startTime, $endTime, $dateColumn) {
                $currentDate = $startDate->copy()->startOfDay();
                
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
                $currentDate = $startDate->copy()->startOfDay();
                
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

    public function updated($property): void
    {
        if (in_array($property, ['startDate', 'endDate'])) {
            $this->validateAndFilter();
        }
    }

    public function filter(): void
    {
        $this->validateAndFilter();
    }

    private function validateAndFilter(): void
    {
        // Validate dates
        if (!$this->startDate || !$this->endDate) {
            return;
        }

        if (Carbon::parse($this->startDate)->gt(Carbon::parse($this->endDate))) {
            // Swap dates if start date is after end date
            $temp = $this->startDate;
            $this->startDate = $this->endDate;
            $this->endDate = $temp;
        }

        $this->resetTable();
    }

    public function resetDates(): void
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->resetTable();
    }

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}


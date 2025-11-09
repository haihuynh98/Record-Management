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

class CancelledProfileDateStatistics extends Page implements HasTable
{
    use InteractsWithTable;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Thống kê hủy theo ngày';
    protected static ?string $title = 'Thống kê hủy theo ngày';
    protected static ?string $slug = 'cancelled-profile-date-statistics';
    protected static ?string $navigationGroup = 'Báo cáo';
    protected static ?int $navigationSort = 7;

    protected static string $view = 'filament.pages.cancelled-profile-date-statistics';

    public $startDate;
    public $endDate;

    public static function getShieldPermissionPrefix(): string
    {
        return 'cancelled_profile_date_statistics';
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
                    ->label('Số lượng hồ sơ hủy')
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

        return User::query()
            ->whereHas('roles', function ($query) {
                $query->where('name', 'creator');
            })
            ->withCount([
                'profiles as profile_count' => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [
                        Carbon::parse($startDate)->startOfDay(),
                        Carbon::parse($endDate)->endOfDay()
                    ])
                    ->where('status', 3);
                }
            ]);
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


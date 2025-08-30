<?php

namespace App\Filament\Widgets;

use App\Models\Profile;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class ProfileChart extends ChartWidget
{
    protected static ?string $heading = 'Xu hướng Hồ sơ theo Tuần';
    protected static ?string $maxHeight = '300px';

    public static function canView(): bool
    {
        return auth()->user()->can('widget_ProfileChart');
    }

    protected function getData(): array
    {
        $weeks = collect();
        $data = collect();
        
        for ($i = 7; $i >= 0; $i--) {
            $weekStart = Carbon::now()->subWeeks($i)->startOfWeek();
            $weekEnd = Carbon::now()->subWeeks($i)->endOfWeek();
            $weeks->push('Tuần ' . $weekStart->format('d/m'));
            
            $count = Profile::whereBetween('created_at', [$weekStart, $weekEnd])
                ->count();
            
            $data->push($count);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Số lượng hồ sơ',
                    'data' => $data->toArray(),
                    'fill' => true,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 3,
                    'tension' => 0.4,
                    'pointBackgroundColor' => 'rgb(59, 130, 246)',
                    'pointBorderColor' => '#fff',
                    'pointBorderWidth' => 2,
                    'pointRadius' => 6,
                ],
            ],
            'labels' => $weeks->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}

<?php

namespace App\Exports;

use App\Models\Game;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class VipPlayersPerGameSheet implements FromCollection, WithEvents, WithHeadings, WithTitle
{
    public function __construct(protected Game $game) {}

    public function collection(): Collection
    {
        return $this->game->vipPlayers->values()->map(fn ($player, int $index) => [
            $index + 1,
            $player->customer_name,
            $player->phone,
            $player->facebook_link ?? '',
            $player->total_deposit,
            $player->notes ?? '',
        ]);
    }

    public function headings(): array
    {
        return [
            'STT',
            'Họ tên khách hàng',
            'Số điện thoại',
            'Link Facebook',
            'Tổng số tiền đã nạp',
            'Chú thích',
        ];
    }

    public function title(): string
    {
        return Str::limit($this->game->name, 31, '');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $rowCount = $this->game->vipPlayers->count() + 1;
                $lastColumn = 'F';

                if ($rowCount < 2) {
                    return;
                }

                $range = "A1:{$lastColumn}{$rowCount}";

                $sheet->setAutoFilter($range);

                $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E2E8F0'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                $sheet->getStyle("A2:A{$rowCount}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("E2:E{$rowCount}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');

                foreach (range('A', $lastColumn) as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }
}

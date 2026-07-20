<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class OrdersExport implements FromView, ShouldAutoSize, WithEvents
{
    // Baris-baris tetap (fixed) sebelum tabel data dimulai
    private const ROW_TITLE          = 1;
    private const ROW_PERIODE        = 2;
    private const ROW_REKAP_STATUS_H = 4;
    private const ROW_PAID           = 5;
    private const ROW_WAITING        = 6;
    private const ROW_EXPIRED        = 7;
    private const ROW_REJECTED       = 8;
    private const ROW_REKAP_UANG_H   = 10;
    private const ROW_GROSS          = 11;
    private const ROW_SHIPPING       = 12;
    private const ROW_NET            = 13;
    private const ROW_TABLE_HEADER   = 16;
    private const ROW_TABLE_START    = 17;

    public function __construct(
        protected $orders,
        protected array $stats,
        protected ?string $startDate,
        protected ?string $endDate,
    ) {}

    public function view(): View
    {
        return view('order.export', [
            'orders'    => $this->orders,
            'stats'     => $this->stats,
            'startDate' => $this->startDate,
            'endDate'   => $this->endDate,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastDataRow = self::ROW_TABLE_START + max($this->orders->count() - 1, 0);
                $lastCol = 'F';

                // ===== Title =====
                $sheet->mergeCells('A1:F1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB('FFFFFF');
                $sheet->getStyle('A1:F1')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('2C3E50');
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getRowDimension(1)->setRowHeight(28);

                // ===== Periode =====
                $sheet->getStyle('A2')->getFont()->setBold(true);
                $sheet->getStyle('A2:B2')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('ECF0F1');

                // ===== Section headers (Rekap Status & Rekap Pendapatan) =====
                foreach (["A" . self::ROW_REKAP_STATUS_H, "A" . self::ROW_REKAP_UANG_H] as $cell) {
                    $row = (int) substr($cell, 1);
                    $sheet->mergeCells("A{$row}:B{$row}");
                    $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('FFFFFF');
                    $sheet->getStyle("A{$row}:B{$row}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('34495E');
                }

                // ===== Rekap Status rows =====
                $statusColors = [
                    self::ROW_PAID     => '2ECC71', // hijau
                    self::ROW_WAITING  => 'F1C40F', // kuning
                    self::ROW_EXPIRED  => 'E74C3C', // merah
                    self::ROW_REJECTED => '95A5A6', // abu
                ];
                foreach ($statusColors as $row => $color) {
                    $sheet->getStyle("A{$row}:B{$row}")->applyFromArray([
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BDC3C7']],
                        ],
                    ]);
                    $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("B{$row}")->getFont()->setBold(true);
                    // aksen warna kecil di kolom A
                    $sheet->getStyle("A{$row}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB($color);
                    $sheet->getStyle("A{$row}")->getFont()->getColor()->setRGB('FFFFFF');
                }

                // ===== Rekap Pendapatan rows =====
                foreach ([self::ROW_GROSS, self::ROW_SHIPPING, self::ROW_NET] as $row) {
                    $sheet->getStyle("A{$row}:B{$row}")->applyFromArray([
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BDC3C7']],
                        ],
                    ]);
                    $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode('#,##0');
                    $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }
                // Pendapatan bersih ditonjolkan
                $sheet->getStyle('A' . self::ROW_NET . ':B' . self::ROW_NET)->getFont()->setBold(true);
                $sheet->getStyle('A' . self::ROW_NET . ':B' . self::ROW_NET)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('D5F5E3');

                // ===== Table header =====
                $headerRange = "A" . self::ROW_TABLE_HEADER . ":{$lastCol}" . self::ROW_TABLE_HEADER;
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '2C3E50'],
                    ],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
                    ],
                ]);
                $sheet->getRowDimension(self::ROW_TABLE_HEADER)->setRowHeight(22);

                // ===== Table data rows =====
                if ($this->orders->count() > 0) {
                    $dataRange = "A" . self::ROW_TABLE_START . ":{$lastCol}{$lastDataRow}";
                    $sheet->getStyle($dataRange)->applyFromArray([
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BDC3C7']],
                        ],
                    ]);
                    // Alternating row color (zebra)
                    for ($row = self::ROW_TABLE_START; $row <= $lastDataRow; $row++) {
                        if (($row - self::ROW_TABLE_START) % 2 === 1) {
                            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setRGB('F8F9F9');
                        }
                    }
                    // Format kolom Total (D) sebagai angka
                    $sheet->getStyle("D" . self::ROW_TABLE_START . ":D{$lastDataRow}")
                        ->getNumberFormat()->setFormatCode('#,##0');
                    $sheet->getStyle("D" . self::ROW_TABLE_START . ":D{$lastDataRow}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    // Kolom No (A) center
                    $sheet->getStyle("A" . self::ROW_TABLE_START . ":A{$lastDataRow}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    // Kolom Status (E) center + bold
                    $sheet->getStyle("E" . self::ROW_TABLE_START . ":E{$lastDataRow}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("E" . self::ROW_TABLE_START . ":E{$lastDataRow}")->getFont()->setBold(true);
                }

                // Freeze pane di bawah header tabel biar saat scroll header tetap kelihatan
                $sheet->freezePane('A' . self::ROW_TABLE_START);

                // Lebar kolom manual (override autosize untuk beberapa kolom)
                $sheet->getColumnDimension('B')->setWidth(28);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('F')->setWidth(20);
            },
        ];
    }
}

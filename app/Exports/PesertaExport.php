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

class PesertaExport implements FromView, ShouldAutoSize, WithEvents
{
    private const ROW_HEADER = 1;
    private const ROW_DATA_START = 2;
    private const LAST_COLUMN = 'N'; // sesuaikan kalau jumlah kolom di view berubah

    // Kolom Alamat Lengkap dikunci lebarnya (isinya bisa panjang) seperti Sinopsis di export Film
    private const ALAMAT_COLUMN = 'K';
    private const ALAMAT_WIDTH = 35;

    // Kolom Username Instagram: tidak perlu ikut auto-size
    private const INSTAGRAM_COLUMN = 'L';
    private const INSTAGRAM_WIDTH = 18;

    public function __construct(protected $users) {}

    public function view(): View
    {
        return view('user.export-peserta', [
            'users' => $this->users,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = self::LAST_COLUMN;
                $lastRow = self::ROW_DATA_START + max($this->users->count() - 1, 0);

                // Header
                $sheet->getStyle("A" . self::ROW_HEADER . ":{$lastCol}" . self::ROW_HEADER)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '2C3E50'],
                    ],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => false],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
                    ],
                ]);
                $sheet->getRowDimension(self::ROW_HEADER)->setRowHeight(20);

                if ($this->users->count() > 0) {
                    $dataRange = "A" . self::ROW_DATA_START . ":{$lastCol}{$lastRow}";

                    $sheet->getStyle($dataRange)->applyFromArray([
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BDC3C7']],
                        ],
                        'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => false],
                    ]);

                    // Zebra stripe
                    for ($row = self::ROW_DATA_START; $row <= $lastRow; $row++) {
                        if (($row - self::ROW_DATA_START) % 2 === 1) {
                            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setRGB('F8F9F9');
                        }
                    }

                    // Kolom No (A) center
                    $sheet->getStyle("A" . self::ROW_DATA_START . ":A{$lastRow}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Kolom Role center + bold
                    $sheet->getStyle("E" . self::ROW_DATA_START . ":E{$lastRow}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // Kunci lebar kolom Alamat Lengkap, tidak ikut auto-size
                $sheet->getColumnDimension(self::ALAMAT_COLUMN)
                    ->setAutoSize(false)
                    ->setWidth(self::ALAMAT_WIDTH);

                // Kunci lebar kolom Username Instagram, tidak ikut auto-size
                $sheet->getColumnDimension(self::INSTAGRAM_COLUMN)
                    ->setAutoSize(false)
                    ->setWidth(self::INSTAGRAM_WIDTH);

                // Kolom No Whatsapp (C) -> paksa jadi Text supaya tidak dikonversi
                // Excel ke notasi ilmiah (6,28967E+12) dan tidak kehilangan digit di depan (0/62).
                // Ditulis ulang langsung dari data asli, bukan dari cell yang sudah terlanjur dianggap angka.
                if ($this->users->count() > 0) {
                    foreach ($this->users->values() as $i => $user) {
                        $row = self::ROW_DATA_START + $i;
                        $sheet->setCellValueExplicit(
                            "C{$row}",
                            (string) $user->no_hp,
                            \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                        );
                    }
                    $sheet->getStyle("C" . self::ROW_DATA_START . ":C{$lastRow}")
                        ->getNumberFormat()->setFormatCode('@');
                }

                $sheet->freezePane('A' . self::ROW_DATA_START);
                $sheet->setSelectedCell('A1');
            },
        ];
    }
}

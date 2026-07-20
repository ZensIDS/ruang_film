<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class FilmsExport implements FromView, ShouldAutoSize, WithEvents
{
    private const ROW_HEADER = 1;
    private const ROW_DATA_START = 2;

    // Kolom terakhir di tabel -> HARUS sinkron dengan jumlah <th> di view (film/export.blade.php)
    private const LAST_COLUMN = 'Z';

    // Kolom Sinopsis: dikunci lebarnya, tidak ikut auto-size
    private const SINOPSIS_COLUMN = 'J';
    private const SINOPSIS_WIDTH = 30;

    // Kolom GSM: sama seperti Sinopsis, isinya bisa panjang (list URL)
    private const GSM_COLUMN = 'O';
    private const GSM_WIDTH = 30;

    /**
     * Sesuaikan disk di sini kalau file disimpan bukan di disk 'public'.
     */
    private const DISK = 'public';

    public function __construct(protected $films) {}

    public function view(): View
    {
        return view('film.export', [
            'films' => $this->films,
        ]);
    }

    /**
     * Ubah path relatif (yang disimpan di DB) jadi URL lengkap yang bisa diklik.
     * Return '-' kalau path kosong.
     */
    public static function fullUrl(?string $path): string
    {
        if (empty($path)) {
            return '-';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk(self::DISK)->url($path);
    }

    public static function gsmUrlList(?string $rawJson): string
    {
        if (empty($rawJson)) {
            return '-';
        }

        $paths = json_decode($rawJson, true);

        if (! is_array($paths) || empty($paths)) {
            // fallback: kalau ternyata bukan JSON valid, anggap satu path biasa
            return self::fullUrl($rawJson);
        }

        $urls = array_map(function ($path) {
            return '"' . self::fullUrl($path) . '"';
        }, $paths);

        return implode(",\n", $urls);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = self::LAST_COLUMN;
                $lastRow = self::ROW_DATA_START + max($this->films->count() - 1, 0);

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

                if ($this->films->count() > 0) {
                    $dataRange = "A" . self::ROW_DATA_START . ":{$lastCol}{$lastRow}";

                    // Border saja, TANPA wrap text
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
                }

                // Kunci lebar kolom Sinopsis SETELAH auto-size jalan,
                // supaya tidak ikut melebar mengikuti isi teksnya yang panjang.
                $sheet->getColumnDimension(self::SINOPSIS_COLUMN)
                    ->setAutoSize(false)
                    ->setWidth(self::SINOPSIS_WIDTH);

                $sheet->getColumnDimension(self::GSM_COLUMN)
                    ->setAutoSize(false)
                    ->setWidth(self::GSM_WIDTH);

                $sheet->freezePane('A' . self::ROW_DATA_START);
                $sheet->setSelectedCell('A1');
            },
        ];
    }
}

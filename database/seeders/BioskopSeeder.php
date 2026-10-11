<?php

namespace Database\Seeders;

use App\Models\Bioskop;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BioskopSeeder extends Seeder
{
    /**
     * Hapus semua data bioskop lalu isi ulang: 5 bioskop berbeda,
     * masing-masing tayang 3 hari ke depan (mulai hari ini) dengan 3 sesi per hari.
     * Hanya menyentuh tabel bioskops. Nama, tempat, & alamat hanyalah contoh;
     * ubah lewat menu admin: Data Program > Bioskop Gratis.
     */
    public function run()
    {
        Bioskop::truncate();

        $cinemas = [
            ['Bioskop Tengkorak Mbah Sayem', 'Gedung Karya Dharma',  'Jl. Contoh No. 1'],
            ['Bioskop Pocong Keliling',      'Pendopo Kabupaten',    'Jl. Contoh No. 2'],
            ['Bioskop Kuntilanak Merah',     'Aula Kecamatan',       'Jl. Contoh No. 3'],
            ['Bioskop Genderuwo Wetan',      'Balai Desa Sukamaju',  'Jl. Contoh No. 4'],
            ['Bioskop Layar Tancap Angker',  'Lapangan Alun-Alun',   'Jl. Contoh No. 5'],
        ];

        $slots = [
            ['Sesi 1', '13:00'],
            ['Sesi 2', '15:30'],
            ['Sesi 3', '19:00'],
        ];

        // Pola status [bioskop][hari][sesi]: 0 = available, 1 = full (dibuat bervariasi untuk contoh)
        $full = [
            [[0, 0, 0], [0, 0, 1], [0, 0, 0]],
            [[0, 0, 0], [0, 0, 0], [0, 1, 1]],
            [[1, 0, 0], [0, 0, 0], [0, 0, 0]],
            [[0, 0, 1], [0, 1, 1], [1, 1, 1]],
            [[0, 0, 0], [0, 0, 0], [0, 0, 0]],
        ];

        foreach ($cinemas as $i => [$name, $venue, $address]) {
            $schedules = [];

            for ($day = 0; $day < 3; $day++) {
                $sessions = [];
                foreach ($slots as $j => [$label, $time]) {
                    $sessions[] = [
                        'label'  => $label,
                        'time'   => $time,
                        'status' => $full[$i][$day][$j] ? Bioskop::STATUS_FULL : Bioskop::STATUS_AVAILABLE,
                    ];
                }

                $schedules[] = [
                    'date'     => Carbon::today()->addDays($day)->toDateString(),
                    'sessions' => $sessions,
                ];
            }

            Bioskop::create([
                'name'       => $name,
                'venue'      => $venue,
                'address'    => $address,
                'schedules'  => $schedules,
                'sort_order' => $i + 1,
                'is_active'  => true,
            ]);
        }
    }
}
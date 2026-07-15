<?php

namespace Database\Seeders;

use App\Models\SubmissionSetting;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SubmissionSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        collect($this->settings())->each(function ($setting) {
            $openAt = Carbon::parse($setting['open_at']);
            $closeAt = Carbon::parse($setting['close_at']);

            SubmissionSetting::updateOrCreate(
                ['name' => $setting['name']],
                array_merge($setting, [
                    'last_year_featured_film_ids' => $setting['last_year_featured_film_ids'] ?? [],
                    'timeline_items' => $setting['timeline_items']
                        ?? SubmissionSetting::defaultTimelineItems($openAt, $closeAt, $setting['name']),
                    'open_at' => $openAt,
                    'close_at' => $closeAt,
                ])
            );
        });
    }

    protected function settings()
    {
        return [
            [
                'name' => 'Periode Akhir 2025',
                'hero_title' => "FESTIVAL FILM\nHOROR 2025",
                'hero_description' => 'Periode perdana yang mempertemukan karya, komunitas, dan publik dalam satu panggung festival horor berbasis budaya lokal.',
                'hero_image' => 'landing/images/BACKGROUND FFH 2026.png',
                'about_title' => 'Festival Film Horor 2025',
                'about_description' => 'FFH 2025 menjadi langkah awal hadirnya ruang apresiasi dan pengembangan sinema horor yang bertumpu pada kekayaan cerita lokal Indonesia.',
                'about_description_secondary' => 'Melalui kompetisi, diskusi, pemutaran, dan kerja kolaboratif, festival ini membangun fondasi ekosistem yang mempertemukan sineas, komunitas, dan publik.',
                'about_image' => 'landing/images/Fest.jpg',
                'hashtag' => '#FestivalFilmHoror2025',
                'theme_title' => 'Tema Festival Film Horor 2025',
                'theme_name' => 'AKAR GELAP',
                'theme_quote' => '"Membaca ulang horor sebagai ingatan, warisan, dan suara dari ruang-ruang yang jarang diterangi."',
                'theme_description' => "Periode 2025 menjadi titik mula untuk membaca horor sebagai pengalaman sosial dan budaya.\n\nTema ini membuka jalan bagi karya yang merekam keresahan, kepercayaan, dan ketegangan yang hidup di sekitar kita.",
                'theme_image' => 'landing/images/BACKGROUND FFH 2026.png',
                'festival_board' => $this->festivalBoard(),
                'last_year_title' => 'A New Horror Experience',
                'last_year_description' => 'FFH 2025 menjadi fondasi awal festival: karya-karya terbaik lahir dari keberanian membaca horor sebagai pengalaman budaya, sosial, dan personal.',
                'last_year_catalog_label' => 'Download Katalog Festival Film Horor 2025',
                'last_year_catalog_url' => '/download/ekatalog',
                'last_year_catalog_file' => null,
                'last_year_stat_film_submitted' => 48,
                'last_year_stat_special_films' => 12,
                'last_year_stat_audience' => 2000,
                'last_year_stat_participants' => 28,
                'open_at' => '2025-07-01 00:00:00',
                'close_at' => '2025-08-31 23:59:00',
            ],
            [
                'name' => 'Periode Awal 2026',
                'hero_title' => "FESTIVAL FILM\nHOROR 2026",
                'hero_description' => 'Horor tidak selalu hadir sebagai sosok yang menakutkan. Ia hidup dalam ingatan, kepercayaan, trauma, dan pengalaman yang diwariskan dari generasi ke generasi.',
                'hero_image' => 'landing/images/BACKGROUND FFH 2026.png',
                'about_title' => 'Festival Film Horor 2026',
                'about_description' => 'Festival Film Horor (FFH) merupakan apresiasi, edukasi, ruang, dan pengembangan ekosistem sinema horor di Indonesia. Festival ini hadir untuk membuka ruang diskusi yang lebih luas mengenai horor sebagai medium yang merekam pengalaman manusia, ingatan kolektif, budaya lokal, hingga berbagai persoalan sosial yang hidup di tengah masyarakat.',
                'about_description_secondary' => 'Melalui pemutaran film, kompetisi, diskusi, pameran, lokakarya, dan program publik lainnya, FFH berupaya mempertemukan pembuat film, akademisi, komunitas, pelajar, serta publik dalam satu ruang yang inklusif dan kolaboratif.',
                'about_image' => 'landing/images/Fest.jpg',
                'hashtag' => '#FestivalFilmHoror2026',
                'theme_title' => 'Tema Festival Film Horor 2026',
                'theme_name' => 'INDIGO',
                'theme_quote' => '"Melihat yang tak terlihat. Membaca yang terlupakan. Membangun yang akan datang."',
                'theme_description' => "Sebenarnya, apakah Indigo itu?\n\nIndigo, secara harfiah, merupakan warna nila atau ungu. Dalam budaya kekinian, indigo mulai mengalami perkembangan makna dan kerap dilekatkan pada sensitivitas, intuisi, dan pembacaan atas hal-hal yang tak kasatmata.\n\nDi Nusantara, cerita tentang dunia yang tak terindera tumbuh subur dalam legenda, mitos, dan pengalaman spiritual. Karena itu, INDIGO dipilih sebagai tema utama FFH 2026 untuk membaca ulang horor dari sudut pandang budaya, spiritualitas, psikologi, dan ingatan kolektif.\n\nFestival Film Horor 2026 mencoba menjadi titik temu antara pengetahuan tradisional berbasis pengalaman dengan pengetahuan modern yang menuntut pembuktian rasional, sehingga lahir ruang dialog yang lebih kaya dan terbuka.",
                'theme_image' => 'landing/images/BACKGROUND FFH 2026.png',
                'festival_board' => $this->festivalBoard(),
                'last_year_title' => 'A New Horror Experience',
                'last_year_description' => 'Festival Film Horor 2025 menjadi langkah awal hadirnya platform horor berbasis kelokalan di Indonesia. Edisi perdana ini membangun fondasi melalui antusiasme audiens, partisipasi sineas, serta terbentuknya ekosistem yang mempertemukan film, budaya, kolaborasi, dan potensi ekonomi lokal.',
                'last_year_catalog_label' => 'Download Katalog Festival Film Horor 2025',
                'last_year_catalog_url' => '/download/ekatalog',
                'last_year_catalog_file' => null,
                'last_year_stat_film_submitted' => 285,
                'last_year_stat_special_films' => 60,
                'last_year_stat_audience' => 8000,
                'last_year_stat_participants' => 62,
                'open_at' => '2026-06-08 23:35:00',
                'close_at' => '2026-08-06 23:59:00',
            ],
            [
                'name' => 'Periode Akhir 2026',
                'hero_title' => "FESTIVAL FILM\nHOROR 2026",
                'hero_description' => 'Periode lanjutan yang tetap fleksibel lintas bulan hingga tahun berikutnya.',
                'hero_image' => 'landing/images/BACKGROUND FFH 2026.png',
                'about_title' => 'Festival Film Horor 2026',
                'about_description' => 'Periode lanjutan festival yang menyiapkan kesinambungan kurasi, penjurian, dan program publik menuju tahun berikutnya.',
                'about_description_secondary' => 'Setting ini disiapkan agar alur landing page, timeline, dan administrasi submission tetap konsisten untuk periode susulan.',
                'about_image' => 'landing/images/Fest.jpg',
                'hashtag' => '#FestivalFilmHoror2026',
                'theme_title' => 'Tema Festival Film Horor 2026',
                'theme_name' => 'INDIGO',
                'theme_quote' => '"Melihat yang tak terlihat. Membaca yang terlupakan. Membangun yang akan datang."',
                'theme_description' => 'Periode lanjutan ini mempertahankan tema utama festival sekaligus membuka ruang penyesuaian program hingga awal 2027.',
                'theme_image' => 'landing/images/BACKGROUND FFH 2026.png',
                'festival_board' => $this->festivalBoard(),
                'last_year_title' => 'A New Horror Experience',
                'last_year_description' => 'Periode ini melanjutkan fondasi 2026 sambil menyiapkan materi highlight terbaik untuk siklus festival berikutnya.',
                'last_year_catalog_label' => 'Download Katalog Festival Film Horor 2026',
                'last_year_catalog_url' => '/download/ekatalog',
                'last_year_catalog_file' => null,
                'last_year_stat_film_submitted' => 0,
                'last_year_stat_special_films' => 0,
                'last_year_stat_audience' => 0,
                'last_year_stat_participants' => 0,
                'open_at' => '2026-11-01 00:00:00',
                'close_at' => '2027-01-31 23:59:00',
            ],
        ];
    }

    protected function festivalBoard()
    {
        return [
            ['name' => 'Indrata Nur Bayuaji', 'title' => 'Bupati Pacitan', 'image' => 'landing/images/INB.png'],
            ['name' => 'Garin Nugroho', 'title' => 'Produser, Sutradara & Penulis', 'image' => 'landing/images/GARIN.png'],
            ['name' => 'Ong Harry Wahyu', 'title' => 'Penggerak Budaya & Komunitas', 'image' => 'landing/images/ONGGY.png'],
        ];
    }
}

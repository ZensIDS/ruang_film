<?php

namespace Database\Seeders;

use App\Models\Program;
use App\Models\ProgramCategory;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            ProgramCategorySeeder::class,
        ]);

        $categories = ProgramCategory::query()
            ->whereIn('slug', ['edukasi', 'eksperiens', 'ekosistem'])
            ->get()
            ->keyBy('slug');

        collect($this->programs())
            ->each(function ($programData) use ($categories) {
                $category = $categories->get($programData['category_slug']);

                if (!$category) {
                    return;
                }

                Program::updateOrCreate(
                    ['slug' => $programData['slug']],
                    [
                        'program_category_id' => $category->id,
                        'title' => $programData['title'],
                        'summary' => $programData['summary'],
                        'content' => $programData['content'],
                        'poster' => $programData['poster'],
                        'sort_order' => $programData['sort_order'],
                        'is_active' => true,
                    ]
                );
            });
    }

    protected function programs()
    {
        return [
            [
                'category_slug' => 'edukasi',
                'title' => 'Kelas Kritik Horor',
                'slug' => 'kelas-kritik-horor',
                'summary' => 'Sesi pembacaan film horor dari sudut pandang kritik, budaya, dan bahasa visual.',
                'content' => '<p>Kelas ini mempertemukan peserta dengan pendekatan dasar membaca film horor secara kritis.</p><p>Diskusi mencakup struktur adegan, simbol, ritme ketegangan, dan konteks budaya yang membentuk pengalaman menonton.</p>',
                'poster' => 'landing/images/Fest.jpg',
                'sort_order' => 1,
            ],
            [
                'category_slug' => 'edukasi',
                'title' => 'Workshop Penulisan Cerita Gelap',
                'slug' => 'workshop-penulisan-cerita-gelap',
                'summary' => 'Lokakarya intensif untuk mengembangkan ide cerita, logline, dan worldbuilding horor.',
                'content' => '<p>Peserta diajak menyusun premis, konflik, dan ketegangan yang lahir dari pengalaman lokal maupun ingatan kolektif.</p><p>Fokus utama workshop adalah memperkuat orisinalitas dan konsistensi nada cerita.</p>',
                'poster' => 'landing/images/BACKGROUND FFH 2026.png',
                'sort_order' => 2,
            ],
            [
                'category_slug' => 'edukasi',
                'title' => 'Diskusi Produksi Film Independen',
                'slug' => 'diskusi-produksi-film-independen',
                'summary' => 'Forum berbagi strategi produksi efektif untuk sineas dan komunitas film independen.',
                'content' => '<p>Program ini membahas perencanaan produksi, kerja kolaboratif, pengelolaan kru, dan pemanfaatan sumber daya terbatas.</p><p>Materi dirancang agar relevan bagi pembuat film pemula maupun yang sedang mengembangkan proyek berikutnya.</p>',
                'poster' => 'landing/images/kategori/UMUM.png',
                'sort_order' => 3,
            ],
            [
                'category_slug' => 'edukasi',
                'title' => 'Mentoring Talenta Muda',
                'slug' => 'mentoring-talenta-muda',
                'summary' => 'Pendampingan terarah bagi pelajar dan talenta baru untuk membangun proyek film pertamanya.',
                'content' => '<p>Mentor akan mendampingi peserta dalam menyusun arah proyek, kebutuhan tim, serta langkah pengembangan yang realistis.</p><p>Program ini menekankan proses belajar yang aplikatif dan berkelanjutan.</p>',
                'poster' => 'landing/images/kategori/PELAJAR REGIONAL.png',
                'sort_order' => 4,
            ],
            [
                'category_slug' => 'edukasi',
                'title' => 'Laboratorium Pitching Proyek',
                'slug' => 'laboratorium-pitching-proyek',
                'summary' => 'Simulasi pitching untuk melatih presentasi ide program, film, atau inisiatif kreatif kepada mitra.',
                'content' => '<p>Peserta berlatih menyusun presentasi singkat, tajam, dan meyakinkan untuk kebutuhan kemitraan atau pengembangan proyek.</p><p>Setiap sesi dilengkapi umpan balik untuk memperkuat narasi dan kejelasan tujuan.</p>',
                'poster' => 'landing/images/wisata/wisata1.jpg',
                'sort_order' => 5,
            ],
            [
                'category_slug' => 'eksperiens',
                'title' => 'Pemutaran Tengah Malam',
                'slug' => 'pemutaran-tengah-malam',
                'summary' => 'Rangkaian screening malam dengan kurasi suasana ruang yang lebih imersif dan intens.',
                'content' => '<p>Program pemutaran ini dirancang untuk membangun pengalaman menonton yang lebih dekat dengan atmosfer horor.</p><p>Penonton diajak masuk ke ruang tontonan yang dikurasi secara visual, suara, dan ritme acara.</p>',
                'poster' => 'landing/images/wisata/wisata2.jpg',
                'sort_order' => 1,
            ],
            [
                'category_slug' => 'eksperiens',
                'title' => 'Tur Horor Lokasi Cerita',
                'slug' => 'tur-horor-lokasi-cerita',
                'summary' => 'Perjalanan singkat ke titik-titik inspiratif yang menghubungkan cerita, ruang, dan memori lokal.',
                'content' => '<p>Tur ini membuka ruang perjumpaan antara narasi horor dengan lanskap Pacitan dan cerita yang berkembang di masyarakat.</p><p>Peserta akan mendapatkan pengalaman observasi langsung untuk membaca ruang sebagai sumber cerita.</p>',
                'poster' => 'landing/images/wisata/wisata3.jpg',
                'sort_order' => 2,
            ],
            [
                'category_slug' => 'eksperiens',
                'title' => 'Ruang Dengar Cerita Malam',
                'slug' => 'ruang-dengar-cerita-malam',
                'summary' => 'Sesi mendengar kisah, folklore, dan pengalaman personal dalam format pertunjukan intim.',
                'content' => '<p>Format program ini menempatkan cerita lisan sebagai inti pengalaman festival.</p><p>Pengunjung diajak menyimak kisah dalam suasana yang mendukung imajinasi, keheningan, dan intensitas emosi.</p>',
                'poster' => 'landing/images/wisata/wisata4.jpg',
                'sort_order' => 3,
            ],
            [
                'category_slug' => 'eksperiens',
                'title' => 'Instalasi Horor Immersive',
                'slug' => 'instalasi-horor-immersive',
                'summary' => 'Instalasi audio visual yang mengajak pengunjung merasakan pengalaman horor secara multisensor.',
                'content' => '<p>Program ini menggabungkan elemen suara, cahaya, visual, dan lintasan ruang menjadi pengalaman yang lebih partisipatif.</p><p>Setiap pengunjung dapat menafsirkan narasi yang hadir melalui sensasi dan detail ruang.</p>',
                'poster' => 'landing/images/wisata/wisata5.jpg',
                'sort_order' => 4,
            ],
            [
                'category_slug' => 'eksperiens',
                'title' => 'Pagelaran Budaya Malam Hari',
                'slug' => 'pagelaran-budaya-malam-hari',
                'summary' => 'Pertunjukan budaya yang mengolah mitos, gerak, musik, dan simbol-simbol lokal ke dalam pengalaman festival.',
                'content' => '<p>Pagelaran ini menghadirkan pendekatan performatif terhadap horor sebagai ekspresi budaya dan ingatan masyarakat.</p><p>Program disusun untuk mempertemukan tradisi, pertunjukan, dan atmosfer festival dalam satu rangkaian.</p>',
                'poster' => 'landing/images/wisata/wisata6.jpg',
                'sort_order' => 5,
            ],
            [
                'category_slug' => 'ekosistem',
                'title' => 'Forum Kolaborasi Komunitas',
                'slug' => 'forum-kolaborasi-komunitas',
                'summary' => 'Pertemuan lintas komunitas untuk merancang kerja sama program, distribusi, dan pengembangan jaringan.',
                'content' => '<p>Forum ini mempertemukan pengelola komunitas, penyelenggara acara, dan pelaku kreatif untuk membangun kerja bersama.</p><p>Topik diskusi meliputi model kolaborasi, pembagian peran, dan keberlanjutan program.</p>',
                'poster' => 'landing/images/collab/col1.png',
                'sort_order' => 1,
            ],
            [
                'category_slug' => 'ekosistem',
                'title' => 'Meet The Festival Partners',
                'slug' => 'meet-the-festival-partners',
                'summary' => 'Sesi pertemuan terbuka dengan mitra festival untuk membicarakan dukungan, peluang, dan kebutuhan program.',
                'content' => '<p>Peserta dapat memahami bagaimana program festival dirancang bersama berbagai pihak dari sektor budaya, pendidikan, dan komersial.</p><p>Sesi ini juga membuka peluang penjajakan kolaborasi baru untuk edisi berikutnya.</p>',
                'poster' => 'landing/images/collab/col2.png',
                'sort_order' => 2,
            ],
            [
                'category_slug' => 'ekosistem',
                'title' => 'Pasar Ide Kreatif',
                'slug' => 'pasar-ide-kreatif',
                'summary' => 'Ruang temu bagi penggagas proyek, komunitas, dan mitra untuk mempresentasikan ide dan menjajaki pengembangan.',
                'content' => '<p>Program ini memfasilitasi pertukaran ide secara lebih terbuka antara pembuat program, produser, komunitas, dan calon mitra.</p><p>Fokusnya adalah memperluas kemungkinan realisasi proyek melalui percakapan yang terarah.</p>',
                'poster' => 'landing/images/collab/col3.png',
                'sort_order' => 3,
            ],
            [
                'category_slug' => 'ekosistem',
                'title' => 'Jaringan Distribusi Alternatif',
                'slug' => 'jaringan-distribusi-alternatif',
                'summary' => 'Diskusi praktik distribusi film dan program publik lewat jalur komunitas, ruang alternatif, dan jejaring lokal.',
                'content' => '<p>Program ini mengulas strategi distribusi yang tidak selalu bergantung pada jalur komersial utama.</p><p>Pembahasan menyorot kerja komunitas, pemutaran keliling, hingga distribusi berbasis kolaborasi lintas kota.</p>',
                'poster' => 'landing/images/collab/col4.png',
                'sort_order' => 4,
            ],
            [
                'category_slug' => 'ekosistem',
                'title' => 'Ruang Keberlanjutan Festival',
                'slug' => 'ruang-keberlanjutan-festival',
                'summary' => 'Percakapan strategis tentang bagaimana festival bertumbuh secara berkelanjutan bersama publik dan mitra.',
                'content' => '<p>Sesi ini membahas model keberlanjutan dari sisi program, pendanaan, partisipasi publik, dan kerja institusional.</p><p>Tujuannya adalah membangun fondasi festival yang sehat dan relevan untuk jangka panjang.</p>',
                'poster' => 'landing/images/collab/col5.png',
                'sort_order' => 5,
            ],
        ];
    }
}

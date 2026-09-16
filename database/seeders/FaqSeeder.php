<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        collect([
            [
                'question'   => 'Siapa saja yang dapat mengikuti festival ini?',
                'answer'     => 'Festival ini terbuka bagi filmmaker independen, pelajar, mahasiswa, komunitas film, rumah produksi, dan masyarakat umum sesuai dengan ketentuan kategori yang berlaku.',
                'sort_order' => 1,
                'is_active'  => true,
            ],
            [
                'question'   => 'Apakah saya bisa mendaftarkan lebih dari satu karya film?',
                'answer'     => 'Ya, Anda dapat mendaftarkan lebih dari satu karya film, selama memenuhi persyaratan masing-masing kategori.',
                'sort_order' => 2,
                'is_active'  => true,
            ],
            [
                'question'   => 'Bagaimana mekanisme proses seleksi film?',
                'answer'     => 'Setelah film tersubmit akan masuk tahap kurasi oleh tim kurator berdasarkan kualitas cerita, teknis, dan originalitas karya.',
                'sort_order' => 3,
                'is_active'  => true,
            ],
            [
                'question'   => 'Apakah ada biaya pendaftaran?',
                'answer'     => 'Proses submission tidak dikenakan biaya.',
                'sort_order' => 4,
                'is_active'  => true,
            ],
            [
                'question'   => 'Apakah film yang pernah dipublikasikan sebelumnya dapat didaftarkan?',
                'answer'     => 'Film yang telah dipublikasikan secara umum, termasuk melalui platform digital atau pemutaran komersial, dapat didaftarkan sesuai dengan ketentuan festival yang berlaku.',
                'sort_order' => 5,
                'is_active'  => true,
            ],
            [
                'question'   => 'Kapan pengumuman Official Selection dilakukan?',
                'answer'     => 'Pengumuman karya yang lolos seleksi resmi akan disampaikan melalui website dan media sosial resmi festival sesuai jadwal yang telah ditentukan.',
                'sort_order' => 6,
                'is_active'  => true,
            ],
            [
                'question'   => 'Apakah peserta akan memperoleh sertifikat?',
                'answer'     => 'Peserta yang karyanya masuk dalam Official Selection akan memperoleh sertifikat partisipasi dalam bentuk digital atau fisik sesuai kebijakan panitia.',
                'sort_order' => 7,
                'is_active'  => true,
            ],
            [
                'question'   => 'Bagaimana jika mengalami kendala teknis saat submission?',
                'answer'     => 'Peserta dapat menghubungi panitia melalui email atau kontak resmi yang tercantum pada website festival untuk mendapatkan bantuan lebih lanjut.',
                'sort_order' => 8,
                'is_active'  => true,
            ],
        ])->each(function ($faq) {
            Faq::updateOrCreate(
                ['question' => $faq['question']],
                $faq
            );
        });
    }
}
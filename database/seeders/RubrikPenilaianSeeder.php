<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ReviewRubric;
use Illuminate\Database\Seeder;

class RubrikPenilaianSeeder extends Seeder
{
    public function run()
    {
        collect($this->rubrics())->each(function ($stages, $categorySlug) {
            $category = Category::where('slug', $categorySlug)->first();

            if (!$category) {
                $this->command->warn('Kategori tidak ditemukan: ' . $categorySlug);
                return;
            }

            collect($stages)->each(function ($groups, $stage) use ($category) {
                $rubric = ReviewRubric::updateOrCreate(
                    [
                        'category_id' => $category->id,
                        'stage' => $stage,
                    ],
                    [
                        'name' => $category->name . ' - ' . (ReviewRubric::stageLabels()[$stage] ?? ucfirst($stage)),
                        'is_active' => true,
                    ]
                );

                $rubric->groups()->delete();

                foreach ($groups as $groupIndex => $groupData) {
                    $group = $rubric->groups()->create([
                        'title' => $groupData['title'],
                        'weight' => $groupData['weight'],
                        'sort_order' => $groupIndex,
                    ]);

                    foreach ($groupData['items'] as $itemIndex => $itemData) {
                        $group->items()->create([
                            'title' => $itemData['title'],
                            'description' => $itemData['description'],
                            'weight' => $itemData['weight'],
                            'sort_order' => $itemIndex,
                        ]);
                    }
                }
            });
        });
    }

    protected function rubrics()
    {
        return [
            'umum-nasional' => [
                ReviewRubric::STAGE_CURATION => $this->curationUmum(),
                ReviewRubric::STAGE_JURY => $this->juryUmum(),
            ],
            'pelajar-jawa-timur' => [
                ReviewRubric::STAGE_CURATION => $this->curationEksibisiPelajar(),
                ReviewRubric::STAGE_JURY => $this->juryDenganKreativitasAlat(),
            ],
            'organisasi-komunitas-pacitan' => [
                ReviewRubric::STAGE_CURATION => $this->curationEksibisiPelajar(),
                ReviewRubric::STAGE_JURY => $this->juryDenganKreativitasAlat(),
            ],
            'pelajar-sd-smp-pacitan' => [
                ReviewRubric::STAGE_CURATION => $this->curationEksibisiPelajar(),
                ReviewRubric::STAGE_JURY => $this->juryDenganKreativitasAlat(),
            ],
        ];
    }

    protected function curationUmum()
    {
        return [
            [
                'title' => 'NARASI & SKENARIO',
                'weight' => 50,
                'items' => [
                    [
                        'title' => 'Orisinalitas & Tema',
                        'description' => 'Keunikan ide, kejelasan pesan yang disampaikan, serta kesesuaian dengan tema.',
                        'weight' => 20,
                    ],
                    [
                        'title' => 'Struktur Cerita',
                        'description' => 'Plot yang padat, efektif, dan memiliki perkembangan yang logis dan menarik dalam durasi pendek.',
                        'weight' => 15,
                    ],
                    [
                        'title' => 'Karakterisasi',
                        'description' => 'Kedalaman, konsistensi, dan perkembangan karakter, serta penggunaan dialog tepat guna.',
                        'weight' => 15,
                    ],
                ],
            ],
            $this->directingGroup(),
            $this->technicalGroup(),
        ];
    }

    protected function curationEksibisiPelajar()
    {
        return [
            [
                'title' => 'NARASI & SKENARIO',
                'weight' => 50,
                'items' => [
                    [
                        'title' => 'Orisinalitas & Tema',
                        'description' => 'Keunikan ide, kejelasan pesan yang disampaikan, serta kesesuaian dengan tema.',
                        'weight' => 15,
                    ],
                    [
                        'title' => 'Struktur Cerita',
                        'description' => 'Plot yang padat, efektif, dan memiliki perkembangan yang logis dan menarik dalam durasi pendek.',
                        'weight' => 10,
                    ],
                    [
                        'title' => 'Karakterisasi',
                        'description' => 'Kedalaman, konsistensi, dan perkembangan karakter, serta penggunaan dialog tepat guna.',
                        'weight' => 10,
                    ],
                    [
                        'title' => 'Pesan Moral',
                        'description' => 'Makna tersirat yang muncul di balik cerita film, bukan yang diucapkan langsung, tetapi yang dirasakan dan dimaknai penonton.',
                        'weight' => 15,
                    ],
                ],
            ],
            $this->directingGroup(),
            $this->technicalGroup(),
        ];
    }

    protected function directingGroup()
    {
        return [
            'title' => 'PENYUTRADARAAN',
            'weight' => 20,
            'items' => [
                [
                    'title' => 'Visi Sutradara',
                    'description' => 'Konsistensi tone dan mood, dan keberhasilan sutradara mewujudkan visi cerita ke dalam gambar.',
                    'weight' => 10,
                ],
                [
                    'title' => 'Pengarahan Aktor',
                    'description' => 'Kualitas akting, penjiwaan emosi, dan blocking aktor yang natural dan dinamis.',
                    'weight' => 10,
                ],
            ],
        ];
    }

    protected function technicalGroup()
    {
        return [
            'title' => 'TEKNIK & ESTETIKA',
            'weight' => 30,
            'items' => [
                [
                    'title' => 'Sinematografi',
                    'description' => 'Pengaturan framing yang sesuai dengan motivasi cerita yang dibangun dan kontinuitas visual yang menjaga alur cerita.',
                    'weight' => 5,
                ],
                [
                    'title' => 'Artistik',
                    'description' => 'Penggunaan latar, properti, kostum, dan penataan artistik yang mendukung narasi secara keseluruhan.',
                    'weight' => 5,
                ],
                [
                    'title' => 'Pencahayaan',
                    'description' => 'Penggunaan teknik pencahayaan yang efektif untuk membangun suasana.',
                    'weight' => 5,
                ],
                [
                    'title' => 'Editing Video',
                    'description' => 'Ritme pacing dan cutting yang halus, efektif, mendukung alur cerita, kontinuitas visual, dan color grading yang konsisten.',
                    'weight' => 5,
                ],
                [
                    'title' => 'Tata Suara & Musik',
                    'description' => 'Kualitas audio dialog yang jernih, sound design, dan kesesuaian ilustrasi musik.',
                    'weight' => 5,
                ],
                [
                    'title' => 'Kreativitas',
                    'description' => 'Pertimbangan terhadap keterbatasan sumber daya, kreativitas, dan keberanian bereksperimen dalam kondisi realistis.',
                    'weight' => 5,
                ],
            ],
        ];
    }

    protected function juryUmum()
    {
        return [
            [
                'title' => 'ASPEK PENILAIAN JURI',
                'weight' => null,
                'items' => [
                    $this->juryItemFear(),
                    $this->juryItemAtmosphere(),
                    $this->juryItemEmotion(),
                    [
                        'title' => 'Kreativitas',
                        'description' => 'Keberanian sutradara dalam menabrak konvensi genre: struktur non-linear, pendekatan dokumenter, surealisme, atau eksplorasi sensorik yang genuine.',
                        'weight' => 1,
                    ],
                    $this->juryItemCohesion(),
                    $this->juryItemAfterTaste(),
                ],
            ],
        ];
    }

    protected function juryDenganKreativitasAlat()
    {
        return [
            [
                'title' => 'ASPEK PENILAIAN JURI',
                'weight' => null,
                'items' => [
                    $this->juryItemFear(),
                    $this->juryItemAtmosphere(),
                    $this->juryItemEmotion(),
                    [
                        'title' => 'Kreativitas dengan alat sederhana',
                        'description' => 'Keberanian sutradara dalam menabrak konvensi genre, serta kreativitas penggunaan alat dan kondisi yang ada.',
                        'weight' => 1,
                    ],
                    $this->juryItemCohesion(),
                    $this->juryItemAfterTaste(),
                ],
            ],
        ];
    }

    protected function juryItemFear()
    {
        return [
            'title' => 'Rasa Takut Yang Bermakna',
            'description' => 'Film dinilai dari makna yang dibangun melalui rasa takut berdasarkan refleksi kehidupan atau isu sosial dan budaya yang menjadi sumber ketakutan kolektif.',
            'weight' => 1,
        ];
    }

    protected function juryItemAtmosphere()
    {
        return [
            'title' => 'Suasana Yang Terasa',
            'description' => 'Kekuatan penciptaan ruang dan suasana yang hidup, di mana unsur visual, suara, dan waktu berpadu membangun atmosfer yang menakutkan.',
            'weight' => 1,
        ];
    }

    protected function juryItemEmotion()
    {
        return [
            'title' => 'Kejujuran Emosi',
            'description' => 'Kemampuan film menghadirkan pengalaman emosional yang tulus dan manusiawi, bukan akting berlebihan, tetapi kejujuran terhadap rasa takut.',
            'weight' => 1,
        ];
    }

    protected function juryItemCohesion()
    {
        return [
            'title' => 'Keterpaduan Film',
            'description' => 'Perpaduan yang ideal antar unsur film: naskah, sinematografi, suara, akting, dan penyutradaraan bekerja menuju pengalaman rasa yang utuh.',
            'weight' => 1,
        ];
    }

    protected function juryItemAfterTaste()
    {
        return [
            'title' => 'After Taste',
            'description' => 'Jejak emosional dan intelektual yang tertinggal setelah film selesai, serta pertanyaan tentang diri dan dunia yang dapat menjadi bahan diskursus.',
            'weight' => 1,
        ];
    }
}

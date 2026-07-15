<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Film;
use App\Models\JuryScore;
use App\Models\SubmissionSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FilmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $submissionSettings = SubmissionSetting::query()
            ->whereIn('name', ['Periode Akhir 2025', 'Periode Awal 2026'])
            ->get()
            ->keyBy('name');

        $filmCategories = Category::query()
            ->whereIn('slug', [
                'umum-nasional',
                'pelajar-jawa-timur',
                'organisasi-komunitas-pacitan',
                'pelajar-sd-smp-pacitan',
            ])
            ->get()
            ->keyBy('slug');

        $participants = User::query()
            ->with('detail')
            ->whereIn('email', [
                'peserta.umum@gmail.com',
                'peserta.pelajar@gmail.com',
                'peserta.organisasi@gmail.com',
                'peserta.pacitan@gmail.com',
            ])
            ->get()
            ->keyBy('email');

        $previousPeriod = $submissionSettings->get('Periode Akhir 2025');
        $currentPeriod = $submissionSettings->get('Periode Awal 2026');

        if (!$previousPeriod || !$currentPeriod) {
            throw new \RuntimeException('Submission setting dependencies are missing for FilmSeeder.');
        }

        $posterPaths = [
            'posters/Xie7FPSHQiMa582yEFQcRrRI8BBTjwNm9tCiXBFC.jpg',
            'posters/gIZx6qHtMn1yK5w5wm5n3lTiMOiwgVdXLddNhTvO.png',
        ];

        $kruPaths = [
            'kru/YiM2JowQxQaxIdPTxpKMk8MUlrLFZUCNm8vJqBqA.png',
            'kru/jSMGOtuF7Chvz2GpBp971rUTQcEe5Auu6nun8f3M.jpg',
        ];

        $gsmPaths = json_encode([
            'gsm/p4W7CJBDzd1q8M3axWESfg6AwM0wNhWOfEZnfttZ.jpg',
            'gsm/zuogn9UGh5nMXU1F7fqWkT2v25ASWGV0eMre2kSk.jpg',
            'gsm/3soiHTd1SQjaDi5GXuTAT0Tebkizkne7zsM8j33y.jpg',
        ]);

        $featuredFilmIds = [];
        $filmCounter = 0;

        foreach ($this->winnerDefinitions() as $definition) {
            foreach ($definition['films'] as $filmData) {
                $participant = $participants->get($definition['user']);
                $category = $filmCategories->get($definition['category']);

                if (!$participant || !$category) {
                    throw new \RuntimeException('Film seeder dependencies are missing for winner film data.');
                }

                $film = Film::updateOrCreate(
                    [
                        'submission_setting_id' => $previousPeriod->id,
                        'name' => $filmData['name'],
                    ],
                    [
                        'user_id' => $participant->id,
                        'category_id' => $category->id,
                        'duration' => 900 + ($filmCounter * 10),
                        'tahun_produksi' => '2025',
                        'subtitle' => 'Ya',
                        'sinopsis' => 'Film ini mengeksplorasi horor sebagai pengalaman sosial, budaya, dan personal dalam konteks lokal yang kuat.',
                        'sutradara' => $filmData['sutradara'],
                        'produser' => $filmData['produser'],
                        'penulis' => $filmData['sutradara'],
                        'poster' => $posterPaths[$filmCounter % count($posterPaths)],
                        'gsm' => $gsmPaths,
                        'trailer' => 'https://example.com/trailer/' . Str::slug($filmData['name']),
                        'film' => 'https://example.com/film/' . Str::slug($filmData['name']),
                        'kru' => $kruPaths[$filmCounter % count($kruPaths)],
                        'other_1' => null,
                        'other_2' => null,
                        'status' => Film::CURATION_APPROVED,
                        'curation_status' => Film::CURATION_APPROVED,
                        'winner_rank' => $filmData['winner_rank'],
                        'created_at' => Carbon::parse('2025-08-20 12:00:00')->addMinutes($filmCounter),
                        'updated_at' => Carbon::parse('2025-09-12 12:00:00')->addMinutes($filmCounter),
                    ]
                );

                if ((int) $filmData['winner_rank'] <= 2) {
                    $featuredFilmIds[] = $film->id;
                }

                $filmCounter++;
            }
        }

        $currentPeriod->update([
            'last_year_featured_film_ids' => $featuredFilmIds,
        ]);

        foreach ($this->currentSubmissionDefinitions() as $index => $filmData) {
            $participant = $participants->get($filmData['email']);
            $category = $filmCategories->get($filmData['category']);

            if (!$participant || !$category) {
                throw new \RuntimeException('Film seeder dependencies are missing for current submission data.');
            }

            $film = Film::updateOrCreate(
                [
                    'submission_setting_id' => $currentPeriod->id,
                    'name' => $filmData['name'],
                ],
                [
                    'user_id' => $participant->id,
                    'category_id' => $category->id,
                    'duration' => 780 + ($index * 15),
                    'tahun_produksi' => '2026',
                    'subtitle' => 'Ya',
                    'sinopsis' => 'Submission aktif untuk keperluan uji alur kurasi dan penjurian.',
                    'sutradara' => $participant->name,
                    'produser' => optional($participant->detail)->community_name ?: $participant->name,
                    'penulis' => $participant->name,
                    'poster' => $posterPaths[$index % count($posterPaths)],
                    'gsm' => $gsmPaths,
                    'trailer' => 'https://example.com/trailer/current-' . ($index + 1),
                    'film' => 'https://example.com/film/current-' . ($index + 1),
                    'kru' => $kruPaths[$index % count($kruPaths)],
                    'other_1' => null,
                    'other_2' => null,
                    'status' => $filmData['status'],
                    'curation_status' => $filmData['status'],
                    'winner_rank' => null,
                    'created_at' => Carbon::parse('2026-02-10 09:00:00')->addHours($index),
                    'updated_at' => Carbon::parse('2026-02-10 09:00:00')->addHours($index),
                ]
            );

            if ($film->curation_status === Film::CURATION_APPROVED) {
                $jury = User::query()
                    ->where('role', 'juri')
                    ->where('category_id', $film->category_id)
                    ->first();

                if ($jury) {
                    JuryScore::updateOrCreate(
                        [
                            'film_id' => $film->id,
                            'jury_id' => $jury->id,
                        ],
                        [
                            'score' => 88,
                            'note' => 'Layak masuk shortlist.',
                        ]
                    );
                }
            }
        }
    }

    protected function winnerDefinitions()
    {
        return [
            [
                'category' => 'umum-nasional',
                'user' => 'peserta.umum@gmail.com',
                'films' => [
                    ['name' => 'Mama Minta Hotspot', 'sutradara' => 'Panji Respati', 'produser' => 'Kolong Sinema', 'winner_rank' => '1'],
                    ['name' => 'Terbang Terendam', 'sutradara' => 'Adamifa Sobirin', 'produser' => 'Ladamif Films', 'winner_rank' => '2'],
                    ['name' => 'Diam-Diam Ingin Aku Melawan', 'sutradara' => 'Muhammad Ibrahim', 'produser' => 'Moro-Moro Production', 'winner_rank' => '3'],
                    ['name' => 'Layar yang Retak', 'sutradara' => 'Rasyid Pradana', 'produser' => 'Ruang Tengah Pictures', 'winner_rank' => '4'],
                    ['name' => 'Doa di Ujung Gang', 'sutradara' => 'Nabilla Azzahra', 'produser' => 'Teras Malam Studio', 'winner_rank' => '5'],
                ],
            ],
            [
                'category' => 'pelajar-jawa-timur',
                'user' => 'peserta.pelajar@gmail.com',
                'films' => [
                    ['name' => 'Sajhen', 'sutradara' => 'Apriliansyah Salman Al Farisi Jakfar', 'produser' => 'SMK Negeri 1 Sumenep', 'winner_rank' => '1'],
                    ['name' => 'Pendakian Terakhir', 'sutradara' => 'Nanang Supriyono', 'produser' => 'MAN Pacitan', 'winner_rank' => '2'],
                    ['name' => 'Rumah Paling Sunyi', 'sutradara' => 'Vina Salsabila', 'produser' => 'SMKN 2 Ponorogo', 'winner_rank' => '3'],
                    ['name' => 'Jam Pulang Terakhir', 'sutradara' => 'Nadia Rahma Putri', 'produser' => 'SMAN 1 Ponorogo', 'winner_rank' => '4'],
                    ['name' => 'Kursi Kosong Belakang Kelas', 'sutradara' => 'Dewa Ayu Cintika', 'produser' => 'SMK Multimedia Kediri', 'winner_rank' => '5'],
                ],
            ],
            [
                'category' => 'organisasi-komunitas-pacitan',
                'user' => 'peserta.organisasi@gmail.com',
                'films' => [
                    ['name' => 'Wit Wiwit Kawit', 'sutradara' => 'Karang Taruna Kecamatan Bandar', 'produser' => 'Karang Taruna Kecamatan Bandar', 'winner_rank' => '1'],
                    ['name' => 'Seribu Bayangan', 'sutradara' => 'PKK & Karang Taruna Desa Candi', 'produser' => 'PKK & Karang Taruna Desa Candi', 'winner_rank' => '2'],
                    ['name' => 'Lorong Tengah Malam', 'sutradara' => 'Komunitas Srawung Pacitan', 'produser' => 'Komunitas Srawung Pacitan', 'winner_rank' => '3'],
                    ['name' => 'Suluk dari Selatan', 'sutradara' => 'Forum Pemuda Sudimoro', 'produser' => 'Forum Pemuda Sudimoro', 'winner_rank' => '4'],
                    ['name' => 'Ronda Terakhir', 'sutradara' => 'Karang Taruna Tegalombo', 'produser' => 'Karang Taruna Tegalombo', 'winner_rank' => '5'],
                ],
            ],
            [
                'category' => 'pelajar-sd-smp-pacitan',
                'user' => 'peserta.pacitan@gmail.com',
                'films' => [
                    ['name' => 'Sumpek', 'sutradara' => 'SMP Negeri 1 Arjosari', 'produser' => 'SMP Negeri 1 Arjosari', 'winner_rank' => '1'],
                    ['name' => 'Lampu Terakhir', 'sutradara' => 'SMP Negeri 2 Pacitan', 'produser' => 'SMP Negeri 2 Pacitan', 'winner_rank' => '2'],
                    ['name' => 'Lembayung di Kelas Tua', 'sutradara' => 'SDN Pacitan 3', 'produser' => 'SDN Pacitan 3', 'winner_rank' => '3'],
                    ['name' => 'Bisik dari Ruang UKS', 'sutradara' => 'SMP Negeri 3 Pacitan', 'produser' => 'SMP Negeri 3 Pacitan', 'winner_rank' => '4'],
                    ['name' => 'Bel Merah di Lorong Timur', 'sutradara' => 'SDIT Ar-Rahmah Pacitan', 'produser' => 'SDIT Ar-Rahmah Pacitan', 'winner_rank' => '5'],
                ],
            ],
        ];
    }

    protected function currentSubmissionDefinitions()
    {
        $assignments = [
            [
                'email' => 'peserta.umum@gmail.com',
                'category' => 'umum-nasional',
            ],
            [
                'email' => 'peserta.pelajar@gmail.com',
                'category' => 'pelajar-jawa-timur',
            ],
            [
                'email' => 'peserta.organisasi@gmail.com',
                'category' => 'organisasi-komunitas-pacitan',
            ],
            [
                'email' => 'peserta.pacitan@gmail.com',
                'category' => 'pelajar-sd-smp-pacitan',
            ],
        ];

        $titlesByStatus = [
            Film::CURATION_SUBMITTED => [
                'Malam di Rumah Tua',
                'Pintu yang Tidak Pernah Terkunci',
                'Tangga di Ujung Lorong',
            ],
            Film::CURATION_VERIFIED => [
                'Bel Pukul Dua Belas',
                'Jejak di Ruang Kelas',
                'Senter dari Halaman Belakang',
            ],
            Film::CURATION_UNDER_REVIEW => [
                'Bisik dari Jendela Timur',
                'Ritual di Balik Panggung',
                'Kursi yang Selalu Bergeser',
            ],
            Film::CURATION_DETERMINATION => [
                'Kabut di Depan Mushola',
                'Suara dari Gudang Serbaguna',
                'Bulan Merah di Atas Lapangan',
            ],
            Film::CURATION_APPROVED => [
                'Asap dari Kelas Kosong',
                'Telepon di Pos Ronda',
                'Lonceng Tanpa Menara',
            ],
            Film::CURATION_REJECTED => [
                'Bayang di Balik Tirai',
                'Peta Rumah yang Hilang',
                'Pukul Tiga di Koridor Selatan',
            ],
        ];

        $definitions = [];
        $statusIndex = 0;

        foreach ($titlesByStatus as $status => $titles) {
            foreach ($titles as $titleIndex => $title) {
                $assignment = $assignments[($statusIndex * count($titles) + $titleIndex) % count($assignments)];

                $definitions[] = [
                    'email' => $assignment['email'],
                    'category' => $assignment['category'],
                    'name' => $title,
                    'status' => $status,
                ];
            }

            $statusIndex++;
        }

        return $definitions;
    }
}

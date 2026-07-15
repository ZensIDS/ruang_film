<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $filmCategories = Category::query()
            ->whereIn('slug', [
                'umum-nasional',
                'pelajar-jawa-timur',
                'organisasi-komunitas-pacitan',
                'pelajar-sd-smp-pacitan',
            ])
            ->get()
            ->keyBy('slug');

        foreach ($this->systemUsers() as $userData) {
            $category = $userData['category_slug']
                ? $filmCategories->get($userData['category_slug'])
                : null;

            User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => $this->defaultPasswordHash(),
                    'role' => $userData['role'],
                    'category_id' => optional($category)->id,
                    'no_hp' => $userData['no_hp'],
                ]
            );
        }

        collect($this->participants())->each(function ($participant) use ($filmCategories) {
            $category = $filmCategories->get($participant['category']);

            $user = User::updateOrCreate(
                ['email' => $participant['email']],
                [
                    'name' => $participant['name'],
                    'email' => $participant['email'],
                    'password' => $this->defaultPasswordHash(),
                    'role' => 'peserta',
                    'no_hp' => $participant['no_hp'],
                    'category_id' => optional($category)->id,
                ]
            );

            UserDetail::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'community_name' => $participant['community_name'],
                    'provinsi_code' => '35',
                    'provinsi_name' => 'Jawa Timur',
                    'kabupaten_code' => '3501',
                    'kabupaten_name' => 'Pacitan',
                    'kecamatan_code' => '3501010',
                    'kecamatan_name' => 'Pacitan',
                    'desa_code' => '3501010001',
                    'desa_name' => 'Sidoharjo',
                    'username_ig' => $participant['username_ig'],
                    'posisi' => 'Perwakilan',
                    'alamat_lengkap' => $participant['alamat_lengkap'],
                    'tanggal_lahir' => '2000-01-01',
                ]
            );
        });
    }

    protected function systemUsers()
    {
        return [
            [
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'role' => 'admin',
                'category_slug' => null,
                'no_hp' => '081111111111',
            ],
            [
                'name' => 'Superadmin FFH',
                'email' => 'adminffh@gmail.com',
                'role' => 'admin',
                'category_slug' => null,
                'no_hp' => '081111111112',
            ],
            [
                'name' => 'Admin Submission FFH',
                'email' => 'adminsub@gmail.com',
                'role' => 'adminsub',
                'category_slug' => null,
                'no_hp' => '081111111113',
            ],
            [
                'name' => 'Kurator Festival',
                'email' => 'kurator@gmail.com',
                'role' => 'kurator',
                'category_slug' => null,
                'no_hp' => '081111111114',
            ],
            [
                'name' => 'Juri Umum Nasional',
                'email' => 'juri.umum@gmail.com',
                'role' => 'juri',
                'category_slug' => 'umum-nasional',
                'no_hp' => '081111111115',
            ],
            [
                'name' => 'Juri Pelajar Regional',
                'email' => 'juri.pelajar@gmail.com',
                'role' => 'juri',
                'category_slug' => 'pelajar-jawa-timur',
                'no_hp' => '081111111116',
            ],
            [
                'name' => 'Juri Organisasi Pacitan',
                'email' => 'juri.organisasi@gmail.com',
                'role' => 'juri',
                'category_slug' => 'organisasi-komunitas-pacitan',
                'no_hp' => '081111111117',
            ],
            [
                'name' => 'Juri Pelajar Pacitan',
                'email' => 'juri.paud@gmail.com',
                'role' => 'juri',
                'category_slug' => 'pelajar-sd-smp-pacitan',
                'no_hp' => '081111111118',
            ],
        ];
    }

    protected function participants()
    {
        return [
            [
                'name' => 'Kolong Sinema',
                'email' => 'peserta.umum@gmail.com',
                'category' => 'umum-nasional',
                'no_hp' => '082111111111',
                'community_name' => 'Kolong Sinema',
                'username_ig' => 'kolongsinema',
                'alamat_lengkap' => 'Jl. Mawar No. 10, Surabaya',
            ],
            [
                'name' => 'SMK Negeri 1 Sumenep',
                'email' => 'peserta.pelajar@gmail.com',
                'category' => 'pelajar-jawa-timur',
                'no_hp' => '082111111112',
                'community_name' => 'SMK Negeri 1 Sumenep',
                'username_ig' => 'smkn1sumenep',
                'alamat_lengkap' => 'Jl. Pendidikan No. 5, Sumenep',
            ],
            [
                'name' => 'Karang Taruna Kecamatan Bandar',
                'email' => 'peserta.organisasi@gmail.com',
                'category' => 'organisasi-komunitas-pacitan',
                'no_hp' => '082111111113',
                'community_name' => 'Karang Taruna Kecamatan Bandar',
                'username_ig' => 'ktbandar',
                'alamat_lengkap' => 'Jl. Raya Bandar No. 8, Pacitan',
            ],
            [
                'name' => 'SMP Negeri 1 Arjosari',
                'email' => 'peserta.pacitan@gmail.com',
                'category' => 'pelajar-sd-smp-pacitan',
                'no_hp' => '082111111114',
                'community_name' => 'SMP Negeri 1 Arjosari',
                'username_ig' => 'smpn1arjosari',
                'alamat_lengkap' => 'Jl. Arjosari No. 12, Pacitan',
            ],
        ];
    }

    protected function defaultPasswordHash()
    {
        return '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    }
}

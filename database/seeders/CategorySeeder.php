<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        collect($this->categories())->each(function ($category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        });
    }

    protected function categories()
    {
        return [
            [
                'name' => 'Umum Nasional',
                'slug' => 'umum-nasional',
                'description' => 'Kompetisi film horor terbuka bagi sineas Indonesia dari berbagai latar belakang.',
                'landing_summary' => 'Kompetisi film horor terbuka bagi sineas Indonesia dari berbagai latar belakang.',
                'image' => 'landing/images/kategori/UMUM.png',
                'detail_route' => '/umum',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Pelajar Se - Jawa Timur',
                'slug' => 'pelajar-jawa-timur',
                'description' => 'Kompetisi film horor bagi pelajar SMA/SMK wilayah Jawa Timur.',
                'landing_summary' => 'Kompetisi film horor bagi pelajar SMA/SMK wilayah Jawa Timur.',
                'image' => 'landing/images/kategori/PELAJAR REGIONAL.png',
                'detail_route' => '/pelajar',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Organisasi & Komunitas Lokal Pacitan',
                'slug' => 'organisasi-komunitas-pacitan',
                'description' => 'Kategori untuk organisasi lokal Pacitan seperti PKK, PAUD/TK, Karang Taruna, dan komunitas.',
                'landing_summary' => 'Kategori eksibisi untuk organisasi lokal Pacitan, PKK, PAUD/TK, Karang Taruna, dan komunitas.',
                'image' => 'landing/images/kategori/EKSIBISI.png',
                'detail_route' => '/ekshibisi/organisasi',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Pelajar SD - SMP Se-Pacitan',
                'slug' => 'pelajar-sd-smp-pacitan',
                'description' => 'Kategori eksibisi untuk pelajar SD hingga SMP di Pacitan.',
                'landing_summary' => 'Kategori eksibisi untuk pelajar SD hingga SMP di Pacitan.',
                'image' => 'landing/images/kategori/EKSIBISI.png',
                'detail_route' => '/ekshibisi/paud',
                'sort_order' => 4,
                'is_active' => true,
            ],
        ];
    }
}

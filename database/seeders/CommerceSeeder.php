<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use App\Models\Expedition;
use App\Models\Merchandise;
use App\Models\MerchandiseCategory;
use Illuminate\Database\Seeder;

class CommerceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $merchandiseCategories = collect($this->merchandiseCategories())
            ->mapWithKeys(function ($category) {
                return [
                    $category['slug'] => MerchandiseCategory::updateOrCreate(
                        ['slug' => $category['slug']],
                        $category
                    ),
                ];
            });

        foreach ($this->merchandises() as $item) {
            Merchandise::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'merchandise_category_id' => $merchandiseCategories[$item['category']]->id,
                    'name' => $item['name'],
                    'slug' => $item['slug'],
                    'image' => $item['image'],
                    'price' => $item['price'],
                    'discount_price' => $item['discount_price'],
                    'weight' => $item['weight'],
                    'qty_stock' => $item['qty_stock'],
                    'summary' => $item['summary'],
                    'description' => $item['description'],
                    'is_active' => $item['is_active'],
                ]
            );
        }

        foreach ($this->expeditions() as $expedition) {
            Expedition::updateOrCreate(
                ['name' => $expedition['name'], 'service_name' => $expedition['service_name']],
                $expedition
            );
        }

        foreach ($this->bankAccounts() as $bankAccount) {
            BankAccount::updateOrCreate(
                ['rek_bank_name' => $bankAccount['rek_bank_name'], 'rek_bank_no' => $bankAccount['rek_bank_no']],
                $bankAccount
            );
        }
    }

    protected function merchandiseCategories()
    {
        return [
            [
                'name' => 'Apparel',
                'slug' => 'apparel',
                'description' => 'Merchandise pakaian resmi Ruang Film',
                'is_active' => true,
            ],
            [
                'name' => 'Aksesoris',
                'slug' => 'aksesoris',
                'description' => 'Aksesoris resmi festival',
                'is_active' => true,
            ],
            [
                'name' => 'Collectibles',
                'slug' => 'collectibles',
                'description' => 'Koleksi festival dan item edisi terbatas',
                'is_active' => true,
            ],
        ];
    }

    protected function merchandises()
    {
        return [
            [
                'category' => 'apparel',
                'name' => 'Official Festival T-Shirt',
                'slug' => 'official-festival-t-shirt',
                'price' => 120000,
                'discount_price' => 100000,
                'weight' => 250,
                'qty_stock' => 20,
                'summary' => 'Kaos resmi festival Ruang Film',
                'description' => 'Kaos official dengan bahan cotton combed.',
                'image' => null,
                'is_active' => true,
            ],
            [
                'category' => 'aksesoris',
                'name' => 'Festival Tote Bag',
                'slug' => 'festival-tote-bag',
                'price' => 85000,
                'discount_price' => null,
                'weight' => 150,
                'qty_stock' => 15,
                'summary' => 'Tote bag resmi festival',
                'description' => 'Tote bag koleksi untuk peserta dan penonton.',
                'image' => null,
                'is_active' => true,
            ],
            [
                'category' => 'collectibles',
                'name' => 'Poster Festival Indigo',
                'slug' => 'poster-festival-indigo',
                'price' => 45000,
                'discount_price' => null,
                'weight' => 80,
                'qty_stock' => 30,
                'summary' => 'Poster resmi tema INDIGO',
                'description' => 'Poster festival ukuran A3 edisi 2026.',
                'image' => null,
                'is_active' => true,
            ],
        ];
    }

    protected function expeditions()
    {
        return [
            ['name' => 'JNE', 'external_code' => 'jne', 'service_name' => 'REG', 'fee' => 15000, 'is_active' => true],
            ['name' => 'J&T', 'external_code' => 'jnt', 'service_name' => 'REG', 'fee' => 18000, 'is_active' => true],
            ['name' => 'SiCepat', 'external_code' => 'sicepat', 'service_name' => 'BEST', 'fee' => 20000, 'is_active' => true],
        ];
    }

    protected function bankAccounts()
    {
        return [
            ['rek_name' => 'Ruang Film Pacitan', 'rek_bank_name' => 'BCA', 'rek_bank_no' => '1234567890', 'is_active' => true],
            ['rek_name' => 'Ruang Film Pacitan', 'rek_bank_name' => 'BRI', 'rek_bank_no' => '9988776655', 'is_active' => true],
        ];
    }
}

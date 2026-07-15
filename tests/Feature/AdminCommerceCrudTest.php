<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCommerceCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_merchandise_related_master_data()
    {
        Storage::fake('public');

        $admin = User::factory()->role('admin')->create();
        $filmCategory = Category::factory()->create();

        $this->actingAs($admin)
            ->post(route('merchandise-categories.store'), [
                'name' => 'Merch Category',
                'description' => 'Kategori merch',
                'is_active' => 1,
            ])
            ->assertRedirect(route('merchandise-categories.index'));

        $this->actingAs($admin)
            ->post(route('expeditions.store'), [
                'name' => 'JNE',
                'external_code' => 'jne',
                'service_name' => 'REG',
                'fee' => 15000,
                'is_active' => 1,
            ])
            ->assertRedirect(route('expeditions.index'));

        $this->actingAs($admin)
            ->post(route('bank-accounts.store'), [
                'rek_name' => 'Ruang Film',
                'rek_bank_name' => 'BCA',
                'rek_bank_no' => '1234567890',
                'is_active' => 1,
            ])
            ->assertRedirect(route('bank-accounts.index'));

        $merchCategoryId = \App\Models\MerchandiseCategory::first()->id;

        $this->actingAs($admin)
            ->post(route('admin-merchandises.store'), [
                'merchandise_category_id' => $merchCategoryId,
                'name' => 'Official T-Shirt',
                'price' => 120000,
                'discount_price' => 100000,
                'weight' => 250,
                'qty_stock' => 10,
                'summary' => 'Ringkas',
                'description' => 'Deskripsi',
                'image' => UploadedFile::fake()->image('shirt.jpg'),
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin-merchandises.index'));

        $this->assertDatabaseHas('merchandise_categories', ['name' => 'Merch Category']);
        $this->assertDatabaseHas('expeditions', ['name' => 'JNE', 'external_code' => 'jne', 'service_name' => 'REG']);
        $this->assertDatabaseHas('bank_accounts', ['rek_bank_name' => 'BCA']);
        $this->assertDatabaseHas('merchandises', ['name' => 'Official T-Shirt']);
        Storage::disk('public')->assertExists(\App\Models\Merchandise::first()->image);
    }
}

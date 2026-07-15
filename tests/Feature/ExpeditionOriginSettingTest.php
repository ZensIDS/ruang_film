<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Province;
use Tests\TestCase;

class ExpeditionOriginSettingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.rajaongkir.base_url' => 'https://rajaongkir.komerce.id/api/v1',
            'services.rajaongkir.api_key_shipping_cost' => 'cost-key',
            'services.rajaongkir.fallback_origin_destination_id' => '3855',
            'services.rajaongkir.legacy_origin_district_id' => '3855',
        ]);
    }

    public function test_admin_can_search_and_save_laravolt_origin_with_auto_matched_rajaongkir_id()
    {
        $admin = User::factory()->role('admin')->create();
        $this->seedLaravoltOriginRows();

        Http::fake([
            'https://rajaongkir.komerce.id/api/v1/destination/domestic-destination*' => Http::response([
                'meta' => ['code' => 200],
                'data' => [
                    [
                        'id' => 40561,
                        'label' => 'PACITAN, PACITAN, PACITAN, JAWA TIMUR, 63512',
                        'province_name' => 'JAWA TIMUR',
                        'city_name' => 'PACITAN',
                        'district_name' => 'PACITAN',
                        'subdistrict_name' => 'PACITAN',
                        'zip_code' => '63512',
                    ],
                    [
                        'id' => 40552,
                        'label' => 'BALEHARJO, PACITAN, PACITAN, JAWA TIMUR, 63511',
                        'province_name' => 'JAWA TIMUR',
                        'city_name' => 'PACITAN',
                        'district_name' => 'PACITAN',
                        'subdistrict_name' => 'BALEHARJO',
                        'zip_code' => '63511',
                    ],
                ],
            ], 200),
        ]);

        $this->actingAs($admin)
            ->getJson(route('expeditions.origin.laravolt-search', ['keyword' => 'Pacitan']))
            ->assertOk()
            ->assertJsonPath('data.0.district_code', '3501010');

        $this->actingAs($admin)
            ->post(route('expeditions.origin.laravolt'), [
                'district_code' => '3501010',
            ])
            ->assertRedirect(route('expeditions.index'));

        $this->assertSame('3501010', AppSetting::getValue('shipping_origin_laravolt_district_code'));
        $this->assertSame('PACITAN', AppSetting::getValue('shipping_origin_laravolt_district_name'));
        $this->assertSame('40561', AppSetting::getValue('shipping_origin_laravolt_auto_destination_id'));
        $this->assertSame('40561', AppSetting::shippingOriginDestinationId());
        $this->assertSame('', AppSetting::getValue('shipping_origin_rajaongkir_destination_id', ''));
    }

    public function test_admin_can_search_and_save_rajaongkir_backup_origin()
    {
        $admin = User::factory()->role('admin')->create();

        Http::fake([
            'https://rajaongkir.komerce.id/api/v1/destination/domestic-destination*' => Http::response([
                'meta' => ['code' => 200],
                'data' => [[
                    'id' => 17486,
                    'label' => 'KEBON JERUK, KEBON JERUK, JAKARTA BARAT, DKI JAKARTA, 11530',
                    'province_name' => 'DKI JAKARTA',
                    'city_name' => 'JAKARTA BARAT',
                    'district_name' => 'KEBON JERUK',
                    'subdistrict_name' => 'KEBON JERUK',
                    'zip_code' => '11530',
                ]],
            ], 200),
        ]);

        $this->actingAs($admin)
            ->getJson(route('expeditions.origin.rajaongkir-search', ['keyword' => 'Kebon Jeruk']))
            ->assertOk()
            ->assertJsonPath('data.0.id', '17486');

        $this->actingAs($admin)
            ->post(route('expeditions.origin.rajaongkir'), [
                'destination_id' => '17486',
                'destination_label' => 'KEBON JERUK, KEBON JERUK, JAKARTA BARAT, DKI JAKARTA, 11530',
            ])
            ->assertRedirect(route('expeditions.index'));

        $this->assertSame('17486', AppSetting::getValue('shipping_origin_rajaongkir_destination_id'));
        $this->assertSame('17486', AppSetting::shippingOriginDestinationId());
    }

    protected function seedLaravoltOriginRows()
    {
        Province::create([
            'code' => '35',
            'name' => 'JAWA TIMUR',
        ]);

        City::create([
            'code' => '3501',
            'province_code' => '35',
            'name' => 'KABUPATEN PACITAN',
        ]);

        District::create([
            'code' => '3501010',
            'city_code' => '3501',
            'name' => 'PACITAN',
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\Category;
use App\Models\Expedition;
use App\Models\Merchandise;
use App\Models\MerchandiseCategory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Province;
use Laravolt\Indonesia\Models\Village;
use Tests\TestCase;

class CheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_creates_invoice_uses_payment_due_setting_and_decrements_stock()
    {
        $filmCategory = Category::factory()->create();
        $user = User::factory()->create([
            'role' => 'peserta',
            'category_id' => $filmCategory->id,
        ]);
        UserDetail::factory()->create(['user_id' => $user->id]);

        $merchCategory = MerchandiseCategory::factory()->create();
        $merchandise = Merchandise::factory()->create([
            'merchandise_category_id' => $merchCategory->id,
            'price' => 120000,
            'qty_stock' => 10,
        ]);
        $expedition = Expedition::factory()->create([
            'external_code' => 'jne',
            'fee' => 99000,
        ]);

        AppSetting::factory()->create([
            'key' => 'payment_due_hours',
            'value' => '48',
        ]);

        $this->fakeSuccessfulShippingLookup(15000, 'REG', 'jne');

        $this->actingAs($user)
            ->post(route('cart.store', $merchandise), ['quantity' => 2])
            ->assertRedirect();

        $response = $this->actingAs($user)
            ->post(route('checkout.store'), [
                'selected_shipping_option' => 'jne|reg',
                'postal_code' => '63511',
                'notes' => 'Tolong kirim cepat',
            ]);

        $order = Order::first();

        $response->assertRedirect(route('orders.show', $order));
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_WAITING_PAYMENT,
            'subtotal' => 240000,
            'shipping_fee' => 15000,
            'total' => 255000,
            'expedition_id' => $expedition->id,
            'expedition_code' => 'jne',
            'expedition_service_code' => 'reg',
            'shipping_destination_id' => '68424',
        ]);
        $this->assertEquals(2, $order->items()->sum('quantity'));
        $this->assertTrue($order->payment_due_at->between(now()->addHours(47), now()->addHours(49)));
        $this->assertSame(8, $merchandise->fresh()->qty_stock);
        $this->assertSame(0, $user->cart()->first()->items()->count());
    }

    public function test_general_buyer_can_fill_shipping_biodata_inline_during_checkout()
    {
        $user = User::factory()->create([
            'role' => 'umum',
            'category_id' => null,
            'email' => 'buyer-inline@gmail.com',
        ]);

        $merchCategory = MerchandiseCategory::factory()->create();
        $merchandise = Merchandise::factory()->create([
            'merchandise_category_id' => $merchCategory->id,
            'price' => 120000,
            'qty_stock' => 10,
        ]);
        $expedition = Expedition::factory()->create([
            'external_code' => 'jne',
            'fee' => 15000,
        ]);

        $this->fakeSuccessfulShippingLookup(15000, 'REG', 'jne');

        $this->actingAs($user)
            ->post(route('cart.store', $merchandise), ['quantity' => 1])
            ->assertRedirect();

        $this->actingAs($user)
            ->get(route('checkout.show'))
            ->assertOk()
            ->assertSee('Biodata Pengiriman');

        $response = $this->actingAs($user)
            ->post(route('checkout.store'), [
                'name' => 'Buyer Inline',
                'email' => 'buyer-inline@gmail.com',
                'no_hp' => '081234567890',
                'provinsi_code' => '35',
                'provinsi_name' => 'JAWA TIMUR',
                'kabupaten_code' => '3501',
                'kabupaten_name' => 'KABUPATEN PACITAN',
                'kecamatan_code' => '3501010',
                'kecamatan_name' => 'PACITAN',
                'desa_code' => '3501010001',
                'desa_name' => 'BALEHARJO',
                'alamat_lengkap' => 'Jl. Pantai Selatan No. 1',
                'selected_shipping_option' => 'jne|reg',
                'postal_code' => '63511',
            ]);

        $order = Order::first();

        $response->assertRedirect(route('orders.show', $order));
        $this->assertDatabaseHas('user_details', [
            'user_id' => $user->id,
            'provinsi_name' => 'JAWA TIMUR',
            'alamat_lengkap' => 'Jl. Pantai Selatan No. 1',
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'recipient_name' => 'Buyer Inline',
            'full_address' => 'Jl. Pantai Selatan No. 1, BALEHARJO, PACITAN, KABUPATEN PACITAN, JAWA TIMUR',
            'expedition_id' => $expedition->id,
        ]);
    }

    public function test_general_buyer_can_checkout_with_self_pickup_without_shipping_lookup()
    {
        Http::fake();

        $user = User::factory()->create([
            'role' => 'umum',
            'category_id' => null,
            'email' => 'pickup-buyer@gmail.com',
        ]);

        $merchCategory = MerchandiseCategory::factory()->create();
        $merchandise = Merchandise::factory()->create([
            'merchandise_category_id' => $merchCategory->id,
            'price' => 85000,
            'qty_stock' => 5,
        ]);

        $this->actingAs($user)
            ->post(route('cart.store', $merchandise), ['quantity' => 1])
            ->assertRedirect();

        $response = $this->actingAs($user)
            ->post(route('checkout.store'), [
                'name' => 'Pickup Buyer',
                'email' => 'pickup-buyer@gmail.com',
                'no_hp' => '081234567890',
                'alamat_lengkap' => 'Saya ambil sendiri',
                'selected_shipping_option' => 'pickup_basecamp',
                'notes' => 'Nanti diambil ke basecamp',
            ]);

        $order = Order::first();

        $response->assertRedirect(route('orders.show', $order));
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'expedition_id' => null,
            'expedition_code' => 'pickup',
            'expedition_name' => 'Ambil di Basecamp',
            'shipping_fee' => 0,
            'total' => 85000,
            'full_address' => 'Ambil di Basecamp',
        ]);
        $this->assertDatabaseHas('user_details', [
            'user_id' => $user->id,
            'alamat_lengkap' => 'Saya ambil sendiri',
            'provinsi_name' => '',
            'kabupaten_name' => '',
            'kecamatan_name' => '',
            'desa_name' => '',
        ]);
        Http::assertNothingSent();
    }

    public function test_general_buyer_can_save_biodata_with_selected_rajaongkir_destination()
    {
        $user = User::factory()->create([
            'role' => 'umum',
            'category_id' => null,
            'email' => 'buyer-biodata@gmail.com',
        ]);

        $response = $this->actingAs($user)
            ->post(route('user-detail.save'), [
                'name' => 'Buyer Biodata',
                'email' => 'buyer-biodata@gmail.com',
                'no_hp' => '081234567890',
                'shipping_destination_id' => '68424',
                'shipping_destination_label' => 'Minomartani, Ngaglik, Kabupaten Sleman, DI Yogyakarta',
                'provinsi_name' => 'DI Yogyakarta',
                'kabupaten_name' => 'Kabupaten Sleman',
                'kecamatan_name' => 'Ngaglik',
                'desa_name' => 'Minomartani',
                'alamat_lengkap' => 'Jl. Palagan No. 7',
            ]);

        $response->assertRedirect(route('user-detail.index'));
        $this->assertDatabaseHas('user_details', [
            'user_id' => $user->id,
            'provinsi_code' => '',
            'kabupaten_code' => '',
            'kecamatan_code' => '',
            'desa_code' => '',
            'provinsi_name' => 'DI Yogyakarta',
            'kabupaten_name' => 'Kabupaten Sleman',
            'kecamatan_name' => 'Ngaglik',
            'desa_name' => 'Minomartani',
            'alamat_lengkap' => 'Jl. Palagan No. 7',
        ]);
    }

    public function test_participant_biodata_keeps_location_selects_editable_for_regional_categories()
    {
        $categories = Category::factory()->count(4)->create();
        $user = User::factory()->create([
            'role' => 'peserta',
            'category_id' => $categories->last()->id,
        ]);
        UserDetail::factory()->create([
            'user_id' => $user->id,
            'provinsi_code' => '35',
            'provinsi_name' => 'JAWA TIMUR',
            'kabupaten_code' => '3501',
            'kabupaten_name' => 'KABUPATEN PACITAN',
        ]);

        $response = $this->actingAs($user)->get(route('user-detail.index'));

        $response->assertOk()
            ->assertSee('<select name="provinsi_code" id="provinsi" class="field-input" required>', false)
            ->assertSee('<select name="kabupaten_code" id="kabupaten" class="field-input" required>', false)
            ->assertDontSee('value="KABUPATEN PACITAN" disabled', false);
    }

    public function test_participant_biodata_page_embeds_saved_location_metadata_for_restore()
    {
        $category = Category::factory()->create();
        $user = User::factory()->create([
            'role' => 'peserta',
            'category_id' => $category->id,
        ]);
        $detail = UserDetail::factory()->create([
            'user_id' => $user->id,
            'provinsi_code' => '35',
            'provinsi_name' => 'JAWA TIMUR',
            'kabupaten_code' => '3501',
            'kabupaten_name' => 'KABUPATEN PACITAN',
            'kecamatan_code' => '3501010',
            'kecamatan_name' => 'PACITAN',
            'desa_code' => '3501010001',
            'desa_name' => 'BALEHARJO',
        ]);

        $response = $this->actingAs($user)->get(route('user-detail.index'));

        $response->assertOk()
            ->assertSee('const existingKabupaten = ' . json_encode($detail->kabupaten_code) . ';', false)
            ->assertSee('const existingKabupatenName = ' . json_encode($detail->kabupaten_name) . ';', false)
            ->assertSee('const existingKecamatan = ' . json_encode($detail->kecamatan_code) . ';', false)
            ->assertSee('const existingKecamatanName = ' . json_encode($detail->kecamatan_name) . ';', false)
            ->assertSee('const existingDesaCode = ' . json_encode($detail->desa_code) . ';', false)
            ->assertSee('const existingDesaName = ' . json_encode($detail->desa_name) . ';', false)
            ->assertSee('restoreParticipantLocation().catch', false);
    }

    public function test_shipping_options_endpoint_returns_live_quotes_for_valid_address()
    {
        $user = User::factory()->role('umum')->create();
        $merchandise = Merchandise::factory()->create([
            'qty_stock' => 5,
            'weight' => 400,
        ]);

        Expedition::factory()->create([
            'name' => 'JNE',
            'external_code' => 'jne',
            'is_active' => true,
        ]);

        $this->fakeSuccessfulShippingLookup(21000, 'REG', 'jne');

        $this->actingAs($user)
            ->post(route('cart.store', $merchandise), ['quantity' => 1]);

        $response = $this->actingAs($user)
            ->postJson(route('checkout.shipping-options'), [
                'provinsi_code' => '35',
                'provinsi_name' => 'JAWA TIMUR',
                'kabupaten_code' => '3501',
                'kabupaten_name' => 'KABUPATEN PACITAN',
                'kecamatan_code' => '3501010',
                'kecamatan_name' => 'PACITAN',
                'desa_code' => '3501010001',
                'desa_name' => 'BALEHARJO',
                'postal_code' => '63511',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.destination.id', '68424')
            ->assertJsonPath('data.groups.0.courier_code', 'jne')
            ->assertJsonPath('data.groups.0.options.0.quote_id', 'jne|reg')
            ->assertJsonPath('data.groups.0.options.0.price', 21000);
    }

    public function test_shipping_options_endpoint_skips_same_origin_destination_id_and_uses_fallback_origin()
    {
        config([
            'services.rajaongkir.base_url' => 'https://rajaongkir.komerce.id/api/v1',
            'services.rajaongkir.origin_destination_id' => '68423',
            'services.rajaongkir.fallback_origin_destination_id' => '68423',
        ]);

        AppSetting::setValue('shipping_origin_laravolt_auto_destination_id', '68424');
        AppSetting::setValue('shipping_origin_rajaongkir_destination_id', '68424');

        $user = User::factory()->create([
            'role' => 'peserta',
        ]);
        UserDetail::factory()->create([
            'user_id' => $user->id,
            'provinsi_code' => '35',
            'provinsi_name' => 'JAWA TIMUR',
            'kabupaten_code' => '3501',
            'kabupaten_name' => 'KABUPATEN PACITAN',
            'kecamatan_code' => '3501010',
            'kecamatan_name' => 'PACITAN',
            'desa_code' => '3501010001',
            'desa_name' => 'BALEHARJO',
        ]);

        $merchandise = Merchandise::factory()->create([
            'qty_stock' => 5,
            'weight' => 400,
        ]);

        Expedition::factory()->create([
            'name' => 'JNE',
            'external_code' => 'jne',
            'is_active' => true,
        ]);

        Http::fake([
            'https://rajaongkir.komerce.id/api/v1/destination/domestic-destination*' => Http::response([
                'meta' => ['code' => 200],
                'data' => [[
                    'id' => '68424',
                    'label' => 'Baleharjo, Pacitan, Kabupaten Pacitan, Jawa Timur, 63511',
                    'province_name' => 'Jawa Timur',
                    'city_name' => 'Kabupaten Pacitan',
                    'subdistrict_name' => 'Pacitan',
                    'village_name' => 'Baleharjo',
                    'zip_code' => '63511',
                ]],
            ], 200),
            'https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost' => function ($request) {
                $this->assertSame('68423', (string) data_get($request->data(), 'origin'));

                return Http::response([
                    'meta' => ['code' => 200],
                    'data' => [[
                        'code' => 'jne',
                        'name' => 'JNE',
                        'service' => 'REG',
                        'description' => 'Regular Service',
                        'cost' => 21000,
                        'etd' => '2-3 day',
                    ]],
                ], 200);
            },
        ]);

        $this->actingAs($user)
            ->post(route('cart.store', $merchandise), ['quantity' => 1]);

        $response = $this->actingAs($user)
            ->postJson(route('checkout.shipping-options'), [
                'postal_code' => '63511',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.destination.id', '68424')
            ->assertJsonPath('data.groups.0.options.0.quote_id', 'jne|reg')
            ->assertJsonPath('data.groups.0.options.0.price', 21000);
    }

    public function test_shipping_options_endpoint_handles_participant_with_generic_saved_village_name()
    {
        config([
            'services.rajaongkir.base_url' => 'https://rajaongkir.komerce.id/api/v1',
            'services.rajaongkir.origin_destination_id' => '68423',
            'services.rajaongkir.fallback_origin_destination_id' => '68423',
        ]);

        AppSetting::setValue('shipping_origin_laravolt_auto_destination_id', '40561');
        AppSetting::setValue('shipping_origin_rajaongkir_destination_id', '40561');

        $user = User::factory()->create([
            'role' => 'peserta',
        ]);
        UserDetail::factory()->create([
            'user_id' => $user->id,
            'provinsi_code' => '35',
            'provinsi_name' => 'JAWA TIMUR',
            'kabupaten_code' => '3501',
            'kabupaten_name' => 'KABUPATEN PACITAN',
            'kecamatan_code' => '3501010',
            'kecamatan_name' => 'PACITAN',
            'desa_code' => '',
            'desa_name' => 'PACITAN',
        ]);

        $merchandise = Merchandise::factory()->create([
            'qty_stock' => 5,
            'weight' => 400,
        ]);

        Expedition::factory()->create([
            'name' => 'JNE',
            'external_code' => 'jne',
            'is_active' => true,
        ]);

        Http::fake([
            'https://rajaongkir.komerce.id/api/v1/destination/domestic-destination*' => Http::response([
                'meta' => ['code' => 200],
                'data' => [
                    [
                        'id' => '40561',
                        'label' => 'Pacitan, Pacitan, Kabupaten Pacitan, Jawa Timur, 63512',
                        'province_name' => 'Jawa Timur',
                        'city_name' => 'Kabupaten Pacitan',
                        'district_name' => 'Pacitan',
                        'subdistrict_name' => 'Pacitan',
                        'zip_code' => '63512',
                    ],
                    [
                        'id' => '40552',
                        'label' => 'Baleharjo, Pacitan, Kabupaten Pacitan, Jawa Timur, 63511',
                        'province_name' => 'Jawa Timur',
                        'city_name' => 'Kabupaten Pacitan',
                        'district_name' => 'Pacitan',
                        'subdistrict_name' => 'Pacitan',
                        'village_name' => 'Baleharjo',
                        'zip_code' => '63511',
                    ],
                ],
            ], 200),
            'https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost' => Http::response([
                'meta' => ['code' => 200],
                'data' => [[
                    'code' => 'jne',
                    'name' => 'JNE',
                    'service' => 'REG',
                    'description' => 'Regular Service',
                    'cost' => 21000,
                    'etd' => '2-3 day',
                ]],
            ], 200),
        ]);

        $this->actingAs($user)
            ->post(route('cart.store', $merchandise), ['quantity' => 1]);

        $this->actingAs($user)
            ->postJson(route('checkout.shipping-options'), [
                'postal_code' => '63512',
            ])
            ->assertOk()
            ->assertJsonPath('data.destination.id', '40561')
            ->assertJsonPath('data.groups.0.options.0.quote_id', 'jne|reg');
    }

    public function test_checkout_destination_search_returns_rajaongkir_results()
    {
        $user = User::factory()->role('umum')->create();

        Http::fake([
            'https://rajaongkir.komerce.id/api/v1/destination/domestic-destination*' => Http::response([
                'meta' => ['code' => 200],
                'data' => [[
                    'id' => '68424',
                    'label' => 'Minomartani, Ngaglik, Kabupaten Sleman, DI Yogyakarta, 55581',
                    'province_name' => 'DI Yogyakarta',
                    'city_name' => 'Kabupaten Sleman',
                    'district_name' => 'Ngaglik',
                    'subdistrict_name' => 'Ngaglik',
                    'village_name' => 'Minomartani',
                    'zip_code' => '55581',
                ]],
            ], 200),
        ]);

        $this->actingAs($user)
            ->getJson(route('checkout.destination-search', ['keyword' => 'Minomartani']))
            ->assertOk()
            ->assertJsonPath('data.0.id', '68424')
            ->assertJsonPath('data.0.city', 'Kabupaten Sleman')
            ->assertJsonPath('data.0.village', 'Minomartani');
    }

    public function test_shipping_options_endpoint_accepts_saved_rajaongkir_names_without_destination_id()
    {
        $user = User::factory()->role('umum')->create();
        UserDetail::factory()->create([
            'user_id' => $user->id,
            'provinsi_code' => '',
            'kabupaten_code' => '',
            'kecamatan_code' => '',
            'desa_code' => '',
            'provinsi_name' => 'DI Yogyakarta',
            'kabupaten_name' => 'Kabupaten Sleman',
            'kecamatan_name' => 'Ngaglik',
            'desa_name' => 'Minomartani',
        ]);

        $merchandise = Merchandise::factory()->create([
            'qty_stock' => 5,
            'weight' => 400,
        ]);

        Expedition::factory()->create([
            'name' => 'JNE',
            'external_code' => 'jne',
            'is_active' => true,
        ]);

        Http::fake([
            'https://rajaongkir.komerce.id/api/v1/destination/domestic-destination*' => Http::response([
                'meta' => ['code' => 200],
                'data' => [[
                    'id' => '68424',
                    'label' => 'Minomartani, Ngaglik, Kabupaten Sleman, DI Yogyakarta, 55581',
                    'province_name' => 'DI Yogyakarta',
                    'city_name' => 'Kabupaten Sleman',
                    'district_name' => 'Ngaglik',
                    'subdistrict_name' => 'Ngaglik',
                    'village_name' => 'Minomartani',
                    'zip_code' => '55581',
                ]],
            ], 200),
            'https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost' => Http::response([
                'meta' => ['code' => 200],
                'data' => [[
                    'code' => 'jne',
                    'name' => 'JNE',
                    'service' => 'REG',
                    'description' => 'Regular Service',
                    'cost' => 21000,
                    'etd' => '2-3 day',
                ]],
            ], 200),
        ]);

        $this->actingAs($user)
            ->post(route('cart.store', $merchandise), ['quantity' => 1]);

        $this->actingAs($user)
            ->postJson(route('checkout.shipping-options'), [
                'provinsi_name' => 'DI Yogyakarta',
                'kabupaten_name' => 'Kabupaten Sleman',
                'kecamatan_name' => 'Ngaglik',
                'desa_name' => 'Minomartani',
            ])
            ->assertOk()
            ->assertJsonPath('data.destination.id', '68424')
            ->assertJsonPath('data.groups.0.options.0.quote_id', 'jne|reg');
    }

    public function test_shipping_options_endpoint_accepts_selected_rajaongkir_destination()
    {
        $user = User::factory()->role('umum')->create();
        $merchandise = Merchandise::factory()->create([
            'qty_stock' => 5,
            'weight' => 400,
        ]);

        Expedition::factory()->create([
            'name' => 'JNE',
            'external_code' => 'jne',
            'is_active' => true,
        ]);

        $this->fakeSuccessfulShippingLookup(21000, 'REG', 'jne');

        $this->actingAs($user)
            ->post(route('cart.store', $merchandise), ['quantity' => 1]);

        $this->actingAs($user)
            ->postJson(route('checkout.shipping-options'), [
                'shipping_destination_id' => '68424',
                'shipping_destination_label' => 'Minomartani, Ngaglik, Kabupaten Sleman, DI Yogyakarta, 55581',
                'provinsi_name' => 'DI Yogyakarta',
                'kabupaten_name' => 'Kabupaten Sleman',
                'kecamatan_name' => 'Ngaglik',
                'desa_name' => 'Minomartani',
                'postal_code' => '55581',
            ])
            ->assertOk()
            ->assertJsonPath('data.destination.id', '68424')
            ->assertJsonPath('data.groups.0.options.0.quote_id', 'jne|reg');
    }

    public function test_shipping_options_uses_laravolt_codes_when_hidden_location_names_are_stale()
    {
        $user = User::factory()->role('umum')->create();
        $merchandise = Merchandise::factory()->create([
            'qty_stock' => 5,
            'weight' => 400,
        ]);

        Expedition::factory()->create([
            'name' => 'JNE',
            'external_code' => 'jne',
            'is_active' => true,
        ]);

        $this->seedLaravoltDestinationRows();
        $this->fakeSuccessfulShippingLookup(21000, 'REG', 'jne');

        $this->actingAs($user)
            ->post(route('cart.store', $merchandise), ['quantity' => 1]);

        $response = $this->actingAs($user)
            ->postJson(route('checkout.shipping-options'), [
                'provinsi_code' => '35',
                'provinsi_name' => 'Pilih Provinsi',
                'kabupaten_code' => '3501',
                'kabupaten_name' => 'Pilih Kabupaten/Kota',
                'kecamatan_code' => '3501010',
                'kecamatan_name' => 'Pilih Kecamatan',
                'desa_code' => '3501010001',
                'desa_name' => 'Pilih Desa/Kelurahan',
                'postal_code' => '63511',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.destination.id', '68424')
            ->assertJsonPath('data.groups.0.options.0.quote_id', 'jne|reg');
    }

    public function test_general_buyer_checkout_can_persist_selected_rajaongkir_destination()
    {
        $user = User::factory()->create([
            'role' => 'umum',
            'category_id' => null,
            'email' => 'buyer-destination@gmail.com',
        ]);

        $merchCategory = MerchandiseCategory::factory()->create();
        $merchandise = Merchandise::factory()->create([
            'merchandise_category_id' => $merchCategory->id,
            'price' => 120000,
            'qty_stock' => 10,
        ]);
        $expedition = Expedition::factory()->create([
            'external_code' => 'jne',
            'fee' => 15000,
        ]);

        $this->fakeSuccessfulShippingLookup(15000, 'REG', 'jne');

        $this->actingAs($user)
            ->post(route('cart.store', $merchandise), ['quantity' => 1])
            ->assertRedirect();

        $response = $this->actingAs($user)
            ->post(route('checkout.store'), [
                'name' => 'Buyer Destination',
                'email' => 'buyer-destination@gmail.com',
                'no_hp' => '081234567890',
                'shipping_destination_id' => '68424',
                'shipping_destination_label' => 'Minomartani, Ngaglik, Kabupaten Sleman, DI Yogyakarta, 55581',
                'provinsi_name' => 'DI Yogyakarta',
                'kabupaten_name' => 'Kabupaten Sleman',
                'kecamatan_name' => 'Ngaglik',
                'desa_name' => 'Minomartani',
                'alamat_lengkap' => 'Jl. Palagan No. 7',
                'selected_shipping_option' => 'jne|reg',
                'postal_code' => '55581',
            ]);

        $order = Order::first();

        $response->assertRedirect(route('orders.show', $order));
        $this->assertDatabaseHas('user_details', [
            'user_id' => $user->id,
            'provinsi_code' => '',
            'kabupaten_code' => '',
            'kecamatan_code' => '',
            'desa_code' => '',
            'provinsi_name' => 'DI Yogyakarta',
            'kabupaten_name' => 'Kabupaten Sleman',
            'kecamatan_name' => 'Ngaglik',
            'desa_name' => 'Minomartani',
            'alamat_lengkap' => 'Jl. Palagan No. 7',
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'shipping_destination_id' => '68424',
            'recipient_name' => 'Buyer Destination',
            'full_address' => 'Jl. Palagan No. 7, Minomartani, Ngaglik, Kabupaten Sleman, DI Yogyakarta',
            'expedition_id' => $expedition->id,
        ]);
    }

    public function test_checkout_rejects_tampered_or_stale_shipping_quote()
    {
        $user = User::factory()->role('peserta')->create();
        UserDetail::factory()->create(['user_id' => $user->id]);
        $merchandise = Merchandise::factory()->create(['qty_stock' => 5]);
        Expedition::factory()->create([
            'name' => 'JNE',
            'external_code' => 'jne',
            'is_active' => true,
        ]);

        Http::fake([
            'https://rajaongkir.komerce.id/api/v1/destination/domestic-destination*' => Http::response([
                'meta' => ['code' => 200],
                'data' => [[
                    'id' => '68424',
                    'label' => 'Baleharjo, Pacitan, Kabupaten Pacitan, Jawa Timur, 63511',
                    'province_name' => 'Jawa Timur',
                    'city_name' => 'Kabupaten Pacitan',
                    'subdistrict_name' => 'Pacitan',
                    'village_name' => 'Baleharjo',
                    'zip_code' => '63511',
                ]],
            ], 200),
            'https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost' => Http::response([
                'meta' => ['code' => 200],
                'data' => [[
                    'code' => 'jne',
                    'name' => 'JNE',
                    'costs' => [[
                        'service' => 'YES',
                        'description' => 'Yakin Esok Sampai',
                        'cost' => [['value' => 30000, 'etd' => '1']],
                    ]],
                ]],
            ], 200),
        ]);

        $this->actingAs($user)
            ->post(route('cart.store', $merchandise), ['quantity' => 1]);

        $response = $this->from(route('checkout.show'))
            ->actingAs($user)
            ->post(route('checkout.store'), [
                'selected_shipping_option' => 'jne|reg',
            ]);

        $response->assertRedirect(route('checkout.show'));
        $response->assertSessionHasErrors('selected_shipping_option');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_shipping_options_endpoint_fails_cleanly_when_destination_cannot_be_resolved()
    {
        $user = User::factory()->role('umum')->create();
        $merchandise = Merchandise::factory()->create(['qty_stock' => 5]);

        Expedition::factory()->create([
            'name' => 'JNE',
            'external_code' => 'jne',
            'is_active' => true,
        ]);

        Http::fake([
            'https://rajaongkir.komerce.id/api/v1/destination/domestic-destination*' => Http::response([
                'meta' => ['code' => 200],
                'data' => [[
                    'id' => '11111',
                    'label' => 'Bandung, Jawa Barat',
                    'province_name' => 'Jawa Barat',
                    'city_name' => 'Bandung',
                    'subdistrict_name' => 'Coblong',
                ]],
            ], 200),
        ]);

        $this->actingAs($user)
            ->post(route('cart.store', $merchandise), ['quantity' => 1]);

        $response = $this->actingAs($user)
            ->postJson(route('checkout.shipping-options'), [
                'provinsi_code' => '35',
                'provinsi_name' => 'JAWA TIMUR',
                'kabupaten_code' => '3501',
                'kabupaten_name' => 'KABUPATEN PACITAN',
                'kecamatan_code' => '3501010',
                'kecamatan_name' => 'PACITAN',
                'desa_code' => '3501010001',
                'desa_name' => 'BALEHARJO',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.shipping.0', 'Alamat pengiriman belum bisa dipetakan. Cek kembali kecamatan dan kabupaten.');
    }

    public function test_payment_proof_upload_and_admin_verification_work()
    {
        Storage::fake('public');

        $admin = User::factory()->role('admin')->create();
        $user = User::factory()->role('peserta')->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => Order::STATUS_WAITING_PAYMENT,
            'payment_due_at' => now()->addDay(),
        ]);

        $this->actingAs($user)
            ->post(route('orders.payment-proof', $order), [
                'payment_proof' => UploadedFile::fake()->image('proof.jpg'),
            ])
            ->assertRedirect();

        $order->refresh();

        $this->assertSame(Order::STATUS_WAITING_VERIFICATION, $order->status);
        Storage::disk('public')->assertExists($order->payment_proof_path);

        $this->actingAs($admin)
            ->post(route('admin.orders.verify', $order), [
                'verification_note' => 'Valid',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_PAID,
            'verified_by' => $admin->id,
        ]);
    }

    public function test_payment_proof_links_support_legacy_storage_prefixed_paths()
    {
        $admin = User::factory()->role('admin')->create();
        $user = User::factory()->role('peserta')->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'payment_proof_path' => 'storage/payment-proofs/legacy-proof.jpg',
        ]);

        $expectedUrl = asset('storage/payment-proofs/legacy-proof.jpg');

        $this->actingAs($user)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee($expectedUrl);

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee($expectedUrl);
    }

    public function test_order_detail_allows_owner_when_order_user_id_is_hydrated_as_string()
    {
        $user = User::factory()->role('umum')->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->app['router']->bind('order', function ($value) {
            $boundOrder = Order::findOrFail($value);

            $boundOrder->setRawAttributes(array_merge($boundOrder->getAttributes(), [
                'user_id' => (string) $boundOrder->getRawOriginal('user_id'),
            ]), true);

            return $boundOrder;
        });

        $this->actingAs($user)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee($order->invoice_number);
    }

    public function test_overdue_order_is_expired_and_stock_is_restored()
    {
        $user = User::factory()->role('peserta')->create();
        $merchandise = Merchandise::factory()->create([
            'qty_stock' => 0,
        ]);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => Order::STATUS_WAITING_PAYMENT,
            'payment_due_at' => now()->subHour(),
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'merchandise_id' => $merchandise->id,
            'quantity' => 2,
            'subtotal' => 100000,
        ]);

        $this->actingAs($user)
            ->get(route('orders.index'))
            ->assertOk();

        $this->assertSame(Order::STATUS_EXPIRED, $order->fresh()->status);
        $this->assertSame(2, $merchandise->fresh()->qty_stock);
    }

    protected function fakeSuccessfulShippingLookup($price = 15000, $service = 'REG', $courierCode = 'jne')
    {
        config([
            'services.rajaongkir.origin_destination_id' => '68423',
            'services.rajaongkir.base_url' => 'https://rajaongkir.komerce.id/api/v1',
        ]);

        Http::fake([
            'https://rajaongkir.komerce.id/api/v1/destination/domestic-destination*' => Http::response([
                'meta' => ['code' => 200],
                'data' => [[
                    'id' => '68424',
                    'label' => 'Baleharjo, Pacitan, Kabupaten Pacitan, Jawa Timur, 63511',
                    'province_name' => 'Jawa Timur',
                    'city_name' => 'Kabupaten Pacitan',
                    'subdistrict_name' => 'Pacitan',
                    'village_name' => 'Baleharjo',
                    'zip_code' => '63511',
                ]],
            ], 200),
            'https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost' => Http::response([
                'meta' => ['code' => 200],
                'data' => [[
                    'code' => $courierCode,
                    'name' => strtoupper($courierCode),
                    'service' => $service,
                    'description' => 'Regular Service',
                    'cost' => $price,
                    'etd' => '2-3 day',
                ]],
            ], 200),
        ]);
    }

    protected function seedLaravoltDestinationRows()
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

        Village::create([
            'code' => '3501010001',
            'district_code' => '3501010',
            'name' => 'BALEHARJO',
        ]);
    }
}

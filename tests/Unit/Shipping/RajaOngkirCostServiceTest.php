<?php

namespace Tests\Unit\Shipping;

use App\Exceptions\ShippingException;
use App\Services\Shipping\RajaOngkirCostService;
use App\Services\Shipping\RajaOngkirDestinationResolver;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RajaOngkirCostServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.rajaongkir.base_url' => 'https://rajaongkir.komerce.id/api/v1',
            'services.rajaongkir.api_key_shipping_cost' => 'cost-key',
            'services.rajaongkir.origin_destination_id' => '68423',
            'services.rajaongkir.default_couriers' => ['jne'],
        ]);
    }

    public function test_destination_resolver_returns_confident_match_for_saved_laravolt_address()
    {
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
        ]);

        $resolver = $this->app->make(RajaOngkirDestinationResolver::class);
        $destination = $resolver->resolve([
            'provinsi_name' => 'JAWA TIMUR',
            'kabupaten_name' => 'KABUPATEN PACITAN',
            'kecamatan_name' => 'PACITAN',
            'desa_name' => 'BALEHARJO',
            'postal_code' => '63511',
        ]);

        $this->assertSame('68424', $destination['id']);
        $this->assertSame('Baleharjo, Pacitan, Kabupaten Pacitan, Jawa Timur, 63511', $destination['label']);
    }

    public function test_destination_resolver_prefers_district_level_match_when_saved_village_is_generic()
    {
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
        ]);

        $resolver = $this->app->make(RajaOngkirDestinationResolver::class);
        $destination = $resolver->resolve([
            'provinsi_name' => 'JAWA TIMUR',
            'kabupaten_name' => 'KABUPATEN PACITAN',
            'kecamatan_name' => 'PACITAN',
            'desa_name' => 'PACITAN',
        ]);

        $this->assertSame('40561', $destination['id']);
    }

    public function test_destination_resolver_picks_a_stable_match_when_village_is_missing_and_only_village_options_exist()
    {
        Http::fake([
            'https://rajaongkir.komerce.id/api/v1/destination/domestic-destination*' => Http::response([
                'meta' => ['code' => 200],
                'data' => [
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
                    [
                        'id' => '40553',
                        'label' => 'Sidoharjo, Pacitan, Kabupaten Pacitan, Jawa Timur, 63514',
                        'province_name' => 'Jawa Timur',
                        'city_name' => 'Kabupaten Pacitan',
                        'district_name' => 'Pacitan',
                        'subdistrict_name' => 'Pacitan',
                        'village_name' => 'Sidoharjo',
                        'zip_code' => '63514',
                    ],
                ],
            ], 200),
        ]);

        $resolver = $this->app->make(RajaOngkirDestinationResolver::class);
        $destination = $resolver->resolve([
            'provinsi_name' => 'JAWA TIMUR',
            'kabupaten_name' => 'KABUPATEN PACITAN',
            'kecamatan_name' => 'PACITAN',
            'desa_name' => '',
        ]);

        $this->assertSame('40552', $destination['id']);
    }

    public function test_cost_service_normalizes_domestic_quotes()
    {
        Http::fake([
            'https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost' => Http::response([
                'meta' => ['code' => 200],
                'data' => [[
                    'code' => 'jne',
                    'name' => 'JNE',
                    'costs' => [[
                        'service' => 'REG',
                        'description' => 'Regular Service',
                        'cost' => [[
                            'value' => 17000,
                            'etd' => '2-3',
                        ]],
                    ]],
                ]],
            ], 200),
        ]);

        $service = $this->app->make(RajaOngkirCostService::class);
        $quotes = $service->calculateDomesticCost('68424', 1000, ['jne']);

        $this->assertSame('jne|reg', $quotes[0]['quote_id']);
        $this->assertSame('jne', $quotes[0]['courier_code']);
        $this->assertSame('reg', $quotes[0]['service_code']);
        $this->assertSame(17000, $quotes[0]['price']);
        $this->assertSame('2-3', $quotes[0]['etd']);
    }

    public function test_cost_service_wraps_api_failures_and_connection_failures()
    {
        Http::fake([
            'https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost' => Http::response([
                'message' => 'Invalid origin',
            ], 422),
        ]);

        $service = $this->app->make(RajaOngkirCostService::class);

        try {
            $service->calculateDomesticCost('68424', 1000, ['jne']);
            $this->fail('Expected ShippingException for API failure.');
        } catch (ShippingException $exception) {
            $this->assertSame('Invalid origin', $exception->userMessage());
        }

        Http::fake([
            'https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost' => function () {
                throw new ConnectionException('timeout');
            },
        ]);

        $this->expectException(ShippingException::class);
        $service->calculateDomesticCost('68424', 1000, ['jne']);
    }
}

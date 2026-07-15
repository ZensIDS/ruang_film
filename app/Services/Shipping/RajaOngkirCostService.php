<?php

namespace App\Services\Shipping;

use App\Exceptions\ShippingException;
use App\Models\AppSetting;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class RajaOngkirCostService
{
    public function searchDomesticDestinations($keyword, $limit = 10, $offset = 0)
    {
        $keyword = trim((string) $keyword);

        if ($keyword === '') {
            return [];
        }

        $response = $this->requestGet('/destination/domestic-destination', [
            'search' => $keyword,
            'limit' => $limit,
            'offset' => $offset,
        ]);

        return collect($this->extractList($response))
            ->map(function ($item) {
                return $this->normalizeDestination($item);
            })
            ->filter(function ($item) {
                return $item['id'] !== '';
            })
            ->values()
            ->all();
    }

    public function calculateDomesticCost($destinationId, $weight, array $couriers = null, $price = 'lowest')
    {
        $originIds = AppSetting::shippingOriginDestinationIds();

        if (empty($originIds)) {
            throw new ShippingException('Origin destination ID is missing.', 'Konfigurasi origin pengiriman belum lengkap.');
        }

        $destinationId = trim((string) $destinationId);

        if ($destinationId === '') {
            throw new ShippingException('Destination ID is missing.', 'Tujuan pengiriman tidak valid.');
        }

        $courierCodes = collect($couriers ?: config('services.rajaongkir.default_couriers', []))
            ->map(function ($courier) {
                return $this->normalizeCode($courier);
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($courierCodes)) {
            throw new ShippingException('No active couriers configured.', 'Belum ada kurir aktif untuk perhitungan ongkir.');
        }

        $attemptedOrigins = 0;
        $lastException = null;
        $encounteredEmptyQuotes = false;

        foreach (AppSetting::shippingOriginDestinationIds($destinationId) as $originId) {
            $attemptedOrigins++;

            try {
                $quotes = $this->calculateDomesticCostForOrigin(
                    $originId,
                    $destinationId,
                    $weight,
                    $courierCodes,
                    $price
                );
            } catch (ShippingException $exception) {
                $lastException = $exception;
                continue;
            }

            if (!empty($quotes)) {
                return $quotes;
            }

            $encounteredEmptyQuotes = true;
        }

        if ($attemptedOrigins === 0) {
            throw new ShippingException(
                'Origin destination ID matches the destination ID.',
                'Origin pengiriman aktif sama dengan tujuan. Perbarui origin pengiriman untuk area ini.'
            );
        }

        if ($lastException) {
            throw $lastException;
        }

        if ($encounteredEmptyQuotes) {
            throw new ShippingException('No shipping quotes available.', 'Tidak ada layanan pengiriman yang tersedia untuk alamat ini.');
        }

        throw new ShippingException('No shipping quotes available.', 'Tidak ada layanan pengiriman yang tersedia untuk alamat ini.');
    }

    public function trackWaybill($airwayBill, $courierCode)
    {
        $response = $this->requestPostWithQuery('/track/waybill', [
            'awb' => trim((string) $airwayBill),
            'courier' => $this->normalizeCode($courierCode),
        ]);

        return $response;
    }

    protected function requestGet($uri, array $query = [])
    {
        try {
            $response = $this->client()->get($uri, $query);
        } catch (RequestException $exception) {
            throw new ShippingException(
                $exception->getMessage(),
                $this->extractErrorMessage(optional($exception->response)->json()) ?: 'Permintaan ongkir gagal diproses.',
                0,
                $exception
            );
        } catch (ConnectionException $exception) {
            throw new ShippingException($exception->getMessage(), 'Gagal terhubung ke layanan pengiriman.', 0, $exception);
        }

        return $this->ensureSuccessfulResponse($response);
    }

    protected function requestFormPost($uri, array $payload = [])
    {
        try {
            $response = $this->client()->asForm()->post($uri, $payload);
        } catch (RequestException $exception) {
            throw new ShippingException(
                $exception->getMessage(),
                $this->extractErrorMessage(optional($exception->response)->json()) ?: 'Permintaan ongkir gagal diproses.',
                0,
                $exception
            );
        } catch (ConnectionException $exception) {
            throw new ShippingException($exception->getMessage(), 'Gagal terhubung ke layanan pengiriman.', 0, $exception);
        }

        return $this->ensureSuccessfulResponse($response);
    }

    protected function requestPostWithQuery($uri, array $query = [])
    {
        try {
            $response = $this->client()->post($uri . '?' . http_build_query($query), []);
        } catch (RequestException $exception) {
            throw new ShippingException(
                $exception->getMessage(),
                $this->extractErrorMessage(optional($exception->response)->json()) ?: 'Permintaan ongkir gagal diproses.',
                0,
                $exception
            );
        } catch (ConnectionException $exception) {
            throw new ShippingException($exception->getMessage(), 'Gagal terhubung ke layanan pengiriman.', 0, $exception);
        }

        return $this->ensureSuccessfulResponse($response);
    }

    protected function calculateDomesticCostForOrigin($originId, $destinationId, $weight, array $courierCodes, $price)
    {
        $response = $this->requestFormPost('/calculate/domestic-cost', [
            'origin' => $originId,
            'destination' => $destinationId,
            'weight' => max(1, (int) $weight),
            'courier' => implode(':', $courierCodes),
            'price' => $price,
        ]);

        return $this->normalizeCostOptions($this->extractList($response));
    }

    protected function client()
    {
        return Http::baseUrl(rtrim((string) config('services.rajaongkir.base_url'), '/'))
            ->acceptJson()
            ->withHeaders([
                'key' => (string) config('services.rajaongkir.api_key_shipping_cost'),
            ])
            ->timeout((int) config('services.rajaongkir.timeout', 20))
            ->retry(
                (int) config('services.rajaongkir.retry_times', 2),
                (int) config('services.rajaongkir.retry_sleep_ms', 250)
            );
    }

    protected function ensureSuccessfulResponse(Response $response)
    {
        if (!$response->successful()) {
            throw new ShippingException(
                'RajaOngkir request failed with status ' . $response->status() . '.',
                $this->extractErrorMessage($response->json()) ?: 'Permintaan ongkir gagal diproses.'
            );
        }

        $payload = $response->json();

        if (data_get($payload, 'success') === false || (int) data_get($payload, 'meta.code', 200) >= 400) {
            throw new ShippingException(
                $this->extractErrorMessage($payload) ?: 'RajaOngkir returned an error.',
                $this->extractErrorMessage($payload) ?: 'Permintaan ongkir gagal diproses.'
            );
        }

        return $payload;
    }

    protected function extractList($payload)
    {
        $data = data_get($payload, 'data');

        if (is_array($data)) {
            return $this->isList($data) ? $data : [$data];
        }

        $results = data_get($payload, 'rajaongkir.results');

        if (is_array($results)) {
            return $this->isList($results) ? $results : [$results];
        }

        return [];
    }

    protected function normalizeDestination($item)
    {
        $label = $this->firstFilled($item, ['label', 'name', 'destination_name']);

        return [
            'id' => (string) $this->firstFilled($item, ['id', 'destination_id', 'subdistrict_id', 'district_id']),
            'label' => (string) ($label ?: collect([
                $this->firstFilled($item, ['village_name', 'village', 'urban']),
                $this->firstFilled($item, ['subdistrict_name', 'subdistrict', 'district_name']),
                $this->firstFilled($item, ['city_name', 'city', 'district']),
                $this->firstFilled($item, ['province_name', 'province']),
            ])->filter()->implode(', ')),
            'province' => (string) $this->firstFilled($item, ['province_name', 'province']),
            'city' => (string) $this->firstFilled($item, ['city_name', 'city', 'district']),
            'district' => (string) $this->firstFilled($item, ['district_name', 'district']),
            'subdistrict' => (string) $this->firstFilled($item, ['subdistrict_name', 'subdistrict']),
            'village' => (string) $this->firstFilled($item, ['village_name', 'village', 'urban']),
            'zip_code' => (string) $this->firstFilled($item, ['zip_code', 'postal_code']),
            'raw' => $item,
        ];
    }

    protected function normalizeCostOptions(array $items)
    {
        $quotes = collect();

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $courierCode = RajaOngkirCourierNormalizer::normalize(
                $this->firstFilled($item, ['code', 'courier', 'shipping', 'courier_code', 'shipping_code', 'name'])
            );
            $courierName = (string) ($this->firstFilled($item, ['name', 'courier_name', 'shipping_name', 'courier']) ?: strtoupper($courierCode));

            foreach ($this->extractServiceItems($item) as $service) {
                $serviceCode = (string) ($this->firstFilled($service, ['service', 'service_code', 'shipping_type', 'code', 'name']) ?: '');
                $serviceName = (string) ($this->firstFilled($service, ['description', 'service_name', 'name', 'service']) ?: $serviceCode);
                $price = $this->extractPrice($service);
                $etd = (string) ($this->extractEtd($service) ?: '');

                if ($courierCode === '' || $serviceCode === '' || $price === null) {
                    continue;
                }

                $quotes->push([
                    'quote_id' => $this->buildQuoteId($courierCode, $serviceCode),
                    'courier_code' => $courierCode,
                    'courier_name' => $courierName,
                    'service_code' => $this->normalizeCode($serviceCode),
                    'service_name' => $serviceName,
                    'description' => (string) ($this->firstFilled($service, ['description', 'note']) ?: $serviceName),
                    'price' => $price,
                    'etd' => $etd,
                    'raw' => $service,
                ]);
            }
        }

        return $quotes
            ->unique(function ($quote) {
                return $quote['quote_id'] . ':' . $quote['price'] . ':' . $quote['etd'];
            })
            ->values()
            ->all();
    }

    protected function extractServiceItems(array $item)
    {
        foreach (['costs', 'services', 'results', 'tariffs'] as $key) {
            $value = Arr::get($item, $key);

            if (is_array($value)) {
                return $this->isList($value) ? $value : [$value];
            }
        }

        if ($this->firstFilled($item, ['service', 'service_code', 'shipping_type'])) {
            return [$item];
        }

        return [];
    }

    protected function extractPrice(array $service)
    {
        $value = $this->firstFilled($service, ['price', 'shipping_cost', 'value']);

        if ($value !== null && $value !== '') {
            return (int) round((float) $value);
        }

        if (array_key_exists('cost', $service) && !is_array($service['cost'])) {
            return (int) round((float) $service['cost']);
        }

        $cost = Arr::get($service, 'cost');

        if (is_array($cost)) {
            foreach ($cost as $item) {
                if (is_array($item) && array_key_exists('value', $item)) {
                    return (int) round((float) $item['value']);
                }
            }
        }

        return null;
    }

    protected function extractEtd(array $service)
    {
        $value = $this->firstFilled($service, ['etd', 'estimate', 'delivery_time', 'lead_time']);

        if ($value !== null && $value !== '') {
            return $this->sanitizeEtd($value);
        }

        $cost = Arr::get($service, 'cost');

        if (is_array($cost)) {
            foreach ($cost as $item) {
                if (is_array($item) && array_key_exists('etd', $item) && $item['etd'] !== '') {
                    return $this->sanitizeEtd($item['etd']);
                }
            }
        }

        return null;
    }

    protected function extractErrorMessage($payload)
    {
        foreach (['meta.message', 'message', 'error', 'errors.0.message'] as $key) {
            $value = data_get($payload, $key);

            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }

    protected function firstFilled(array $payload, array $keys)
    {
        foreach ($keys as $key) {
            $value = Arr::get($payload, $key);

            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    protected function normalizeCode($value)
    {
        return strtolower(trim((string) $value));
    }

    public function buildQuoteId($courierCode, $serviceCode)
    {
        return $this->normalizeCode($courierCode) . '|' . $this->normalizeCode($serviceCode);
    }

    protected function sanitizeEtd($value)
    {
        $value = trim((string) $value);
        $value = preg_replace('/\s*days?\b/i', '', $value);
        $value = preg_replace('/\s*hari\b/i', '', $value);

        return trim($value);
    }

    protected function isList(array $value)
    {
        return array_values($value) === $value;
    }
}

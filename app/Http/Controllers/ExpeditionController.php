<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Expedition;
use App\Services\Shipping\RajaOngkirCourierNormalizer;
use App\Services\Shipping\RajaOngkirCostService;
use Illuminate\Http\Request;
use Laravolt\Indonesia\Models\District;
use Illuminate\Support\Str;

class ExpeditionController extends Controller
{
    public function index()
    {
        $originSettings = $this->originSettings();

        return view('expedition.index', [
            'title' => 'Data Expedisi',
            'expeditions' => Expedition::latest()->get(),
            'originSettings' => $originSettings,
        ]);
    }

    public function create()
    {
        return view('expedition.create', [
            'title' => 'Tambah Expedisi',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);

        Expedition::create(array_merge($validated, [
            'is_active' => $request->boolean('is_active', true),
        ]));

        return redirect()->route('expeditions.index')
            ->with('toast_success', 'Expedisi berhasil disimpan.');
    }

    public function edit(Expedition $expedition)
    {
        return view('expedition.edit', [
            'title' => 'Edit Expedisi',
            'expedition' => $expedition,
        ]);
    }

    public function update(Request $request, Expedition $expedition)
    {
        $validated = $this->validateRequest($request);

        $expedition->update(array_merge($validated, [
            'is_active' => $request->boolean('is_active'),
        ]));

        return redirect()->route('expeditions.index')
            ->with('toast_success', 'Expedisi berhasil diperbarui.');
    }

    public function destroy(Expedition $expedition)
    {
        $expedition->delete();

        return back()->with('toast_success', 'Expedisi berhasil dihapus.');
    }

    public function searchLaravoltOrigin(Request $request)
    {
        $keyword = trim((string) $request->query('keyword'));

        if ($keyword === '') {
            return response()->json([
                'message' => 'Masukkan kata kunci kecamatan/kabupaten terlebih dahulu.',
            ], 422);
        }

        $districts = District::with('city.province')
            ->where(function ($query) use ($keyword) {
                $query->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('code', 'like', '%' . $keyword . '%')
                    ->orWhereHas('city', function ($cityQuery) use ($keyword) {
                        $cityQuery->where('name', 'like', '%' . $keyword . '%')
                            ->orWhere('code', 'like', '%' . $keyword . '%')
                            ->orWhereHas('province', function ($provinceQuery) use ($keyword) {
                                $provinceQuery->where('name', 'like', '%' . $keyword . '%')
                                    ->orWhere('code', 'like', '%' . $keyword . '%');
                            });
                    });
            })
            ->orderBy('name')
            ->limit(15)
            ->get()
            ->map(function ($district) {
                return [
                    'district_code' => $district->code,
                    'district_name' => $district->name,
                    'city_code' => optional($district->city)->code,
                    'city_name' => optional($district->city)->name,
                    'province_code' => optional(optional($district->city)->province)->code,
                    'province_name' => optional(optional($district->city)->province)->name,
                    'label' => collect([
                        $district->name,
                        optional($district->city)->name,
                        optional(optional($district->city)->province)->name,
                    ])->filter()->implode(', '),
                ];
            })
            ->values()
            ->all();

        return response()->json([
            'data' => $districts,
        ]);
    }

    public function searchRajaOngkirOrigin(Request $request, RajaOngkirCostService $costService)
    {
        $keyword = trim((string) $request->query('keyword'));

        if ($keyword === '') {
            return response()->json([
                'message' => 'Masukkan kata kunci destinasi RajaOngkir terlebih dahulu.',
            ], 422);
        }

        return response()->json([
            'data' => $costService->searchDomesticDestinations($keyword, 15, 0),
        ]);
    }

    public function updateOriginFromLaravolt(Request $request, RajaOngkirCostService $costService)
    {
        $validated = $request->validate([
            'district_code' => 'required|string',
        ]);

        $district = District::with('city.province')
            ->where('code', $validated['district_code'])
            ->firstOrFail();

        $autoDestination = $this->resolveRajaOngkirOriginFromDistrict($district, $costService);

        AppSetting::setValue('shipping_origin_laravolt_district_code', $district->code);
        AppSetting::setValue('shipping_origin_laravolt_district_name', $district->name);
        AppSetting::setValue('shipping_origin_laravolt_city_code', optional($district->city)->code ?? '');
        AppSetting::setValue('shipping_origin_laravolt_city_name', optional($district->city)->name ?? '');
        AppSetting::setValue('shipping_origin_laravolt_province_code', optional(optional($district->city)->province)->code ?? '');
        AppSetting::setValue('shipping_origin_laravolt_province_name', optional(optional($district->city)->province)->name ?? '');
        AppSetting::setValue('shipping_origin_laravolt_auto_destination_id', $autoDestination['id'] ?? '');
        AppSetting::setValue('shipping_origin_laravolt_auto_destination_label', $autoDestination['label'] ?? '');
        AppSetting::setValue('shipping_origin_rajaongkir_destination_id', '');
        AppSetting::setValue('shipping_origin_rajaongkir_label', '');

        $message = 'Origin Laravolt berhasil disimpan.';

        if ($autoDestination) {
            $message .= ' RajaOngkir auto-match: ID ' . $autoDestination['id'] . '.';
        } else {
            $message .= ' Auto-match RajaOngkir belum ketemu, jadi checkout akan tetap fallback ke .env sampai backup RajaOngkir diset.';
        }

        return redirect()->route('expeditions.index')->with('toast_success', $message);
    }

    public function updateOriginFromRajaOngkir(Request $request)
    {
        $validated = $request->validate([
            'destination_id' => 'required|string',
            'destination_label' => 'required|string',
        ]);

        AppSetting::setValue('shipping_origin_rajaongkir_destination_id', trim($validated['destination_id']));
        AppSetting::setValue('shipping_origin_rajaongkir_label', trim($validated['destination_label']));

        return redirect()->route('expeditions.index')
            ->with('toast_success', 'Backup origin RajaOngkir berhasil disimpan dan akan diprioritaskan di checkout.');
    }

    protected function validateRequest(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'external_code' => 'required|string|max:100',
            'service_name' => 'nullable|string|max:255',
            'fee' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['external_code'] = RajaOngkirCourierNormalizer::normalize($validated['external_code']);

        return $validated;
    }

    protected function originSettings()
    {
        $laravolt = [
            'district_code' => AppSetting::getValue('shipping_origin_laravolt_district_code', ''),
            'district_name' => AppSetting::getValue('shipping_origin_laravolt_district_name', ''),
            'city_code' => AppSetting::getValue('shipping_origin_laravolt_city_code', ''),
            'city_name' => AppSetting::getValue('shipping_origin_laravolt_city_name', ''),
            'province_code' => AppSetting::getValue('shipping_origin_laravolt_province_code', ''),
            'province_name' => AppSetting::getValue('shipping_origin_laravolt_province_name', ''),
            'auto_destination_id' => AppSetting::getValue('shipping_origin_laravolt_auto_destination_id', ''),
            'auto_destination_label' => AppSetting::getValue('shipping_origin_laravolt_auto_destination_label', ''),
        ];

        $manualRajaOngkirId = AppSetting::getValue('shipping_origin_rajaongkir_destination_id', '');
        $effectiveDestinationId = AppSetting::shippingOriginDestinationId();
        $fallbackDestinationId = trim((string) config('services.rajaongkir.fallback_origin_destination_id'));

        if (trim((string) $manualRajaOngkirId) !== '') {
            $effectiveSource = 'rajaongkir_backup';
        } elseif (trim((string) $laravolt['auto_destination_id']) !== '') {
            $effectiveSource = 'laravolt_auto';
        } else {
            $effectiveSource = 'env_fallback';
        }

        return [
            'laravolt' => $laravolt,
            'rajaongkir_backup' => [
                'destination_id' => $manualRajaOngkirId,
                'label' => AppSetting::getValue('shipping_origin_rajaongkir_label', ''),
            ],
            'effective' => [
                'destination_id' => $effectiveDestinationId,
                'source' => $effectiveSource,
            ],
            'env' => [
                'legacy_district_id' => trim((string) config('services.rajaongkir.legacy_origin_district_id')),
                'fallback_destination_id' => $fallbackDestinationId,
            ],
        ];
    }

    protected function resolveRajaOngkirOriginFromDistrict(District $district, RajaOngkirCostService $costService)
    {
        $keyword = collect([
            $district->name,
            optional($district->city)->name,
            optional(optional($district->city)->province)->name,
        ])->filter()->implode(' ');

        $districtName = $this->normalizeLocationValue($district->name);
        $cityName = $this->normalizeLocationValue(optional($district->city)->name);
        $provinceName = $this->normalizeLocationValue(optional(optional($district->city)->province)->name);

        $results = collect($costService->searchDomesticDestinations($keyword, 20, 0))
            ->filter(function ($destination) use ($districtName, $cityName, $provinceName) {
                return $districtName !== ''
                    && $cityName !== ''
                    && $provinceName !== ''
                    && $this->normalizeLocationValue($destination['district'] ?? '') === $districtName
                    && $this->normalizeLocationValue($destination['city'] ?? '') === $cityName
                    && $this->normalizeLocationValue($destination['province'] ?? '') === $provinceName;
            })
            ->values();

        if ($results->isEmpty()) {
            return null;
        }

        $preferred = $results->first(function ($destination) use ($districtName) {
            $subdistrict = $this->normalizeLocationValue($destination['subdistrict'] ?? '');

            return $subdistrict === $districtName || $subdistrict === '' || $subdistrict === '-';
        });

        if ($preferred) {
            return $preferred;
        }

        return null;
    }

    protected function normalizeLocationValue($value)
    {
        $value = Str::lower(trim((string) $value));
        $value = preg_replace('/\b(provinsi|kabupaten|kota|kecamatan|kelurahan|desa)\b/u', ' ', $value);
        $value = preg_replace('/[^a-z0-9]+/u', ' ', $value);

        return trim(preg_replace('/\s+/u', ' ', $value));
    }
}

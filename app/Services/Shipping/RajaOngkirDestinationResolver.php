<?php

namespace App\Services\Shipping;

use App\Exceptions\ShippingException;
use Illuminate\Support\Str;

class RajaOngkirDestinationResolver
{
    protected $costService;

    public function __construct(RajaOngkirCostService $costService)
    {
        $this->costService = $costService;
    }

    public function resolve(array $location)
    {
        $candidates = collect();

        foreach ($this->buildSearchKeywords($location) as $keyword) {
            foreach ($this->costService->searchDomesticDestinations($keyword, 20, 0) as $destination) {
                $candidates->put($destination['id'], $destination);
            }
        }

        $scored = $candidates
            ->unique(function ($destination) {
                return $this->destinationSignature($destination);
            })
            ->map(function ($destination) use ($location) {
                return $this->evaluateCandidate($destination, $location);
            })
            ->filter(function ($item) {
                return $item['score'] >= 100;
            })
            ->sort(function ($left, $right) {
                return $this->compareCandidates($left, $right);
            })
            ->values();

        if ($scored->isEmpty()) {
            throw new ShippingException(
                'Unable to resolve RajaOngkir destination.',
                'Alamat pengiriman belum bisa dipetakan. Cek kembali kecamatan dan kabupaten.'
            );
        }

        if ($scored->count() > 1 && $this->hasEquivalentRank($scored[0], $scored[1])) {
            $fallbackDestination = $this->resolveEquivalentCandidates($scored);

            if ($fallbackDestination !== null) {
                return $fallbackDestination;
            }

            throw new ShippingException(
                'Ambiguous RajaOngkir destination match.',
                'Alamat pengiriman menghasilkan lebih dari satu tujuan. Periksa kembali detail alamat.'
            );
        }

        return $scored[0]['destination'];
    }

    protected function buildSearchKeywords(array $location)
    {
        return collect([
            implode(' ', array_filter([
                $location['desa_name'] ?? null,
                $location['kecamatan_name'] ?? null,
                $location['kabupaten_name'] ?? null,
            ])),
            implode(' ', array_filter([
                $location['kecamatan_name'] ?? null,
                $location['kabupaten_name'] ?? null,
                $location['provinsi_name'] ?? null,
            ])),
            implode(' ', array_filter([
                $location['kabupaten_name'] ?? null,
                $location['provinsi_name'] ?? null,
            ])),
        ])->map(function ($keyword) {
            return trim((string) $keyword);
        })->filter()->unique()->values()->all();
    }

    protected function scoreCandidate(array $destination, array $location)
    {
        $province = $this->normalizeText($location['provinsi_name'] ?? '');
        $city = $this->normalizeText($location['kabupaten_name'] ?? '');
        $district = $this->normalizeText($location['kecamatan_name'] ?? '');
        $village = $this->normalizeText($location['desa_name'] ?? '');
        $postalCode = trim((string) ($location['postal_code'] ?? ''));

        $label = $this->normalizeText($destination['label'] ?? '');
        $destinationProvince = $this->normalizeText($destination['province'] ?? '');
        $destinationCity = $this->normalizeText($destination['city'] ?? '');
        $destinationDistrict = $this->normalizeText($destination['district'] ?? '');
        $destinationSubdistrict = $this->normalizeText($destination['subdistrict'] ?? '');
        $destinationVillage = $this->normalizeText($destination['village'] ?? '');
        $destinationZip = trim((string) ($destination['zip_code'] ?? ''));

        $provinceMatched = $province !== '' && ($province === $destinationProvince || Str::contains($label, $province));
        $cityMatched = $city !== '' && (
            $city === $destinationCity
            || $city === $destinationDistrict
            || Str::contains($label, $city)
        );
        $districtMatched = $district !== '' && (
            $district === $destinationSubdistrict
            || $district === $destinationDistrict
            || Str::contains($label, $district)
        );

        if (!$provinceMatched || !$cityMatched || !$districtMatched) {
            return 0;
        }

        $score = 100;
        $exactVillageMatch = false;
        $labelVillageMatch = false;
        $requestedVillageIsSpecific = $this->isSpecificLocationPart($village, [$district, $city, $province]);

        if ($requestedVillageIsSpecific) {
            if ($village === $destinationVillage) {
                $exactVillageMatch = true;
                $score += 15;
            } elseif (Str::contains($label, $village)) {
                $labelVillageMatch = true;
                $score += 8;
            }
        }

        if ($postalCode !== '' && $postalCode === $destinationZip) {
            $score += 5;
        }

        return $score;
    }

    protected function evaluateCandidate(array $destination, array $location)
    {
        $province = $this->normalizeText($location['provinsi_name'] ?? '');
        $city = $this->normalizeText($location['kabupaten_name'] ?? '');
        $district = $this->normalizeText($location['kecamatan_name'] ?? '');
        $village = $this->normalizeText($location['desa_name'] ?? '');
        $postalCode = trim((string) ($location['postal_code'] ?? ''));

        $destinationProvince = $this->normalizeText($destination['province'] ?? '');
        $destinationCity = $this->normalizeText($destination['city'] ?? '');
        $destinationDistrict = $this->normalizeText($destination['district'] ?? '');
        $destinationSubdistrict = $this->normalizeText($destination['subdistrict'] ?? '');
        $destinationVillage = $this->normalizeText($destination['village'] ?? '');
        $destinationZip = trim((string) ($destination['zip_code'] ?? ''));

        $requestedVillageIsSpecific = $this->isSpecificLocationPart($village, [$district, $city, $province]);
        $destinationVillageIsSpecific = $this->isSpecificLocationPart($destinationVillage, [
            $destinationSubdistrict,
            $destinationDistrict,
            $destinationCity,
            $destinationProvince,
        ]);

        $exactVillageMatch = $requestedVillageIsSpecific
            && $village !== ''
            && $village === $destinationVillage;

        $labelVillageMatch = $requestedVillageIsSpecific
            && $village !== ''
            && Str::contains($this->normalizeText($destination['label'] ?? ''), $village);

        $postalMatch = $postalCode !== '' && $postalCode === $destinationZip;
        $exactSubdistrictMatch = $district !== ''
            && ($district === $destinationSubdistrict || $district === $destinationDistrict);

        return [
            'score' => $this->scoreCandidate($destination, $location) + $this->levelFitScore(
                $village,
                $district,
                $city,
                $province,
                $destinationVillage,
                $destinationSubdistrict,
                $destinationDistrict,
                $destinationCity,
                $destinationProvince
            ),
            'exact_village_match' => $exactVillageMatch,
            'label_village_match' => $labelVillageMatch,
            'postal_match' => $postalMatch,
            'exact_subdistrict_match' => $exactSubdistrictMatch,
            'requested_village_is_specific' => $requestedVillageIsSpecific,
            'destination_village_is_specific' => $destinationVillageIsSpecific,
            'destination' => $destination,
        ];
    }

    protected function compareCandidates(array $left, array $right)
    {
        foreach ([
            'score',
            'postal_match',
            'exact_village_match',
            'label_village_match',
            'exact_subdistrict_match',
            'requested_village_is_specific',
            'destination_village_is_specific',
        ] as $field) {
            $comparison = ($right[$field] <=> $left[$field]);

            if ($comparison !== 0) {
                return $comparison;
            }
        }

        return strcmp((string) ($left['destination']['id'] ?? ''), (string) ($right['destination']['id'] ?? ''));
    }

    protected function hasEquivalentRank(array $left, array $right)
    {
        foreach ([
            'score',
            'postal_match',
            'exact_village_match',
            'label_village_match',
            'exact_subdistrict_match',
            'requested_village_is_specific',
            'destination_village_is_specific',
        ] as $field) {
            if (($left[$field] ?? null) !== ($right[$field] ?? null)) {
                return false;
            }
        }

        return true;
    }

    protected function resolveEquivalentCandidates($scored)
    {
        $top = $scored[0] ?? null;

        if (!$top) {
            return null;
        }

        $equivalentCandidates = collect($scored)->filter(function ($item) use ($top) {
            return $this->hasEquivalentRank($top, $item);
        })->values();

        if ($equivalentCandidates->isEmpty()) {
            return null;
        }

        if (!$top['requested_village_is_specific']) {
            return $equivalentCandidates->first()['destination'];
        }

        $uniqueAreaKeys = $equivalentCandidates
            ->map(function ($item) {
                return $this->districtAreaKey($item['destination']);
            })
            ->unique()
            ->values();

        if ($uniqueAreaKeys->count() === 1) {
            return $equivalentCandidates->first()['destination'];
        }

        return null;
    }

    protected function levelFitScore(
        $village,
        $district,
        $city,
        $province,
        $destinationVillage,
        $destinationSubdistrict,
        $destinationDistrict,
        $destinationCity,
        $destinationProvince
    ) {
        $requestedVillageIsSpecific = $this->isSpecificLocationPart($village, [$district, $city, $province]);
        $destinationVillageIsSpecific = $this->isSpecificLocationPart($destinationVillage, [
            $destinationSubdistrict,
            $destinationDistrict,
            $destinationCity,
            $destinationProvince,
        ]);

        if ($requestedVillageIsSpecific) {
            return $destinationVillageIsSpecific ? 3 : 0;
        }

        if ($village === '') {
            return $destinationVillageIsSpecific ? 0 : 3;
        }

        return $destinationVillageIsSpecific ? 1 : 4;
    }

    protected function isSpecificLocationPart($value, array $genericParts = [])
    {
        $value = trim((string) $value);

        if ($value === '') {
            return false;
        }

        return !in_array($value, array_filter($genericParts), true);
    }

    protected function destinationSignature(array $destination)
    {
        return implode('|', [
            $this->normalizeText($destination['province'] ?? ''),
            $this->normalizeText($destination['city'] ?? ''),
            $this->normalizeText($destination['district'] ?? ''),
            $this->normalizeText($destination['subdistrict'] ?? ''),
            $this->normalizeText($destination['village'] ?? ''),
            trim((string) ($destination['zip_code'] ?? '')),
        ]);
    }

    protected function districtAreaKey(array $destination)
    {
        return implode('|', [
            $this->normalizeText($destination['province'] ?? ''),
            $this->normalizeText($destination['city'] ?? ''),
            $this->normalizeText($destination['subdistrict'] ?? $destination['district'] ?? ''),
        ]);
    }

    protected function normalizeText($value)
    {
        $value = Str::lower((string) $value);
        $value = preg_replace('/\b(provinsi|kabupaten|kota|kecamatan|kelurahan|desa)\b/u', ' ', $value);
        $value = preg_replace('/[^a-z0-9]+/u', ' ', $value);

        return trim(preg_replace('/\s+/u', ' ', $value));
    }
}

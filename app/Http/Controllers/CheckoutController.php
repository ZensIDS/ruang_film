<?php

namespace App\Http\Controllers;

use App\Exceptions\ShippingException;
use App\Models\AppSetting;
use App\Models\Cart;
use App\Models\Expedition;
use App\Models\Order;
use App\Models\UserDetail;
use App\Services\Shipping\RajaOngkirCostService;
use App\Services\Shipping\RajaOngkirCourierNormalizer;
use App\Services\Shipping\RajaOngkirDestinationResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Province;
use Laravolt\Indonesia\Models\Village;

class CheckoutController extends Controller
{
    protected const SELF_PICKUP_OPTION = 'pickup_basecamp';
    protected const SELF_PICKUP_CODE = 'pickup';
    protected const SELF_PICKUP_NAME = 'Ambil di Basecamp';
    protected const SELF_PICKUP_SERVICE_NAME = 'Self Pickup';

    public function show()
    {
        $cart = Cart::with('items.merchandise.category')
            ->firstOrCreate(['user_id' => auth()->id()]);

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('warning', 'Keranjang Anda masih kosong.');
        }

        $user = auth()->user()->load('detail');
        $isGeneralBuyer = $user->isGeneralBuyer();

        if (!$user->detail && !$isGeneralBuyer) {
            return redirect()->route('user-detail.index')
                ->with('warning', 'Lengkapi biodata terlebih dahulu sebelum checkout.');
        }

        return view('landing.checkout', [
            'cart' => $cart,
            'user' => $user,
            'isGeneralBuyer' => $isGeneralBuyer,
            'provinsi' => $isGeneralBuyer ? Province::orderBy('name')->get() : collect(),
            'selfPickupOption' => static::SELF_PICKUP_OPTION,
            'selfPickupLabel' => static::SELF_PICKUP_NAME,
        ]);
    }

    public function shippingOptions(Request $request, RajaOngkirDestinationResolver $destinationResolver, RajaOngkirCostService $costService)
    {
        $user = auth()->user()->load('detail');
        $cart = Cart::with('items.merchandise')
            ->firstOrCreate(['user_id' => $user->id]);

        if ($cart->items->isEmpty()) {
            return response()->json([
                'message' => 'Keranjang Anda masih kosong.',
                'errors' => ['cart' => ['Keranjang Anda masih kosong.']],
            ], 422);
        }

        try {
            $destination = $this->resolveShippingDestination($request, $user, $destinationResolver, true);
            $quotes = $this->getLiveShippingOptions(
                $costService,
                $destination['id'],
                $this->calculateCartWeight($cart)
            );
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => 'Data alamat belum lengkap.',
                'errors' => $exception->errors(),
            ], 422);
        } catch (ShippingException $exception) {
            return response()->json([
                'message' => $exception->userMessage(),
                'errors' => ['shipping' => [$exception->userMessage()]],
            ], 422);
        }

        return response()->json([
            'data' => [
                'destination' => [
                    'id' => $destination['id'],
                    'label' => $destination['label'],
                ],
                'weight' => $this->calculateCartWeight($cart),
                'groups' => collect($quotes)
                    ->groupBy('courier_code')
                    ->map(function ($items) {
                        $first = $items->first();

                        return [
                            'courier_code' => $first['courier_code'],
                            'courier_name' => $first['courier_name'],
                            'expedition_id' => $first['expedition_id'],
                            'expedition_name' => $first['expedition_name'],
                            'options' => $items->values()->all(),
                        ];
                    })
                    ->values()
                    ->all(),
            ],
        ]);
    }

    public function searchDestination(Request $request, RajaOngkirCostService $costService)
    {
        $keyword = trim((string) $request->query('keyword'));

        if ($keyword === '') {
            return response()->json([
                'message' => 'Masukkan kata kunci alamat terlebih dahulu.',
                'errors' => ['keyword' => ['Masukkan kata kunci alamat terlebih dahulu.']],
            ], 422);
        }

        try {
            $results = collect($costService->searchDomesticDestinations($keyword, 15, 0))
                ->values()
                ->all();
        } catch (ShippingException $exception) {
            return response()->json([
                'message' => $exception->userMessage(),
                'errors' => ['destination' => [$exception->userMessage()]],
            ], 422);
        }

        return response()->json([
            'data' => $results,
        ]);
    }

    public function store(Request $request, RajaOngkirDestinationResolver $destinationResolver, RajaOngkirCostService $costService)
    {
        $user = auth()->user()->load('detail');
        $isGeneralBuyer = $user->isGeneralBuyer();
        $isSelfPickup = $this->isSelfPickupSelection($request->input('selected_shipping_option'));
        $usesSelectedDestination = $this->hasSelectedDestination($request);
        $hasLegacyLocationCodes = $this->hasLegacyLocationCodes($request);

        $rules = [
            'selected_shipping_option' => 'required|string',
            'postal_code' => 'nullable|string|max:10',
            'notes' => 'nullable|string',
        ];

        if ($isGeneralBuyer) {
            $rules = array_merge($rules, [
                'name' => 'required|string|max:100',
                'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
                'no_hp' => 'required|string|min:10|max:15|regex:/^[0-9]+$/',
                'alamat_lengkap' => 'required|string',
            ]);

            if (!$isSelfPickup) {
                if ($usesSelectedDestination) {
                    $rules = array_merge($rules, [
                        'shipping_destination_id' => 'required|string',
                        'shipping_destination_label' => 'required|string',
                        'provinsi_name' => 'required|string',
                        'kabupaten_name' => 'required|string',
                        'kecamatan_name' => 'required|string',
                        'desa_name' => 'nullable|string',
                    ]);
                } elseif ($hasLegacyLocationCodes) {
                    $this->mergeLaravoltLocationNames($request);

                    $rules = array_merge($rules, [
                        'provinsi_code' => 'required',
                        'provinsi_name' => 'required',
                        'kabupaten_code' => 'required',
                        'kabupaten_name' => 'required',
                        'kecamatan_code' => 'required',
                        'kecamatan_name' => 'required',
                        'desa_code' => 'required',
                        'desa_name' => 'required',
                    ]);
                } else {
                    $rules = array_merge($rules, [
                        'provinsi_name' => 'required|string',
                        'kabupaten_name' => 'required|string',
                        'kecamatan_name' => 'required|string',
                        'desa_name' => 'nullable|string',
                    ]);
                }
            }
        }

        $request->validate($rules);

        if ($isGeneralBuyer) {
            $this->persistGeneralBuyerProfile($request, $user);
            $user->load('detail');
        }

        if (!$user->detail) {
            return redirect()->route('user-detail.index')
                ->with('warning', 'Lengkapi biodata terlebih dahulu sebelum checkout.');
        }

        $cart = Cart::with('items.merchandise')
            ->firstOrCreate(['user_id' => $user->id]);

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('warning', 'Keranjang Anda masih kosong.');
        }

        $subtotal = 0;

        foreach ($cart->items as $item) {
            $merchandise = $item->merchandise;

            if (!$merchandise || !$merchandise->is_active) {
                return redirect()->route('cart.index')
                    ->with('warning', 'Ada merchandise yang sudah tidak tersedia.');
            }

            if ($item->quantity > $merchandise->qty_stock) {
                return redirect()->route('cart.index')
                    ->with('warning', 'Stok merchandise tidak mencukupi untuk checkout.');
            }

            $subtotal += $item->quantity * $merchandise->currentPrice();
        }

        $destination = null;
        $selectedQuote = $this->selfPickupQuote();

        if (!$isSelfPickup) {
            try {
                $destination = $this->resolveShippingDestination($request, $user, $destinationResolver);
                $quotes = $this->getLiveShippingOptions(
                    $costService,
                    $destination['id'],
                    $this->calculateCartWeight($cart)
                );
                $selectedQuote = $this->findSelectedQuote($quotes, $request->selected_shipping_option);
            } catch (ShippingException $exception) {
                throw ValidationException::withMessages([
                    'selected_shipping_option' => [$exception->userMessage()],
                ]);
            }
        }

        $order = DB::transaction(function () use ($cart, $request, $user, $subtotal, $selectedQuote, $destination) {
            $order = Order::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'user_id' => $user->id,
                'expedition_id' => $selectedQuote['expedition_id'],
                'expedition_name' => $selectedQuote['expedition_name'],
                'expedition_code' => $selectedQuote['courier_code'],
                'expedition_service_name' => $selectedQuote['service_name'],
                'expedition_service_code' => $selectedQuote['service_code'],
                'recipient_name' => $user->name,
                'recipient_email' => $user->email,
                'recipient_phone' => $user->no_hp,
                'province_name' => $user->detail->provinsi_name,
                'city_name' => $user->detail->kabupaten_name,
                'district_name' => $user->detail->kecamatan_name,
                'village_name' => $user->detail->desa_name,
                'postal_code' => $request->postal_code,
                'shipping_destination_id' => $destination['id'] ?? null,
                'full_address' => $this->resolveOrderAddress($user, $selectedQuote),
                'notes' => $request->notes,
                'shipping_fee' => $selectedQuote['price'],
                'shipping_etd' => $selectedQuote['etd'],
                'subtotal' => $subtotal,
                'total' => $subtotal + $selectedQuote['price'],
                'status' => Order::STATUS_WAITING_PAYMENT,
                'payment_due_at' => now()->addHours(AppSetting::paymentDueHours()),
            ]);

            foreach ($cart->items as $item) {
                $merchandise = $item->merchandise;

                $order->items()->create([
                    'merchandise_id' => $merchandise->id,
                    'merchandise_name' => $merchandise->name,
                    'merchandise_slug' => $merchandise->slug,
                    'merchandise_image' => $merchandise->image,
                    'unit_price' => $merchandise->currentPrice(),
                    'quantity' => $item->quantity,
                    'weight' => $merchandise->weight,
                    'subtotal' => $item->quantity * $merchandise->currentPrice(),
                ]);

                $merchandise->decrement('qty_stock', $item->quantity);
            }

            $cart->items()->delete();

            return $order;
        });

        return redirect()->route('orders.show', $order)
            ->with('success', 'Invoice berhasil dibuat. Silakan lanjutkan pembayaran.');
    }

    protected function generateInvoiceNumber()
    {
        do {
            $number = 'INV-RF-' . now()->format('Ymd') . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Order::where('invoice_number', $number)->exists());

        return $number;
    }

    protected function persistGeneralBuyerProfile(Request $request, $user)
    {
        $shouldClearLegacyCodes = $this->hasSelectedDestination($request) || !$this->hasLegacyLocationCodes($request);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
        ]);

        UserDetail::updateOrCreate(
            ['user_id' => $user->id],
            [
                'community_name' => null,
                'provinsi_code' => $shouldClearLegacyCodes ? '' : (string) $request->provinsi_code,
                'provinsi_name' => (string) $request->input('provinsi_name', ''),
                'kabupaten_code' => $shouldClearLegacyCodes ? '' : (string) $request->kabupaten_code,
                'kabupaten_name' => (string) $request->input('kabupaten_name', ''),
                'kecamatan_code' => $shouldClearLegacyCodes ? '' : (string) $request->kecamatan_code,
                'kecamatan_name' => (string) $request->input('kecamatan_name', ''),
                'desa_code' => $shouldClearLegacyCodes ? '' : (string) $request->desa_code,
                'desa_name' => (string) $request->input('desa_name', ''),
                'username_ig' => null,
                'posisi' => null,
                'alamat_lengkap' => $request->alamat_lengkap,
                'tanggal_lahir' => null,
            ]
        );
    }

    protected function resolveShippingAddressData(Request $request, $user, $forQuote = false)
    {
        $isGeneralBuyer = $user->isGeneralBuyer();

        if (!$isGeneralBuyer) {
            if (!$user->detail) {
                throw ValidationException::withMessages([
                    'address' => ['Lengkapi biodata pengiriman terlebih dahulu.'],
                ]);
            }

            $detail = $user->detail;

            return [
                'provinsi_name' => $this->laravoltName(Province::class, $detail->provinsi_code, $detail->provinsi_name),
                'kabupaten_name' => $this->laravoltName(City::class, $detail->kabupaten_code, $detail->kabupaten_name),
                'kecamatan_name' => $this->laravoltName(District::class, $detail->kecamatan_code, $detail->kecamatan_name),
                'desa_name' => $this->laravoltName(Village::class, $detail->desa_code, $detail->desa_name),
                'postal_code' => $request->postal_code,
            ];
        }

        $this->mergeLaravoltLocationNames($request);

        $data = [
            'provinsi_code' => $request->input('provinsi_code', optional($user->detail)->provinsi_code),
            'provinsi_name' => $request->input('provinsi_name', optional($user->detail)->provinsi_name),
            'kabupaten_code' => $request->input('kabupaten_code', optional($user->detail)->kabupaten_code),
            'kabupaten_name' => $request->input('kabupaten_name', optional($user->detail)->kabupaten_name),
            'kecamatan_code' => $request->input('kecamatan_code', optional($user->detail)->kecamatan_code),
            'kecamatan_name' => $request->input('kecamatan_name', optional($user->detail)->kecamatan_name),
            'desa_code' => $request->input('desa_code', optional($user->detail)->desa_code),
            'desa_name' => $request->input('desa_name', optional($user->detail)->desa_name),
            'postal_code' => $request->postal_code,
        ];

        if ($forQuote) {
            if ($this->hasLocationCodes($data)) {
                validator($data, [
                    'provinsi_code' => 'required',
                    'provinsi_name' => 'required',
                    'kabupaten_code' => 'required',
                    'kabupaten_name' => 'required',
                    'kecamatan_code' => 'required',
                    'kecamatan_name' => 'required',
                    'desa_code' => 'required',
                    'desa_name' => 'required',
                ])->validate();
            } else {
                validator($data, [
                    'provinsi_name' => 'required',
                    'kabupaten_name' => 'required',
                    'kecamatan_name' => 'required',
                    'desa_name' => 'nullable|string',
                ])->validate();
            }
        }

        return $data;
    }

    protected function resolveShippingDestination(Request $request, $user, RajaOngkirDestinationResolver $destinationResolver, $forQuote = false)
    {
        if ($user->isGeneralBuyer()) {
            $selectedDestination = $this->selectedDestinationFromRequest($request, $forQuote);

            if ($selectedDestination !== null) {
                return $selectedDestination;
            }
        }

        $address = $this->resolveShippingAddressData($request, $user, $forQuote);

        return $destinationResolver->resolve($address);
    }

    protected function selectedDestinationFromRequest(Request $request, $forQuote = false)
    {
        $destinationId = trim((string) $request->input('shipping_destination_id'));

        if ($destinationId === '') {
            return null;
        }

        $data = [
            'shipping_destination_id' => $destinationId,
            'shipping_destination_label' => trim((string) $request->input('shipping_destination_label')),
            'provinsi_name' => trim((string) $request->input('provinsi_name')),
            'kabupaten_name' => trim((string) $request->input('kabupaten_name')),
            'kecamatan_name' => trim((string) $request->input('kecamatan_name')),
            'desa_name' => trim((string) $request->input('desa_name')),
            'postal_code' => trim((string) $request->input('postal_code')),
        ];

        if ($forQuote) {
            validator($data, [
                'shipping_destination_id' => 'required',
                'shipping_destination_label' => 'required',
                'provinsi_name' => 'required',
                'kabupaten_name' => 'required',
                'kecamatan_name' => 'required',
            ])->validate();
        }

        return [
            'id' => $data['shipping_destination_id'],
            'label' => $data['shipping_destination_label'],
            'province' => $data['provinsi_name'],
            'city' => $data['kabupaten_name'],
            'district' => $data['kecamatan_name'],
            'subdistrict' => $data['kecamatan_name'],
            'village' => $data['desa_name'],
            'zip_code' => $data['postal_code'],
        ];
    }

    protected function mergeLaravoltLocationNames(Request $request)
    {
        $request->merge([
            'provinsi_name' => $this->laravoltName(Province::class, $request->input('provinsi_code'), $request->input('provinsi_name')),
            'kabupaten_name' => $this->laravoltName(City::class, $request->input('kabupaten_code'), $request->input('kabupaten_name')),
            'kecamatan_name' => $this->laravoltName(District::class, $request->input('kecamatan_code'), $request->input('kecamatan_name')),
            'desa_name' => $this->laravoltName(Village::class, $request->input('desa_code'), $request->input('desa_name')),
        ]);
    }

    protected function laravoltName($modelClass, $code, $fallback = null)
    {
        $code = trim((string) $code);

        if ($code === '') {
            return $fallback;
        }

        $location = $modelClass::where('code', $code)->first();

        return $location ? $location->name : $fallback;
    }

    protected function hasSelectedDestination(Request $request)
    {
        return trim((string) $request->input('shipping_destination_id')) !== '';
    }

    protected function hasLegacyLocationCodes(Request $request)
    {
        return $this->hasLocationCodes($request->only([
            'provinsi_code',
            'kabupaten_code',
            'kecamatan_code',
            'desa_code',
        ]));
    }

    protected function hasLocationCodes(array $data)
    {
        foreach (['provinsi_code', 'kabupaten_code', 'kecamatan_code', 'desa_code'] as $key) {
            if (trim((string) ($data[$key] ?? '')) === '') {
                return false;
            }
        }

        return true;
    }

    protected function calculateCartWeight(Cart $cart)
    {
        $weight = (int) $cart->items->sum(function ($item) {
            return $item->quantity * (int) optional($item->merchandise)->weight;
        });

        return max(1, $weight);
    }

    protected function getLiveShippingOptions(RajaOngkirCostService $costService, $destinationId, $weight)
    {
        $expeditions = Expedition::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->keyBy(function ($expedition) {
                return RajaOngkirCourierNormalizer::normalize(
                    $expedition->external_code ?: $expedition->name
                );
            })
            ->filter(function ($expedition, $code) {
                return $code !== '';
            });

        if ($expeditions->isEmpty()) {
            throw new ShippingException('No active RajaOngkir couriers configured.', 'Belum ada kurir aktif yang tersedia untuk pengiriman.');
        }

        $quotes = collect($costService->calculateDomesticCost($destinationId, $weight, $expeditions->keys()->all()))
            ->filter(function ($quote) use ($expeditions) {
                return $expeditions->has($quote['courier_code']);
            })
            ->map(function ($quote) use ($expeditions) {
                $expedition = $expeditions->get($quote['courier_code']);

                return array_merge($quote, [
                    'expedition_id' => $expedition->id,
                    'expedition_name' => $expedition->name,
                    'courier_name' => $expedition->name ?: $quote['courier_name'],
                ]);
            })
            ->sortBy(function ($quote) {
                return $quote['price'];
            })
            ->values()
            ->all();

        if (empty($quotes)) {
            throw new ShippingException('No shipping quotes available.', 'Tidak ada layanan pengiriman yang tersedia untuk alamat ini.');
        }

        return $quotes;
    }

    protected function findSelectedQuote(array $quotes, $selectedQuoteId)
    {
        $selectedQuoteId = strtolower(trim((string) $selectedQuoteId));

        foreach ($quotes as $quote) {
            if (strtolower((string) $quote['quote_id']) === $selectedQuoteId) {
                return $quote;
            }
        }

        throw new ShippingException(
            'Selected quote was not found in live RajaOngkir options.',
            'Pilihan layanan pengiriman sudah tidak tersedia. Silakan pilih ulang ongkir.'
        );
    }

    protected function isSelfPickupSelection($selectedShippingOption)
    {
        return strtolower(trim((string) $selectedShippingOption)) === static::SELF_PICKUP_OPTION;
    }

    protected function selfPickupQuote()
    {
        return [
            'quote_id' => static::SELF_PICKUP_OPTION,
            'expedition_id' => null,
            'expedition_name' => static::SELF_PICKUP_NAME,
            'courier_code' => static::SELF_PICKUP_CODE,
            'courier_name' => static::SELF_PICKUP_NAME,
            'service_name' => static::SELF_PICKUP_SERVICE_NAME,
            'service_code' => static::SELF_PICKUP_CODE,
            'price' => 0,
            'etd' => null,
        ];
    }

    protected function resolveOrderAddress($user, array $selectedQuote)
    {
        if (($selectedQuote['service_code'] ?? null) === static::SELF_PICKUP_CODE) {
            return static::SELF_PICKUP_NAME;
        }

        return optional($user->detail)->formatted_address ?: static::SELF_PICKUP_NAME;
    }
}

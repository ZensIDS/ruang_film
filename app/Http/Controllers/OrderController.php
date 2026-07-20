<?php

namespace App\Http\Controllers;

use App\Exceptions\ShippingException;
use App\Models\BankAccount;
use App\Models\Merchandise;
use App\Models\Order;
use App\Services\Shipping\RajaOngkirDeliveryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\OrdersExport;
use Maatwebsite\Excel\Facades\Excel;

class OrderController extends Controller
{
    public function index()
    {
        $this->expireOverdueOrders();

        return view('landing.orders', [
            'orders' => auth()->user()->orders()->latest()->get(),
        ]);
    }

    public function show(Order $order)
    {
        $this->expireOverdueOrders();
        $this->authorizeOrderAccess($order);

        $order->load(['items', 'verifier']);

        return view('landing.invoice', [
            'order' => $order,
            'bankAccounts' => BankAccount::where('is_active', true)->orderBy('rek_bank_name')->get(),
        ]);
    }

    public function uploadPaymentProof(Request $request, Order $order)
    {
        $this->expireOverdueOrders();
        abort_unless(auth()->user() && auth()->user()->ownsUserId($order->user_id), 403);

        if (!$order->canUploadProof()) {
            return back()->with('warning', 'Bukti transfer tidak dapat diunggah untuk invoice ini.');
        }

        $request->validateWithBag('uploadPaymentProof', [
            'payment_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $order->deleteStoredPaymentProof();

        $path = $request->file('payment_proof')->store('payment-proofs', 'public');

        $order->update([
            'payment_proof_path' => $path,
            'payment_submitted_at' => now(),
            'status' => Order::STATUS_WAITING_VERIFICATION,
            'verified_at' => null,
            'verified_by' => null,
            'verification_note' => null,
            'rejection_note' => null,
        ]);

        return back()->with('success', 'Bukti transfer berhasil diunggah.');
    }

    public function adminIndex(Request $request)
    {
        $this->expireOverdueOrders();

        ['orders' => $orders, 'stats' => $stats, 'startDateInput' => $startDateInput, 'endDateInput' => $endDateInput]
            = $this->getFilteredOrders($request);

        return view('order.index', [
            'title'     => 'Data Invoice Merchandise',
            'orders'    => $orders,
            'stats'     => $stats,
            'startDate' => $startDateInput,
            'endDate'   => $endDateInput,
        ]);
    }

    public function adminShow(Order $order)
    {
        $this->expireOverdueOrders();

        return view('order.show', [
            'title' => 'Detail Invoice',
            'order' => $order->load(['user', 'items', 'verifier']),
        ]);
    }

    public function exportExcel(Request $request)
    {
        ['orders' => $orders, 'stats' => $stats, 'startDateInput' => $startDateInput, 'endDateInput' => $endDateInput]
            = $this->getFilteredOrders($request);

        $fileName = 'data-invoice-merchandise'
            . ($startDateInput && $endDateInput ? "_{$startDateInput}_sd_{$endDateInput}" : '_semua')
            . '.xlsx';

        return Excel::download(
            new OrdersExport($orders, $stats, $startDateInput, $endDateInput),
            $fileName
        );
    }

    public function verify(Request $request, Order $order)
    {
        if ($order->status !== Order::STATUS_WAITING_VERIFICATION) {
            return back()->with('warning', 'Invoice ini tidak menunggu verifikasi.');
        }

        $request->validate([
            'verification_note' => 'nullable|string',
        ]);

        $order->update([
            'status' => Order::STATUS_PAID,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'verification_note' => $request->verification_note,
            'rejection_note' => null,
        ]);

        return back()->with('success', 'Pembayaran berhasil diverifikasi.');
    }

    public function createShipment(Order $order, RajaOngkirDeliveryService $deliveryService)
    {
        if ($order->isSelfPickup()) {
            return back()->with('warning', 'Pesanan ambil di basecamp tidak memerlukan shipment.');
        }

        if (!config('services.rajaongkir.komship_enabled')) {
            return back()->with('warning', 'Fitur Komship sedang dinonaktifkan pada environment ini.');
        }

        if ($order->status !== Order::STATUS_PAID) {
            return back()->with('warning', 'Shipment hanya bisa dibuat setelah pembayaran berstatus paid.');
        }

        if ($order->hasShipment()) {
            return back()->with('warning', 'Shipment untuk invoice ini sudah pernah dibuat.');
        }

        try {
            $payload = $deliveryService->createShipment($order->load('items'));
        } catch (ShippingException $exception) {
            return back()->with('warning', $exception->userMessage());
        }

        $order->update(array_merge($payload, [
            'shipping_synced_at' => now(),
        ]));

        return back()->with('success', 'Shipment berhasil dibuat dan nomor order Komship sudah tersimpan.');
    }

    public function syncShipment(Order $order, RajaOngkirDeliveryService $deliveryService)
    {
        if ($order->isSelfPickup()) {
            return back()->with('warning', 'Pesanan ambil di basecamp tidak memerlukan sinkronisasi shipment.');
        }

        if (!config('services.rajaongkir.komship_enabled')) {
            return back()->with('warning', 'Fitur Komship sedang dinonaktifkan pada environment ini.');
        }

        if (!$order->hasShipment()) {
            return back()->with('warning', 'Shipment belum dibuat untuk invoice ini.');
        }

        try {
            $payload = $deliveryService->syncShipment($order);
        } catch (ShippingException $exception) {
            return back()->with('warning', $exception->userMessage());
        }

        $order->update(array_merge($payload, [
            'shipping_synced_at' => now(),
        ]));

        return back()->with('success', 'Data shipment berhasil disinkronkan.');
    }

    public function updateAirwayBill(Request $request, Order $order)
    {
        if ($order->isSelfPickup()) {
            return back()->with('warning', 'Pesanan ambil di basecamp tidak menggunakan nomor resi.');
        }

        $validated = $request->validateWithBag('updateAirwayBill', [
            'shipping_airway_bill' => 'required|string|max:100',
        ]);

        $airwayBill = trim((string) $validated['shipping_airway_bill']);

        if ($airwayBill === '') {
            return back()
                ->withErrors([
                    'shipping_airway_bill' => 'Nomor resi wajib diisi.',
                ], 'updateAirwayBill')
                ->withInput();
        }

        $updates = [
            'shipping_airway_bill' => $airwayBill,
        ];

        if ($airwayBill !== trim((string) $order->shipping_airway_bill)) {
            $updates['shipping_tracking_payload'] = null;
            $updates['shipping_synced_at'] = null;
        }

        $order->update($updates);

        return back()->with('success', 'Nomor resi berhasil diperbarui.');
    }

    public function reject(Request $request, Order $order)
    {
        if ($order->status !== Order::STATUS_WAITING_VERIFICATION) {
            return back()->with('warning', 'Invoice ini tidak menunggu verifikasi.');
        }

        $request->validate([
            'rejection_note' => 'required|string',
        ]);

        DB::transaction(function () use ($order, $request) {
            $this->restoreStock($order);

            $order->update([
                'status' => Order::STATUS_PAYMENT_REJECTED,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'rejection_note' => $request->rejection_note,
                'verification_note' => null,
            ]);
        });

        return back()->with('success', 'Pembayaran ditolak dan stok dikembalikan.');
    }

    protected function authorizeOrderAccess(Order $order)
    {
        $allowedRoles = ['admin', 'adminsub', 'adminmerch'];

        if (
            (auth()->user() && auth()->user()->ownsUserId($order->user_id))
            || auth()->user()->hasRole($allowedRoles)
        ) {
            return;
        }

        abort(403);
    }

    protected function expireOverdueOrders()
    {
        $expiredOrders = Order::with('items')
            ->where('status', Order::STATUS_WAITING_PAYMENT)
            ->whereNotNull('payment_due_at')
            ->where('payment_due_at', '<', now())
            ->get();

        foreach ($expiredOrders as $order) {
            DB::transaction(function () use ($order) {
                $this->restoreStock($order);

                $order->update([
                    'status' => Order::STATUS_EXPIRED,
                ]);
            });
        }
    }

    protected function restoreStock(Order $order)
    {
        foreach ($order->items as $item) {
            if (!$item->merchandise_id) {
                continue;
            }

            Merchandise::where('id', $item->merchandise_id)
                ->increment('qty_stock', $item->quantity);
        }
    }

    private function getFilteredOrders(Request $request): array
    {
        $startDateInput = $request->input('start_date');
        $endDateInput   = $request->input('end_date');

        $startDate = $startDateInput ? Carbon::parse($startDateInput)->startOfDay() : null;
        $endDate   = $endDateInput ? Carbon::parse($endDateInput)->endOfDay() : null;

        $query = Order::with('user')->latest();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $orders = $query->get();

        $paidOrders = $orders->where('status', 'paid');

        $grossRevenue  = (float) $paidOrders->sum('total');
        $shippingTotal = (float) $paidOrders->sum('shipping_fee');

        $stats = [
            'paid_count'                 => $paidOrders->count(),
            'waiting_verification_count' => $orders->where('status', 'waiting_verification')->count(),
            'expired_count'               => $orders->where('status', 'expired')->count(),
            'rejected_count'              => $orders->where('status', 'payment_rejected')->count(),
            'gross_revenue'               => $grossRevenue,
            'shipping_total'              => $shippingTotal,
            'net_revenue'                 => $grossRevenue - $shippingTotal,
        ];

        return compact('orders', 'stats', 'startDateInput', 'endDateInput');
    }
}

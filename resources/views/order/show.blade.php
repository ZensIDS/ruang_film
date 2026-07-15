@extends('layouts.master')
@section('container')
<section class="content">
    <div class="row">
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><b>Detail Invoice {{ $order->invoice_number }}</b></h3>
                </div>
                <div class="box-body">
                    <p><b>Pembeli:</b> {{ $order->recipient_name }} ({{ $order->recipient_phone }})</p>
                    @if($order->isSelfPickup())
                    <p><b>Metode:</b> {{ $order->deliveryMethodLabel() }}</p>
                    <p><b>Pengambilan:</b> Pesanan diambil sendiri di basecamp.</p>
                    @else
                    <p><b>Alamat:</b> {{ $order->full_address }}</p>
                    <p><b>Expedisi:</b> {{ $order->deliveryMethodLabel() }}</p>
                    @endif
                    {{-- <p><b>Estimasi:</b> {{ $order->shipping_etd ? $order->shipping_etd . ' hari' : '-' }}</p> --}}
                    <p><b>Status:</b> {{ strtoupper(str_replace('_', ' ', $order->status)) }}</p>
                    <p><b>Batas Bayar:</b> {{ optional($order->payment_due_at)->translatedFormat('d F Y H:i') ?? '-' }} WIB</p>
                    @if($order->notes)
                    <p><b>Catatan:</b> {{ $order->notes }}</p>
                    @endif

                    <hr>
                    <h4><b>Item Pesanan</b></h4>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Qty</th>
                                    <th>Berat</th>
                                    <th>Harga</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr>
                                    <td>{{ $item->merchandise_name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ number_format($item->weight) }} gr</td>
                                    <td>@currency($item->unit_price)</td>
                                    <td>@currency($item->subtotal)</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-right">Subtotal</th>
                                    <th>@currency($order->subtotal)</th>
                                </tr>
                                <tr>
                                    <th colspan="4" class="text-right">Ongkir</th>
                                    <th>
                                        @if($order->isSelfPickup())
                                        Gratis
                                        @else
                                        @currency($order->shipping_fee)
                                        @endif
                                    </th>
                                </tr>
                                <tr>
                                    <th colspan="4" class="text-right">Total</th>
                                    <th>@currency($order->total)</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="box-footer">
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-default">Kembali</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title"><b>Bukti Transfer</b></h3>
                </div>
                <div class="box-body">
                    @if($order->payment_proof_path)
                    <p>
                        <a href="{{ $order->paymentProofUrl() }}" target="_blank" class="btn btn-info btn-sm">
                            Lihat Bukti Transfer
                        </a>
                    </p>
                    @else
                    <p class="text-muted">Belum ada bukti transfer.</p>
                    @endif

                    @if($order->verification_note)
                    <div class="alert alert-success">
                        <b>Catatan Verifikasi:</b><br>
                        {{ $order->verification_note }}
                    </div>
                    @endif

                    @if($order->rejection_note)
                    <div class="alert alert-danger">
                        <b>Catatan Penolakan:</b><br>
                        {{ $order->rejection_note }}
                    </div>
                    @endif

                    @if($order->status === \App\Models\Order::STATUS_WAITING_VERIFICATION)
                    <hr>
                    <form action="{{ route('admin.orders.verify', $order) }}" method="POST" style="margin-bottom:14px;">
                        @csrf
                        <div class="form-group">
                            <label>Catatan Verifikasi</label>
                            <textarea name="verification_note" class="form-control" rows="3" placeholder="Opsional"></textarea>
                        </div>
                        <button type="submit" class="btn btn-success btn-block">Verifikasi Pembayaran</button>
                    </form>

                    <form action="{{ route('admin.orders.reject', $order) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>Alasan Penolakan</label>
                            <textarea name="rejection_note" class="form-control" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger btn-block">Tolak Pembayaran</button>
                    </form>
                    @endif

                    <hr>
                    <h4><b>Shipment</b></h4>
                    @if($order->isSelfPickup())
                    <p class="text-muted" style="margin-bottom:0;">Pesanan ini diambil sendiri di basecamp, jadi tidak memerlukan resi atau shipment.</p>
                    @else
                    {{-- <p><b>Order Komship:</b> {{ $order->shipping_order_no ?? '-' }}</p> --}}
                    <p><b>No. Resi:</b> {{ $order->shipping_airway_bill ?? '-' }}</p>
                    <form action="{{ route('admin.orders.airway-bill.update', $order) }}" method="POST" style="margin-top:12px;">
                        @csrf
                        @method('PATCH')
                        <div class="form-group">
                            <label>Ubah No. Resi</label>
                            <input type="text" name="shipping_airway_bill" class="form-control"
                                value="{{ old('shipping_airway_bill', $order->shipping_airway_bill) }}" required>
                            @if($errors->updateAirwayBill->has('shipping_airway_bill'))
                            <small class="text-danger">{{ $errors->updateAirwayBill->first('shipping_airway_bill') }}</small>
                            @endif
                            <p class="help-block" style="margin-bottom:0;">
                                Mengubah resi akan mengosongkan data tracking tersimpan agar sinkronisasi berikutnya pakai resi terbaru.
                            </p>
                        </div>
                        <button type="submit" class="btn btn-warning btn-block">Simpan No. Resi</button>
                    </form>
                    {{-- <p><b>Status Pengiriman:</b> {{ $order->shippingStatusText() }}</p> --}}
                    {{-- <p><b>Sync Terakhir:</b> {{ optional($order->shipping_synced_at)->translatedFormat('d F Y H:i') ?? '-' }} WIB</p> --}}

                    {{-- @if(!config('services.rajaongkir.komship_enabled')) --}}
                    {{-- <div class="alert alert-info" style="margin-top:12px;"> --}}
                        {{-- Fitur Komship sedang dinonaktifkan. Aktifkan <code>RAJAONGKIR_KOMSHIP_ENABLED=true</code> jika paket shipment ingin dipakai. --}}
                    {{-- </div> --}}
                    {{-- @endif --}}
{{--  --}}
                    {{-- @if(config('services.rajaongkir.komship_enabled') && $order->status === \App\Models\Order::STATUS_PAID && !$order->hasShipment()) --}}
                    {{-- <form action="{{ route('admin.orders.shipment.store', $order) }}" method="POST" style="margin-top:12px;"> --}}
                        {{-- @csrf --}}
                        {{-- <button type="submit" class="btn btn-primary btn-block">Buat Shipment Komship</button> --}}
                    {{-- </form> --}}
                    {{-- @endif --}}
{{--  --}}
                    {{-- @if(config('services.rajaongkir.komship_enabled') && $order->hasShipment()) --}}
                    {{-- <form action="{{ route('admin.orders.shipment.sync', $order) }}" method="POST" style="margin-top:12px;"> --}}
                        {{-- @csrf --}}
                        {{-- <button type="submit" class="btn btn-info btn-block">Sinkronkan Tracking</button> --}}
                    {{-- </form> --}}
                    {{-- @endif --}}
{{--  --}}
                    {{-- @if($order->shippingTrackingEvents()->isNotEmpty()) --}}
                    {{-- <hr> --}}
                    {{-- <h4><b>Riwayat Tracking</b></h4> --}}
                    {{-- <ul class="list-unstyled" style="margin-bottom:0;"> --}}
                        {{-- @foreach($order->shippingTrackingEvents() as $event) --}}
                        {{-- <li style="padding:10px 0; border-bottom:1px solid #eee;"> --}}
                            {{-- <div><b>{{ $event['status_label'] ?? '-' }}</b></div> --}}
                            {{-- <div>{{ $event['description'] ?? '-' }}</div> --}}
                            {{-- <small class="text-muted">{{ trim(($event['date'] ?? '') . ' ' . ($event['location'] ?? '')) ?: '-' }}</small> --}}
                        {{-- </li> --}}
                        {{-- @endforeach --}}
                    {{-- </ul> --}}
                    {{-- @endif --}}
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@extends('layouts.master')
@section('container')
<section class="content-header">
    <h1>Invoice Merchandise</h1>
</section>
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-body table-responsive">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Invoice</th>
                                <th>Pembeli</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Batas Bayar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $order->invoice_number }}</td>
                                <td>{{ $order->user->name ?? '-' }}</td>
                                <td>@currency($order->total)</td>
                                <td>
                                    <span class="label label-{{ $order->status === 'paid' ? 'success' : ($order->status === 'waiting_verification' ? 'warning' : ($order->status === 'expired' || $order->status === 'payment_rejected' ? 'danger' : 'info')) }}">
                                        {{ strtoupper(str_replace('_', ' ', $order->status)) }}
                                    </span>
                                </td>
                                <td>{{ optional($order->payment_due_at)->translatedFormat('d M Y H:i') ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-info btn-xs">Detail</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

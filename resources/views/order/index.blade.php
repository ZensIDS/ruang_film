@extends('layouts.master')
@section('container')
<section class="content-header">
    <h1>Invoice Merchandise</h1>
</section>
<section class="content">
<<<<<<< HEAD
=======
<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-file"></i> Rekap Merchandise</h3>
    </div>
    {{-- Rekap jumlah transaksi per status --}}
    <div class="row">
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="small-box bg-green">
                <div class="inner">
                    <h3>{{ $stats['paid_count'] }}</h3>
                    <p>Paid</p>
                </div>
                <div class="icon"><i class="fa fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3>{{ $stats['waiting_verification_count'] }}</h3>
                    <p>Waiting Verification</p>
                </div>
                <div class="icon"><i class="fa fa-clock-o"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="small-box bg-red">
                <div class="inner">
                    <h3>{{ $stats['expired_count'] }}</h3>
                    <p>Expired</p>
                </div>
                <div class="icon"><i class="fa fa-times-circle"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="small-box bg-maroon">
                <div class="inner">
                    <h3>{{ $stats['rejected_count'] }}</h3>
                    <p>Rejected</p>
                </div>
                <div class="icon"><i class="fa fa-ban"></i></div>
            </div>
        </div>
    </div>

    {{-- Rekap pendapatan --}}
    <div class="row">
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-aqua"><i class="fa fa-money"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pendapatan Kotor</span>
                    <span class="info-box-number">@currency($stats['gross_revenue'])</span>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-yellow"><i class="fa fa-truck"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Ongkir</span>
                    <span class="info-box-number">@currency($stats['shipping_total'])</span>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-green"><i class="fa fa-line-chart"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pendapatan Bersih</span>
                    <span class="info-box-number">@currency($stats['net_revenue'])</span>
                </div>
            </div>
        </div>
    </div>
    <p class="text-muted" style="margin: -10px 0 15px 5px; font-size: 12px;">
        * Pendapatan dihitung dari order berstatus <b>Paid</b> saja. Pendapatan Bersih = Pendapatan Kotor - Total Ongkir.
    </p>
    <hr>

    {{-- Filter tanggal --}}
    <div class="row">
        <div class="col-xs-12">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-filter"></i> Filter Tanggal</h3>
                </div>
                <div class="box-body">
                    <form method="GET" action="{{ url()->current() }}" class="form-inline">
                        <div class="form-group">
                            <label for="date_range" class="control-label" style="margin-right: 8px;">Rentang Tanggal</label>
                            <input
                                type="text"
                                id="date_range"
                                class="form-control"
                                style="width: 260px;"
                                placeholder="Pilih rentang tanggal"
                                autocomplete="off"
                                value="{{ $startDate && $endDate ? \Carbon\Carbon::parse($startDate)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($endDate)->format('d/m/Y') : '' }}">
                        </div>
                        <input type="hidden" name="start_date" id="start_date" value="{{ $startDate }}">
                        <input type="hidden" name="end_date" id="end_date" value="{{ $endDate }}">

                        <button type="submit" class="btn btn-primary" style="margin-left: 8px;">
                            <i class="fa fa-search"></i> Terapkan
                        </button>

                        @if($startDate || $endDate)
                        <a href="{{ url()->current() }}" class="btn btn-default" style="margin-left: 4px;">
                            <i class="fa fa-refresh"></i> Reset
                        </a>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

>>>>>>> 9e8c2069fe474883803df35494add3af52868881
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
<<<<<<< HEAD
=======

{{-- CSS & JS ditaruh langsung di sini (bukan @push) supaya pasti ke-render
     walau layouts.master tidak punya @stack('css') / @stack('scripts').
     Kalau ternyata layout kamu SUDAH punya @stack tersebut dan sudah
     memuat plugin daterangepicker sendiri, blok ini aman untuk dihapus. --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-daterangepicker@3/daterangepicker.css">
<style>
    /* AdminLTE punya beberapa elemen dengan z-index tinggi (header, sidebar,
       overlay) yang bisa menutupi dropdown kalender kalau z-index-nya kalah. */
    .daterangepicker {
        z-index: 99999 !important;
    }
</style>

<script>
(function () {
    // Poll sampai jQuery benar-benar tersedia. Ini mengatasi kasus di mana
    // script ini dieksekusi lebih dulu dibanding jQuery milik layout
    // (misalnya jQuery baru dimuat di bagian bawah <body> oleh master layout).
    function whenJQueryReady(callback) {
        if (window.jQuery) {
            callback(window.jQuery);
        } else {
            setTimeout(function () { whenJQueryReady(callback); }, 50);
        }
    }

    function loadScriptOnce(id, src, onload) {
        if (document.getElementById(id)) {
            onload();
            return;
        }
        var script = document.createElement('script');
        script.id = id;
        script.src = src;
        script.onload = onload;
        document.head.appendChild(script);
    }

    whenJQueryReady(function ($) {
        loadScriptOnce('moment-js-cdn', 'https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js', function () {
            loadScriptOnce('daterangepicker-js-cdn', 'https://cdn.jsdelivr.net/npm/bootstrap-daterangepicker@3/daterangepicker.js', function () {
                initDateRangeFilter($);
            });
        });
    });

    function initDateRangeFilter($) {
        var $dateRangeInput = $('#date_range');

        if (!$dateRangeInput.length || typeof $dateRangeInput.daterangepicker !== 'function') {
            console.error('daterangepicker: plugin gagal dimuat atau input #date_range tidak ditemukan.');
            return;
        }

        var $startDateInput = $('#start_date');
        var $endDateInput = $('#end_date');
        var existingStart = "{{ $startDate }}";
        var existingEnd = "{{ $endDate }}";

        var pickerOptions = {
            autoUpdateInput: false,
            opens: 'left',
            parentEl: 'body',
            alwaysShowCalendars: true,
            showDropdowns: true,
            autoApply: true,
            linkedCalendars: false,
            locale: {
                format: 'DD/MM/YYYY',
                cancelLabel: 'Bersihkan',
                applyLabel: 'Terapkan',
                fromLabel: 'Dari',
                toLabel: 'Sampai',
                customRangeLabel: 'Kustom',
                daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
            },
            ranges: {
                'Hari Ini': [moment(), moment()],
                'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
                '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
                'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                'Bulan Lalu': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            },
        };

        if (existingStart && existingEnd) {
            pickerOptions.startDate = moment(existingStart);
            pickerOptions.endDate = moment(existingEnd);
        }

        $dateRangeInput.daterangepicker(pickerOptions);

        $dateRangeInput.on('apply.daterangepicker', function (ev, picker) {
            $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
            $startDateInput.val(picker.startDate.format('YYYY-MM-DD'));
            $endDateInput.val(picker.endDate.format('YYYY-MM-DD'));

            // Langsung submit form begitu tanggal ter-apply, supaya tidak
            // ada langkah manual lagi yang bisa salah klik / kelewat.
            $(this).closest('form').trigger('submit');
        });

        $dateRangeInput.on('cancel.daterangepicker', function () {
            $(this).val('');
            $startDateInput.val('');
            $endDateInput.val('');
        });
    }
})();
</script>
>>>>>>> 9e8c2069fe474883803df35494add3af52868881
@endsection

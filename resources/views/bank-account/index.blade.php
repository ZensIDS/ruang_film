@extends('layouts.master')
@section('container')
<section class="content-header">
    <h1>Rekening Pembayaran</h1>
</section>
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <a href="{{ route('bank-accounts.create') }}" class="btn btn-success">Tambah</a>
                </div>
                <div class="box-body table-responsive">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Rekening</th>
                                <th>Bank</th>
                                <th>No Rekening</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bankAccounts as $bankAccount)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $bankAccount->rek_name }}</td>
                                <td>{{ $bankAccount->rek_bank_name }}</td>
                                <td>{{ $bankAccount->rek_bank_no }}</td>
                                <td>{{ $bankAccount->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                                <td>
                                    <a href="{{ route('bank-accounts.edit', $bankAccount) }}" class="btn btn-warning btn-xs">Edit</a>
                                    <form action="{{ route('bank-accounts.destroy', $bankAccount) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Hapus rekening ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-xs">Hapus</button>
                                    </form>
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

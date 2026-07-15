@extends('layouts.master')
@section('container')
@include('bank-account.partials.form', [
    'title' => 'Tambah Rekening',
    'action' => route('bank-accounts.store'),
    'method' => 'POST',
    'bankAccount' => null,
])
@endsection

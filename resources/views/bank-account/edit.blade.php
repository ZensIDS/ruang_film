@extends('layouts.master')
@section('container')
@include('bank-account.partials.form', [
    'title' => 'Edit Rekening',
    'action' => route('bank-accounts.update', $bankAccount),
    'method' => 'PUT',
    'bankAccount' => $bankAccount,
])
@endsection

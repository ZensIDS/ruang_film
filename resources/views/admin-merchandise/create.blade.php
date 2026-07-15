@extends('layouts.master')
@section('container')
@include('admin-merchandise.partials.form', [
    'title' => 'Tambah Merchandise',
    'action' => route('admin-merchandises.store'),
    'method' => 'POST',
    'merchandise' => null,
])
@endsection

@extends('layouts.master')
@section('container')
@include('admin-merchandise.partials.form', [
    'title' => 'Edit Merchandise',
    'action' => route('admin-merchandises.update', $merchandise),
    'method' => 'PUT',
    'merchandise' => $merchandise,
])
@endsection

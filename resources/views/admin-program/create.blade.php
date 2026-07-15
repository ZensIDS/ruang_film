@extends('layouts.master')
@section('container')
@include('admin-program.partials.form', [
    'title' => 'Tambah Program',
    'action' => route('admin-programs.store'),
    'method' => 'POST',
    'program' => null,
])
@endsection

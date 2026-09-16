@extends('layouts.master')
@section('container')
@include('jury.partials.form', [
    'title' => 'Tambah Juri',
    'action' => route('juries.store'),
    'method' => 'POST',
    'jury' => null,
])
@endsection
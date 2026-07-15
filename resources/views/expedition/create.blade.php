@extends('layouts.master')
@section('container')
@include('expedition.partials.form', [
    'title' => 'Tambah Expedisi',
    'action' => route('expeditions.store'),
    'method' => 'POST',
    'expedition' => null,
])
@endsection

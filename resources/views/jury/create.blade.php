@extends('layouts.master')
@section('container')
@include('jury.partials.form', [
    'title' => 'Tambah Juri / Kurator',
    'action' => route('juries.store'),
    'method' => 'POST',
    'jury' => null,
    'type' => $type,
])
@endsection
@extends('layouts.master')
@section('container')
@include('jury.partials.form', [
    'title' => 'Edit Juri',
    'action' => route('juries.update', $jury->id),
    'method' => 'PUT',
    'jury' => $jury,
])
@endsection
@extends('layouts.master')
@section('container')
@include('expedition.partials.form', [
    'title' => 'Edit Expedisi',
    'action' => route('expeditions.update', $expedition),
    'method' => 'PUT',
    'expedition' => $expedition,
])
@endsection

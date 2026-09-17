@extends('layouts.master')
@section('container')
@include('jury.partials.form', [
    'title' => $jury->type === 'kurator' ? 'Edit Kurator' : 'Edit Juri',
    'action' => route('juries.update', $jury->id),
    'method' => 'PUT',
    'jury' => $jury,
])
@endsection
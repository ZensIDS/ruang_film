@extends('layouts.master')
@section('container')
@include('admin-program.partials.form', [
    'title' => 'Edit Program',
    'action' => route('admin-programs.update', $program),
    'method' => 'PUT',
    'program' => $program,
])
@endsection

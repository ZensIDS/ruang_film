@extends('layouts.master')
@section('container')
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Tambah Bioskop Gratis</h3>
                </div>
                <form action="{{ route('bioskops.store') }}" method="POST">
                    @csrf
                    @include('bioskop.partials.form', ['bioskop' => null])
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
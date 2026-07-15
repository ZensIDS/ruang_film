@extends('layouts.master')
@section('container')
<section class="content">
    <div class="row">
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><b>Edit Periode Submission</b></h3>
                </div>
                <form action="{{ route('settingUpdate', $submissionSetting) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="box-body">
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i>
                            Konten landing page sekarang dikelola terpisah dari halaman setting submission utama.
                        </div>
                        @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                        @include('submission-setting.partials.period-fields', ['submissionSettingForm' => $submissionSetting])
                    </div>
                    <div class="box-footer">
                        <a href="{{ route('settingIndex') }}" class="btn btn-default">Kembali</a>
                        <button type="submit" class="btn btn-primary">Simpan Periode</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

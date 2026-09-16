@extends('layouts.master')
@section('container')
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Edit FAQ</h3>
                </div>
                <form action="{{ route('faqs.update', $faq->id) }}" method="POST">
                    @method('PUT')
                    @csrf
                    <div class="box-body">
                        @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul style="margin-bottom:0;">
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                        <div class="form-group">
                            <label>Pertanyaan</label>
                            <input required type="text" class="form-control" name="question" value="{{ old('question', $faq->question) }}" placeholder="Masukkan pertanyaan">
                        </div>
                        <div class="form-group">
                            <label>Jawaban</label>
                            <textarea required name="answer" class="form-control" rows="4" placeholder="Masukkan jawaban">{{ old('answer', $faq->answer) }}</textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Urutan Tampil</label>
                                    <input type="number" class="form-control" name="sort_order" value="{{ old('sort_order', $faq->sort_order ?? 0) }}" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group" style="padding-top:25px;">
                                    <label>
                                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $faq->is_active ?? true) ? 'checked' : '' }}>
                                        Aktif ditampilkan
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="box-footer">
                        <a href="{{ route('faqs.index') }}" class="btn btn-default">Kembali</a>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
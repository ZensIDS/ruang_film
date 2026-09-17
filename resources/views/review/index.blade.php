@extends('layouts.master')
@section('container')
@php
    $user = auth()->user();
    $isAdmin = $user->hasRole(['admin', 'adminsub']);
    $canJudge = $user->hasRole('juri');
@endphp
<section class="content-header">
    <h1>Review Submission</h1>
</section>
<section class="content">

    <div class="row">
        {{-- Box Filter --}}
        <div class="col-md-7">
            <div class="box box-solid box-default review-box">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-filter"></i> Filter</h3>
                </div>
                <div class="box-body">
                    <form id="review-filter-form" class="review-inline-form">
                        <div class="form-group">
                            <label>Periode</label>
                            <select name="submission_setting_id" class="form-control">
                                <option value="">Semua Periode</option>
                                @foreach($submissionPeriods as $period)
                                <option value="{{ $period->id }}" {{ (string) $selectedSubmissionSettingId === (string) $period->id ? 'selected' : '' }}>
                                    {{ $period->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Kategori</label>
                            @if($canJudge)
                            <input type="hidden" name="category_id" value="{{ $selectedCategoryId }}">
                            <p class="form-control-static">{{ optional($user->category)->name ?: '-' }}</p>
                            @else
                            <select name="category_id" class="form-control">
                                <option value="">Semua Kategori</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ (string) $selectedCategoryId === (string) $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                            @endif
                        </div>
                        @if(!$canJudge)
                        <div class="form-group">
                            <label>Status</label>
                            <select name="curation_status" class="form-control">
                                <option value="">Semua Status</option>
                                @foreach($statusLabels as $statusValue => $statusLabel)
                                <option value="{{ $statusValue }}" {{ $selectedCurationStatus === $statusValue ? 'selected' : '' }}>
                                    {{ $statusLabel }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @else
                        <input type="hidden" name="curation_status" value="{{ \App\Models\Film::CURATION_APPROVED }}">
                        @endif
                        <input type="hidden" name="stage" value="{{ $stage }}">
                        <div class="form-group review-inline-form__submit">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-filter"></i> Terapkan Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Box Pencarian — sengaja dipisah dari box Filter --}}
        <div class="col-md-5">
            <div class="box box-solid box-default review-box">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-search"></i> Pencarian</h3>
                </div>
                <div class="box-body">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-search"></i></span>
                        <input
                            type="text"
                            id="review-search-input"
                            class="form-control"
                            value="{{ $search }}"
                            placeholder="Judul film, sutradara, produser, nama peserta, atau tim/komunitas..."
                        >
                        <span class="input-group-btn">
                            <button type="button" id="review-search-clear" class="btn btn-default" title="Hapus pencarian">
                                <i class="fa fa-times"></i>
                            </button>
                        </span>
                    </div>
                    <p class="text-muted review-search-hint">
                        <i class="fa fa-info-circle"></i> Ketik minimal 2 huruf, hasil otomatis diperbarui.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Box Hasil --}}
    <div class="row">
        <div class="col-md-12">
            <div class="box review-box">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-table"></i> Daftar Submission</h3>
                    <div class="box-tools">
                        <span id="review-loading-indicator" class="text-muted" style="display:none;">
                            <i class="fa fa-refresh fa-spin"></i> Memuat...
                        </span>
                    </div>
                </div>
                <div id="review-table-container" class="review-table-container">
                    @include('review.partials.table')
                </div>
            </div>
        </div>
    </div>

</section>

<style>
    .review-box .box-header.with-border { padding: 12px 15px; }
    .review-box .box-title { font-size: 15px; font-weight: 600; }
    .review-box .box-title i { margin-right: 6px; color: #3c8dbc; }

    .review-inline-form { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 14px; }
    .review-inline-form .form-group { margin-bottom: 0; min-width: 160px; flex: 1 1 160px; }
    .review-inline-form label { font-weight: 600; font-size: 12px; text-transform: uppercase; color: #777; margin-bottom: 4px; }
    .review-inline-form__submit { flex: 0 0 auto; min-width: 0; }

    .review-search-hint { margin: 8px 0 0; font-size: 12px; }

    .review-table-container { position: relative; min-height: 120px; }
    .review-table-container.is-loading { opacity: .45; pointer-events: none; transition: opacity .15s ease; }

    .review-pagination .pagination { margin: 0; }
</style>
@endsection

@push('scripts')
<script>
    (function () {
        const searchUrl    = @json(route('review.search'));
        const filterForm   = document.getElementById('review-filter-form');
        const searchInput  = document.getElementById('review-search-input');
        const clearBtn      = document.getElementById('review-search-clear');
        const container     = document.getElementById('review-table-container');
        const loadingBadge  = document.getElementById('review-loading-indicator');

        let debounceTimer = null;
        let activeRequest = null;

        function currentParams(extra) {
            const params = new URLSearchParams(new FormData(filterForm));
            if (searchInput.value.trim().length >= 2) {
                params.set('search', searchInput.value.trim());
            }
            if (extra) {
                Object.keys(extra).forEach(function (key) {
                    params.set(key, extra[key]);
                });
            }
            return params;
        }

        function loadTable(extra) {
            if (activeRequest) {
                activeRequest.abort();
            }

            container.classList.add('is-loading');
            loadingBadge.style.display = 'inline';

            const controller = new AbortController();
            activeRequest = controller;

            fetch(searchUrl + '?' + currentParams(extra).toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: controller.signal,
            })
                .then(function (response) { return response.text(); })
                .then(function (html) {
                    container.innerHTML = html;
                    bindPaginationLinks();
                })
                .catch(function (error) {
                    if (error.name !== 'AbortError') {
                        console.error('Gagal memuat data review:', error);
                    }
                })
                .finally(function () {
                    container.classList.remove('is-loading');
                    loadingBadge.style.display = 'none';
                    activeRequest = null;
                });
        }

        function bindPaginationLinks() {
            container.querySelectorAll('.pagination a[href]').forEach(function (link) {
                link.addEventListener('click', function (event) {
                    event.preventDefault();
                    const url = new URL(link.href, window.location.origin);
                    const page = url.searchParams.get('page') || 1;
                    loadTable({ page: page });
                    window.scrollTo({ top: container.offsetTop - 20, behavior: 'smooth' });
                });
            });
        }

        filterForm.addEventListener('submit', function (event) {
            event.preventDefault();
            loadTable({ page: 1 });
        });

        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                loadTable({ page: 1 });
            }, 400);
        });

        clearBtn.addEventListener('click', function () {
            searchInput.value = '';
            loadTable({ page: 1 });
        });

        bindPaginationLinks();
    })();
</script>
@endpush
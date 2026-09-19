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
                <div id="review-bulk-alert"></div>
                @if($isAdmin)
                {{-- Bulk update status. Ditaruh di luar #review-table-container supaya pilihan status
                     tidak ter-reset ketika tabel dimuat ulang (pindah halaman / ganti filter / cari). --}}
                <div class="review-bulk-bar" id="review-bulk-bar">
                    <div class="review-bulk-bar__count">
                        <i class="fa fa-check-square-o"></i>
                        <strong id="review-bulk-count">0</strong> film dipilih
                        <small class="text-muted" id="review-bulk-visible"></small>
                    </div>
                    <div class="review-bulk-bar__form">
                        <select id="review-bulk-status" class="form-control input-sm">
                            <option value="">-- Pilih status baru --</option>
                            @foreach($statusLabels as $statusValue => $statusLabel)
                            <option value="{{ $statusValue }}">{{ $statusLabel }}</option>
                            @endforeach
                        </select>
                        <button type="button" id="review-bulk-apply" class="btn btn-primary btn-sm" disabled>
                            <i class="fa fa-refresh"></i> Ubah Status Terpilih
                        </button>
                        <button type="button" id="review-bulk-clear" class="btn btn-default btn-sm" style="display:none;">
                            <i class="fa fa-times"></i> Kosongkan Pilihan
                        </button>
                    </div>
                    <small class="text-muted">Pilihan tetap tersimpan saat pindah halaman atau ganti filter/pencarian.</small>
                </div>
                @endif
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

    /* Bulk update status */
    #review-bulk-alert .alert { margin: 10px 15px 0; }
    .review-bulk-bar { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; padding: 10px 15px; background: #f7f9fb; border-bottom: 1px solid #e5e5e5; }
    .review-bulk-bar__count { font-size: 13px; white-space: nowrap; }
    .review-bulk-bar__form { display: flex; align-items: center; gap: 8px; }
    .review-bulk-bar__form .form-control { width: auto; min-width: 200px; }
    .review-row-selected > td { background-color: #eaf4fb !important; }
</style>
@endsection

@push('scripts')
<script>
    (function () {
        const searchUrl     = @json(route('review.search'));
        const bulkUrl       = @json(route('review.bulk-status'));
        const csrfToken     = @json(csrf_token());
        const approvedValue = @json(\App\Models\Film::CURATION_APPROVED);
        const filterForm    = document.getElementById('review-filter-form');
        const searchInput   = document.getElementById('review-search-input');
        const clearBtn      = document.getElementById('review-search-clear');
        const container     = document.getElementById('review-table-container');
        const loadingBadge  = document.getElementById('review-loading-indicator');

        // Elemen bulk update (null kalau yang login bukan admin)
        const bulkBar       = document.getElementById('review-bulk-bar');
        const bulkCountEl   = document.getElementById('review-bulk-count');
        const bulkVisibleEl = document.getElementById('review-bulk-visible');
        const bulkStatus    = document.getElementById('review-bulk-status');
        const bulkApplyBtn  = document.getElementById('review-bulk-apply');
        const bulkClearBtn  = document.getElementById('review-bulk-clear');

        // Semua ID film yang dicentang. Disimpan di sini (bukan di tabel) supaya tidak hilang
        // saat tabel dimuat ulang karena pindah halaman, ganti filter, atau pencarian.
        const selectedIds = new Set();

        let debounceTimer = null;
        let activeRequest = null;
        let isBulkSubmitting = false;
        let bulkAlertTimer = null;

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

            return fetch(searchUrl + '?' + currentParams(extra).toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: controller.signal,
            })
                .then(function (response) { return response.text(); })
                .then(function (html) {
                    container.innerHTML = html;
                    bindPaginationLinks();
                    restoreSelection();
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

        // ---------------------------------------------------------------
        // Bulk update status
        // ---------------------------------------------------------------

        // Centang ulang baris di tabel yang ID-nya sudah ada di selectedIds.
        // Dipanggil setiap kali tabel selesai dimuat ulang.
        function restoreSelection() {
            container.querySelectorAll('.review-row-check').forEach(function (check) {
                check.checked = selectedIds.has(check.value);
            });
            updateBulkState();
        }

        function updateBulkState() {
            if (!bulkBar) {
                return; // bukan admin, toolbar bulk tidak ada
            }

            const checks = container.querySelectorAll('.review-row-check');
            const checkAll = document.getElementById('review-check-all');
            let visibleChecked = 0;

            checks.forEach(function (check) {
                if (check.checked) {
                    visibleChecked++;
                }
                const row = check.closest('tr');
                if (row) {
                    row.classList.toggle('review-row-selected', check.checked);
                }
            });

            bulkCountEl.textContent = selectedIds.size;
            bulkVisibleEl.textContent = selectedIds.size ? '(' + visibleChecked + ' di halaman ini)' : '';
            bulkClearBtn.style.display = selectedIds.size ? '' : 'none';

            if (checkAll) {
                checkAll.checked = checks.length > 0 && visibleChecked === checks.length;
                checkAll.indeterminate = visibleChecked > 0 && visibleChecked < checks.length;
            }

            bulkApplyBtn.disabled = isBulkSubmitting || selectedIds.size === 0 || !bulkStatus.value;
        }

        function showBulkAlert(type, text) {
            const holder = document.getElementById('review-bulk-alert');
            if (!holder) {
                return;
            }

            clearTimeout(bulkAlertTimer);
            holder.innerHTML = '';

            const box = document.createElement('div');
            box.className = 'alert alert-' + type + ' alert-dismissible';

            const closeBtn = document.createElement('button');
            closeBtn.type = 'button';
            closeBtn.className = 'close';
            closeBtn.innerHTML = '&times;';
            closeBtn.addEventListener('click', function () { holder.innerHTML = ''; });

            const message = document.createElement('span');
            message.textContent = text; // textContent, bukan innerHTML, biar aman dari injeksi HTML

            box.appendChild(closeBtn);
            box.appendChild(message);
            holder.appendChild(box);

            if (type === 'success') {
                bulkAlertTimer = setTimeout(function () { holder.innerHTML = ''; }, 6000);
            }
        }

        function extractBulkError(result) {
            const data = result.data || {};

            if (data.errors) {
                const firstKey = Object.keys(data.errors)[0];
                if (firstKey && data.errors[firstKey].length) {
                    return data.errors[firstKey][0];
                }
            }
            if (result.status === 419) {
                return 'Sesi halaman sudah kedaluwarsa. Muat ulang halaman lalu coba lagi.';
            }
            if (result.status === 403) {
                return 'Anda tidak memiliki akses untuk mengubah status.';
            }

            return data.message || 'Gagal mengubah status (kode ' + result.status + ').';
        }

        function reloadAfterBulk(page) {
            return loadTable({ page: page }).then(function () {
                // Kalau halaman yang sedang dibuka jadi kosong (mis. semua film di halaman itu
                // pindah status dan filter status sedang aktif), balik ke halaman 1.
                if (Number(page) > 1 && !container.querySelector('.review-row-check')) {
                    return loadTable({ page: 1 });
                }
            });
        }

        function submitBulk() {
            if (isBulkSubmitting || selectedIds.size === 0 || !bulkStatus.value) {
                return;
            }

            const ids = Array.from(selectedIds);
            const visibleChecked = container.querySelectorAll('.review-row-check:checked').length;
            const otherCount = ids.length - visibleChecked;
            const statusLabel = bulkStatus.options[bulkStatus.selectedIndex].text;

            let confirmText = 'Ubah status ' + ids.length + ' film menjadi "' + statusLabel + '"?';
            if (otherCount > 0) {
                confirmText += '\n\n' + visibleChecked + ' film ada di halaman ini, dan ' + otherCount +
                    ' film lainnya dipilih dari halaman/filter lain.';
            }
            if (bulkStatus.value !== approvedValue) {
                confirmText += '\n\nPeringkat juara pada film terpilih (jika ada) akan dikosongkan.';
            }
            if (!window.confirm(confirmText)) {
                return;
            }

            const pageEl = container.querySelector('[data-page]');
            const page = pageEl ? (pageEl.dataset.page || 1) : 1;
            const originalLabel = bulkApplyBtn.innerHTML;

            isBulkSubmitting = true;
            bulkApplyBtn.disabled = true;
            bulkApplyBtn.innerHTML = '<i class="fa fa-refresh fa-spin"></i> Memproses...';

            fetch(bulkUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ curation_status: bulkStatus.value, film_ids: ids }),
            })
                .then(function (response) {
                    return response.json()
                        .catch(function () { return {}; })
                        .then(function (data) {
                            return { ok: response.ok, status: response.status, data: data };
                        });
                })
                .then(function (result) {
                    if (!result.ok) {
                        throw new Error(extractBulkError(result));
                    }
                    if (typeof result.data.count === 'undefined') {
                        throw new Error('Respons server tidak dikenali. Kemungkinan sesi login berakhir, muat ulang halaman.');
                    }

                    // Berhasil: kosongkan pilihan, baru muat ulang tabel.
                    // Kalau gagal, pilihan sengaja dibiarkan supaya bisa dicoba lagi.
                    selectedIds.clear();
                    showBulkAlert('success', result.data.message);
                    return reloadAfterBulk(page);
                })
                .catch(function (error) {
                    showBulkAlert('danger', error.message || 'Terjadi kesalahan saat mengubah status.');
                })
                .finally(function () {
                    isBulkSubmitting = false;
                    bulkApplyBtn.innerHTML = originalLabel;
                    updateBulkState();
                });
        }

        if (bulkBar) {
            bulkStatus.addEventListener('change', updateBulkState);
            bulkApplyBtn.addEventListener('click', submitBulk);
            bulkClearBtn.addEventListener('click', function () {
                selectedIds.clear();
                restoreSelection();
            });
        }

        // Checkbox di dalam tabel: pakai event delegation di container karena isi tabel
        // diganti (innerHTML) setiap kali dimuat ulang.
        container.addEventListener('change', function (event) {
            const target = event.target;

            if (target.id === 'review-check-all') {
                // "Pilih semua" hanya berlaku untuk baris di halaman yang sedang tampil.
                container.querySelectorAll('.review-row-check').forEach(function (check) {
                    check.checked = target.checked;
                    if (target.checked) {
                        selectedIds.add(check.value);
                    } else {
                        selectedIds.delete(check.value);
                    }
                });
                updateBulkState();
            } else if (target.classList.contains('review-row-check')) {
                if (target.checked) {
                    selectedIds.add(target.value);
                } else {
                    selectedIds.delete(target.value);
                }
                updateBulkState();
            }
        });

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
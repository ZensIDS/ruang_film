<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link rel="icon" href="{{ asset('img/logo.png') }}" type="image/png">
    <link rel="stylesheet" href="{{ asset('assets/zenTheme/css/login.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/zenTheme/css/materialdesignicons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/zenTheme/css/bootsrtap.css') }}">
    <link href="https://fonts.googleapis.com/css?family=Quicksand" rel="stylesheet">

    <link type='text/css' href="{{ asset('assets/zenTheme/css/siteman.css') }}" rel='Stylesheet' />
    <link rel="shortcut icon" href="{{ asset('img/logo.png') }}" />
</head>

<body>
    @php
    $submissionOpen = $submissionOpen ?? false;
    $selectedRole = old('role', $selectedRole ?? 'umum');
    @endphp
    <main class="d-flex align-items-center min-vh-100 py-3 py-md-0">
        <div class="container">
            <div class="card login-card shadow-sm" style="border-radius: 16px; max-width: 420px; margin: 0 auto;">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-3">
                        <img src="{{ asset('img/logo.png') }}" width="300px" alt="Logo" class="mb-2">
                        <p class="mb-3"><a href="#" style="color:#1a2aee; font-weight:600; font-size:18px;">FORM PENDAFTARAN FFH 2026</a></p>
                    </div>

                    <form class="login-form" action="{{ route('registStore') }}" method="post">
                        @csrf

                        <div class="form-group mb-3 @error('role') has-error @enderror">
                            <label class="d-block text-muted small mb-2">Pilih Jenis Akun</label>
                            <div class="d-flex rounded-pill border overflow-hidden">
                                <label class="flex-fill mb-0 text-center py-2 {{ $selectedRole === 'umum' ? 'bg-dark text-white' : 'bg-white text-dark' }}" style="cursor:pointer;">
                                    <input type="radio" name="role" value="umum" {{ $selectedRole === 'umum' ? 'checked' : '' }} hidden>
                                    Umum
                                </label>
                                <label class="flex-fill mb-0 text-center py-2 {{ $selectedRole === 'peserta' ? 'bg-dark text-white' : 'bg-white text-dark' }} {{ !$submissionOpen ? 'text-muted' : '' }}" style="cursor:{{ $submissionOpen ? 'pointer' : 'not-allowed' }};">
                                    <input type="radio" name="role" value="peserta" {{ $selectedRole === 'peserta' ? 'checked' : '' }} hidden {{ !$submissionOpen ? 'disabled' : '' }}>
                                    Peserta
                                </label>
                            </div>
                            @if(!$submissionOpen)
                            <span class="text-muted small d-block mt-2">Pendaftaran peserta sedang ditutup. Akun umum tetap tersedia untuk pembeli merchandise.</span>
                            @endif
                            @error('role')
                            <span class="text-danger small ms-2">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Nama Lengkap --}}
                        <div class="form-group mb-3 @error('name') has-error @enderror">
                            <input type="text" name="name" id="name"
                                class="form-control rounded-pill @error('name') is-invalid @enderror"
                                placeholder="Nama Lengkap"
                                value="{{ old('name') }}" required>
                            @error('name')
                            <span class="text-danger small ms-2">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Nomor WhatsApp --}}
                        <div class="form-group mb-3 @error('no_hp') has-error @enderror">
                            <input type="tel" name="no_hp" id="no_hp"
                                class="form-control rounded-pill @error('no_hp') is-invalid @enderror"
                                placeholder="Nomor WhatsApp Aktif (cth: 08123456789)"
                                value="{{ old('no_hp') }}" required>
                            @error('no_hp')
                            <span class="text-danger small ms-2">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="form-group mb-3 @error('email') has-error @enderror">
                            <input type="email" name="email" id="email"
                                class="form-control rounded-pill @error('email') is-invalid @enderror"
                                placeholder="Email"
                                value="{{ old('email') }}" required>
                            @error('email')
                            <span class="text-danger small ms-2">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Password --}}
                        <div class="form-group mb-3 @error('password') has-error @enderror">
                            <input type="password" name="password" id="password"
                                class="form-control rounded-pill @error('password') is-invalid @enderror"
                                placeholder="Password (min. 8 karakter)" required>
                            @error('password')
                            <span class="text-danger small ms-2">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-3 @error('category_id') has-error @enderror" id="category-field">
                            <select name="category_id" id="category_id"
                                class="@error('category_id') is-invalid @enderror"
                                style="width:100%; padding:10px 16px; border:1.5px solid #e0e0e0; border-radius:50px; font-size:14px; color:#555; background:#fff; outline:none;">
                                <option value="" disabled selected>Pilih Kategori *</option>
                                @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('category_id')
                            <span class="text-danger small ms-2">{{ $message }}</span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-dark btn-block rounded-pill w-100 mt-2">
                            DAFTAR
                        </button>
                    </form>
                    <br>
                    <p class="text-center">Sudah Memiliki Akun ? <a href="{{ route('login', ['role' => $selectedRole]) }}">Login Disini !</a></p>
                </div>
            </div>
        </div>
    </main>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    <script>
        const roleInputs = document.querySelectorAll('input[name="role"]');
        const categoryField = document.getElementById('category-field');
        const categorySelect = document.getElementById('category_id');

        function updateRoleState() {
            const selectedRole = document.querySelector('input[name="role"]:checked')?.value || 'umum';
            document.querySelectorAll('input[name="role"]').forEach(function(input) {
                const label = input.closest('label');
                if (!label) return;

                label.classList.toggle('bg-dark', input.checked);
                label.classList.toggle('text-white', input.checked);
                label.classList.toggle('bg-white', !input.checked);
                label.classList.toggle('text-dark', !input.checked);
            });

            const isParticipant = selectedRole === 'peserta';
            categoryField.style.display = isParticipant ? 'block' : 'none';
            categorySelect.required = isParticipant;

            if (!isParticipant) {
                categorySelect.value = '';
            }
        }

        roleInputs.forEach(function(input) {
            input.addEventListener('change', updateRoleState);
        });

        document.querySelectorAll('#category_id option').forEach(function(opt) {
            if (opt.text.length > 35) {
                opt.dataset.fulltext = opt.text;
                opt.text = opt.text.substring(0, 35) + '...';
            }
        });

        updateRoleState();
    </script>
</body>

</html>

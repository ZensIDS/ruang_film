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
    $selectedRole = $selectedRole ?? 'umum';
    @endphp
    <main class="d-flex align-items-center min-vh-100 py-3 py-md-0">
        <div class="container">
            <div class="card login-card shadow-sm" style="border-radius: 16px; max-width: 420px; margin: 0 auto;">
                <div class="card-body p-4 p-md-5">
                    @if(session('success'))
                    <div style="
                        background: #d1fae5;
                        color: #065f46;
                        border: 1px solid #6ee7b7;
                        border-radius: 50px;
                        padding: 10px 16px;
                        font-size: 13px;
                        text-align: center;
                        margin-bottom: 16px;
                    ">
                        ✓ {{ session('success') }}
                    </div>
                    @endif
                    <div class="text-center mb-3">
                        <img src="{{ asset('img/logo.png') }}" width="300px" alt="Logo" class="mb-2">
                        <!-- <p class="mb-3"><a href="#" style="color:#1a2aee; font-weight:600; font-size:18px;">SIMAP</a></p> -->
                    </div>

                    <div class="mb-4">
                        <p class="text-center text-muted small mb-2">Pilih jenis akun</p>
                        <div class="d-flex rounded-pill border overflow-hidden">
                            <a href="{{ route('login', ['role' => 'umum']) }}"
                                class="flex-fill text-center py-2 {{ $selectedRole === 'umum' ? 'bg-dark text-white' : 'bg-white text-dark' }}"
                                style="text-decoration:none;">Umum</a>
                            <a href="{{ route('login', ['role' => 'peserta']) }}"
                                class="flex-fill text-center py-2 {{ $selectedRole === 'peserta' ? 'bg-dark text-white' : 'bg-white text-dark' }}"
                                style="text-decoration:none;">Peserta</a>
                        </div>
                        <p class="text-center text-muted small mt-2 mb-0">
                            {{ $selectedRole === 'peserta'
                                ? 'Masuk untuk submission film dan akses dashboard peserta.'
                                : 'Masuk untuk belanja merchandise, biodata pembeli, dan invoice.' }}
                        </p>
                    </div>

                    <form class="login-form" action="{{ route('authenticate') }}" method="post">
                        @csrf

                        <div class="form-group mb-3 @error('email') has-error @enderror">
                            <input type="text" name="email" id="email"
                                class="form-control rounded-pill"
                                placeholder="Email"
                                value="{{ old('email') }}">
                            @error('email')
                            <span class="help-block text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-3 @error('password') has-error @enderror">
                            <input type="password" name="password" id="password"
                                class="form-control rounded-pill"
                                placeholder="Password">
                            @error('password')
                            <span class="help-block text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <button class="btn btn-dark btn-block rounded-pill w-100 mt-2" type="submit">
                            LOGIN
                        </button>
                    </form>
                    <br>
                    @php
                    $submissionOpen = \App\Models\SubmissionSetting::isOpen();
                    @endphp
                    <p class="text-center">Belum Memiliki Akun ? <a href="{{ route('register', ['role' => $selectedRole]) }}">Daftar Disini !</a></p>
                    @if(!$submissionOpen)
                    <p class="text-center text-muted small mb-2">Pendaftaran peserta sedang ditutup, tetapi akun umum tetap bisa dibuat untuk pembelian merchandise.</p>
                    @endif
                    <p class="text-center"><a href="/">Kembali Ke Halaman Utama</a></p>
                </div>
            </div>
        </div>
    </main>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
</body>

</html>

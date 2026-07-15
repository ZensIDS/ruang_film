<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">

        <!-- Sidebar user panel (optional) -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="{{ asset('img/logo.png') }}" class="img-circle" alt="User Image"><br>
            </div>
            <div class="pull-left info">
                <p>{{ Auth::user()->name }}</p>
                <p>Festival Film Horor 2026</p>
            </div>
        </div>

        @if (Auth::user()->role == 'admin')
        <!-- Sidebar Menu -->
        <ul class="sidebar-menu">
            <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}"><i class="fa fa-tachometer"></i> <span>Dashboard</span></a>
            </li>
            <li class="header" style="color:#d8b4fe;background-color:rgba(124,58,237,0.16);border-left:3px solid #a855f7;">MASTER DATA</li>
            <li class="{{ request()->routeIs('categories.*') ? 'active' : '' }}">
                <a href="{{ route('categories.index') }}"><i class="fa fa-film"></i> <span>Kategori Film</span></a>
            </li>
            <li class="treeview {{ request()->routeIs('program-categories.*') || request()->routeIs('admin-programs.*') ? 'active menu-open' : '' }}">
                <a href="#">
                    <i class="fa fa-calendar"></i> <span>Data Program</span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu" style="{{ request()->routeIs('program-categories.*') || request()->routeIs('admin-programs.*') ? 'display:block;' : '' }}">
                    <li class="{{ request()->routeIs('program-categories.*') ? 'active' : '' }}">
                        <a href="{{ route('program-categories.index') }}"><i class="fa fa-circle-o"></i> Kategori Program</a>
                    </li>
                    <li class="{{ request()->routeIs('admin-programs.*') ? 'active' : '' }}">
                        <a href="{{ route('admin-programs.index') }}"><i class="fa fa-circle-o"></i> Program Festival</a>
                    </li>
                </ul>
            </li>
            <li class="treeview {{ request()->routeIs('merchandise-categories.*') || request()->routeIs('admin-merchandises.*') || request()->routeIs('admin.orders.*') ? 'active menu-open' : '' }}">
                <a href="#">
                    <i class="fa fa-dropbox"></i> <span>Data Merchandise</span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu" style="{{ request()->routeIs('merchandise-categories.*') || request()->routeIs('admin-merchandises.*') ? 'display:block;' : '' }}">
                    <li class="{{ request()->routeIs('merchandise-categories.*') ? 'active' : '' }}">
                        <a href="{{ route('merchandise-categories.index') }}"><i class="fa fa-circle-o"></i> Kategori Merchandise</a>
                    </li>
                    <li class="{{ request()->routeIs('admin-merchandises.*') ? 'active' : '' }}">
                        <a href="{{ route('admin-merchandises.index') }}"><i class="fa fa-circle-o"></i> Merchandise</a>
                    </li>
                    <li class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.orders.index') }}"><i class="fa fa-credit-card"></i> <span>Invoice Merchandise</span></a>
                    </li>
                </ul>
            </li>
            <li class="{{ request()->routeIs('expeditions.*') ? 'active' : '' }}">
                <a href="{{ route('expeditions.index') }}"><i class="fa fa-truck"></i> <span>Expedisi</span></a>
            </li>
            <li class="{{ request()->routeIs('bank-accounts.*') ? 'active' : '' }}">
                <a href="{{ route('bank-accounts.index') }}"><i class="fa fa-bank"></i> <span>Rekening</span></a>
            </li>
            <li class="header" style="color:#d8b4fe;background-color:rgba(124,58,237,0.16);border-left:3px solid #a855f7;">OPERASIONAL</li>
            <li class="{{ request()->routeIs('film.*') ? 'active' : '' }}">
                <a href="{{ route('film.index') }}"><i class="fa fa-file-text"></i> <span>Submission</span></a>
            </li>
            <li class="{{ request()->routeIs('review.*') ? 'active' : '' }}">
                <a href="{{ route('review.index') }}"><i class="fa fa-check-square-o"></i> <span>Review Submission</span></a>
            </li>
            <li class="{{ request()->routeIs('users.index.author') || request()->routeIs('users.show') ? 'active' : '' }}">
                <a href="{{ route('users.index.author') }}"><i class="fa fa-group"></i> <span>Data Peserta</span></a>
            </li>
            <li class="{{ request()->routeIs('users.index') || request()->routeIs('users.index.kurator') ? 'active' : '' }}">
                <a href="{{ route('users.index') }}"><i class="fa fa-user"></i> <span>Data Pengguna</span></a>
            </li>
            <li class="header" style="color:#d8b4fe;background-color:rgba(124,58,237,0.16);border-left:3px solid #a855f7;">KONFIGURASI</li>
            <li class="{{ request()->routeIs('settingIndex') ? 'active' : '' }}">
                <a href="{{ route('settingIndex') }}"><i class="fa fa-gear"></i> <span>Setting Submission</span></a>
            </li>
        </ul><!-- /.sidebar-menu -->
        @endif

        @if (Auth::user()->role == 'adminsub')
        <!-- Sidebar Menu -->
        <ul class="sidebar-menu">
            <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}"><i class="fa fa-tachometer"></i> <span>Dashboard</span></a>
            </li>
            <li class="{{ request()->routeIs('film.*') ? 'active' : '' }}">
                <a href="{{ route('film.index') }}"><i class="fa fa-file-text"></i> <span>Submission</span></a>
            </li>
            <li class="{{ request()->routeIs('review.*') ? 'active' : '' }}">
                <a href="{{ route('review.index') }}"><i class="fa fa-check-square-o"></i> <span>Review Submission</span></a>
            </li>
            <li class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                <a href="{{ route('admin.orders.index') }}"><i class="fa fa-credit-card"></i> <span>Invoice Merchandise</span></a>
            </li>
            <li class="{{ request()->routeIs('users.index.author') || request()->routeIs('users.show') ? 'active' : '' }}">
                <a href="{{ route('users.index.author') }}"><i class="fa fa-group"></i> <span>Data Peserta</span></a>
            </li>
        </ul><!-- /.sidebar-menu -->
        @endif

        @if (Auth::user()->role == 'kurator')
        <ul class="sidebar-menu">
            <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}"><i class="fa fa-tachometer"></i> <span>Dashboard</span></a>
            </li>
            <li class="{{ request()->routeIs('review.*') ? 'active' : '' }}">
                <a href="{{ route('review.index') }}"><i class="fa fa-check-square-o"></i> <span>Kurasi Submission</span></a>
            </li>
        </ul>
        @endif

        @if (Auth::user()->role == 'juri')
        <ul class="sidebar-menu">
            <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}"><i class="fa fa-tachometer"></i> <span>Dashboard</span></a>
            </li>
            <li class="{{ request()->routeIs('review.*') ? 'active' : '' }}">
                <a href="{{ route('review.index') }}"><i class="fa fa-trophy"></i> <span>Penilaian Juri</span></a>
            </li>
        </ul>
        @endif

        @if (Auth::user()->role == 'peserta')
        <!-- Sidebar Menu -->
        <ul class="sidebar-menu">
            <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}"><i class="fa fa-tachometer"></i> <span>Dashboard</span></a>
            </li>
            <li class="{{ request()->routeIs('user-detail.*') ? 'active' : '' }}">
                <a href="{{ route('user-detail.index') }}"><i class="fa fa-user"></i> <span>Biodata</span></a>
            </li>
            <li class="{{ request()->routeIs('film.*') ? 'active' : '' }}">
                <a href="{{ route('film.index') }}"><i class="fa fa-file-text"></i> <span>Submission</span></a>
            </li>
            <li>
                <a href="{{ route('orders.index') }}"><i class="fa fa-credit-card"></i> <span>Invoice Merchandise</span></a>
            </li>
        </ul><!-- /.sidebar-menu -->
        @endif
    </section>
    <!-- /.sidebar -->
</aside>

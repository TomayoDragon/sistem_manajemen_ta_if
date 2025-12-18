<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'Dashboard Staff' }} - IF Ubaya</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap" rel="stylesheet">

    <!-- 1. CSS DATATABLES -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* CSS Reset & Global */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Lato', sans-serif;
        }

        body,
        html {
            height: 100%;
            background-color: #f4f7f6;
        }

        /* --- FIX TAMPILAN DATATABLES --- */
        .dataTables_wrapper .dataTables_length select {
            padding-right: 30px;
            background-position: right 0.5rem center;
        }

        .dataTables_wrapper {
            padding-top: 10px;
        }

        /* --- Struktur Layout Utama --- */
        .layout-container {
            display: flex;
            min-height: 100vh;
        }

        /* --- Sidebar (Kiri) --- */
        .sidebar {
            width: 260px;
            background-color: #0a2e6c;
            /* Biru tua IF */
            color: white;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100%;
        }

        .sidebar-header {
            padding: 24px 20px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #1e4a9c;
        }

        .sidebar-header img {
            width: 50px;
            margin-right: 15px;
        }

        .sidebar-header .logo-text {
            display: flex;
            flex-direction: column;
        }

        .sidebar-header .logo-text span:first-child {
            font-weight: 700;
            font-size: 1.1rem;
        }

        .sidebar-header .logo-text span:last-child {
            font-size: 0.9rem;
            color: #bdc3c7;
        }

        .sidebar-nav {
            flex-grow: 1;
            list-style: none;
            margin-top: 20px;
        }

        .sidebar-nav li {
            width: 100%;
        }

        .sidebar-nav li a {
            display: flex;
            align-items: center;
            padding: 18px 25px;
            color: #ecf0f1;
            text-decoration: none;
            font-size: 1rem;
            transition: background-color 0.2s;
        }

        .sidebar-nav li a i {
            width: 30px;
            font-size: 1.1rem;
            margin-right: 10px;
        }

        /* Active Link Style */
        .sidebar-nav li a.active {
            background-color: #1e4a9c;
            font-weight: 700;
            border-left: 5px solid #3498db;
            padding-left: 20px;
        }

        .sidebar-nav li a:hover:not(.active) {
            background-color: #1a4a9c;
        }

        /* --- Area Konten Utama (Kanan) --- */
        .main-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            margin-left: 260px;
        }

        /* --- Topbar --- */
        .topbar {
            width: 100%;
            background-color: #ffffff;
            border-bottom: 1px solid #ddd;
            padding: 20px 30px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }

        .user-info {
            display: flex;
            align-items: center;
            text-align: right;
        }

        .user-info .user-details {
            margin-right: 15px;
        }

        .user-info .user-name {
            font-weight: 700;
            color: #333;
        }

        .user-info .user-nrp {
            font-size: 0.9rem;
            color: #777;
        }

        .user-info .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background-color: #0a2e6c;
        }

        .user-info-trigger {
            display: flex;
            align-items: center;
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
            border-radius: 8px;
            transition: background-color 0.2s;
        }

        .user-info-trigger:hover {
            background-color: #f0f0f0;
        }

        .dropdown-menu {
            position: absolute;
            top: 80px;
            right: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border: 1px solid #ddd;
            z-index: 50;
            min-width: 200px;
            display: none;
        }

        .dropdown-menu[x-show] {
            display: block;
        }

        .dropdown-menu a {
            display: block;
            padding: 12px 20px;
            text-decoration: none;
            color: #333;
            font-size: 1rem;
            transition: background-color 0.2s;
        }

        .dropdown-menu a:hover {
            background-color: #f9f9f9;
        }

        .dropdown-menu form a {
            color: #d9534f;
        }

        .dropdown-menu form a:hover {
            background-color: #fdf2f2;
        }

        /* --- Konten --- */
        .content-area {
            flex-grow: 1;
            padding: 30px;
            background-color: #f4f7f6;
        }

        .content-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
        }

        .content-box {
            background-color: #ffffff;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            padding: 30px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>

<body>

    <div class="layout-container">

        <nav class="sidebar">
            <div class="sidebar-header">
                <!-- Ganti dengan path logo yang benar -->
                <img src="{{ asset('images/logo_layout.png') }}" alt="Logo IF">
                <div class="logo-text">
                    <span>Informatics</span>
                    <span>Ubaya</span>
                </div>
            </div>

            <ul class="sidebar-nav">
                <!-- Menu Staff: Dashboard -->
                <li>
                    <a href="{{ route('staff.dashboard') }}"
                        class="{{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">
                        <i class="fa-solid fa-check-to-slot"></i>
                        Validasi Berkas
                    </a>
                </li>
                <!-- Menu Staff: Arsip -->
                <li>
                    <a href="{{ route('staff.arsip.index') }}"
                        class="{{ request()->routeIs('staff.arsip.index') ? 'active' : '' }}">
                        <i class="fa-solid fa-archive"></i>
                        Arsip TA
                    </a>
                </li>
                <li>
                    <a href="{{ route('staff.periode.create') }}"
                        class="{{ request()->routeIs('staff.periode.create') ? 'active' : '' }}">
                        <i class="fa-solid fa-calendar-plus"></i> Tambah Periode
                    </a>
                </li>
                <li>
                    <a href="{{ route('staff.jadwal.monitoring') }}"
                        class="{{ request()->routeIs('staff.jadwal.monitoring') ? 'active' : '' }}">
                        <i class="fa-solid fa-desktop"></i> Monitoring Jadwal
                    </a>
                </li>
            </ul>
        </nav>

        <div class="main-wrapper">

            <header class="topbar">
                <div class="user-info" x-data="{ open: false }">
                    <button @click="open = ! open" class="user-info-trigger">
                        <div class="user-details">
                            <!-- Tampilkan Nama Staff -->
                            <div class="user-name">{{ Auth::user()->staff->nama_lengkap ?? 'Staff Admin' }}</div>
                            <div class="user-nrp">{{ Auth::user()->staff->npk ?? '-' }}</div>
                        </div>
                        <div class="user-avatar"></div>
                    </button>

                    <div x-show="open" @click.outside="open = false" x-transition class="dropdown-menu">
                        <a href="{{ route('profile.edit') }}">Profil Saya</a>
                        <div style="border-top: 1px solid #eee;"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <a href="{{ route('logout') }}"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                Log Out
                            </a>
                        </form>
                    </div>
                </div>
            </header>

            <main class="content-area">
                {{ $slot }}
            </main>

        </div>

    </div>

    <!-- 2. JS DATATABLES & STACK SCRIPTS -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    @stack('scripts')

</body>

</html>
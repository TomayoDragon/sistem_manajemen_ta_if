<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>{{ $title ?? 'Dashboard Dosen' }} - IF Ubaya</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* CSS Reset & Global */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Lato', sans-serif;
        }

        body, html {
            height: 100%;
            background-color: #f4f7f6;
        }

        /* --- Struktur Layout Utama --- */
        .layout-container {
            display: flex;
            min-height: 100vh;
        }

        /* --- Sidebar (Kiri) --- */
        .sidebar {
            width: 260px;
            background-color: #0a2e6c; /* Biru tua IF */
            color: white;
            display: flex;
            flex-direction: column;
            position: fixed; /* Sidebar tetap */
            height: 100%;
        }

        .sidebar-header {
            height: 90px; /* Tinggi tetap */
            padding: 0 25px; 
            display: flex;
            align-items: center; /* Vertikal center */
            border-bottom: 1px solid #1e4a9c;
        }

        .sidebar-header img {
            width: 55px;
            height: auto;
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

        .sidebar-nav li { width: 100%; }

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
            width: 30px; /* Beri jarak untuk ikon */
            font-size: 1.1rem;
            margin-right: 10px;
        }
        
        .sidebar-nav li a.active {
            background-color: #1e4a9c; /* Biru lebih muda */
            font-weight: 700;
            border-left: 5px solid #3498db; /* Highlight biru cerah */
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
            margin-left: 260px; /* Sesuaikan dengan lebar sidebar */
        }

        /* --- Topbar (Header Kanan) --- */
        .topbar {
            width: 100%;
            background-color: #ffffff;
            border-bottom: 1px solid #ddd;
            padding: 20px 30px;
            display: flex;
            justify-content: flex-end; /* Posisikan ke kanan */
            align-items: center;
        }

        /* ... (Style untuk .user-info, .dropdown-menu, .user-info-trigger) ... */
        .user-info { display: flex; align-items: center; text-align: right; }
        .user-info .user-details { margin-right: 15px; }
        .user-info .user-name { font-weight: 700; color: #333; }
        .user-info .user-nrp { font-size: 0.9rem; color: #777; }
        .user-info .user-avatar { width: 45px; height: 45px; border-radius: 50%; background-color: #0a2e6c; }

        .user-info-trigger {
            display: flex; align-items: center; background: none; 
            border: none; cursor: pointer; padding: 5px;
            border-radius: 8px; transition: background-color 0.2s;
        }
        .user-info-trigger:hover { background-color: #f0f0f0; }
        .dropdown-menu {
            position: absolute; top: 80px; right: 30px; background: white;
            border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border: 1px solid #ddd; z-index: 50; min-width: 200px;
            display: none; 
        }
        .dropdown-menu[x-show] { display: block; }
        .dropdown-menu a { display: block; padding: 12px 20px; text-decoration: none; color: #333; font-size: 1rem; transition: background-color 0.2s; }
        .dropdown-menu a:hover { background-color: #f9f9f9; }
        .dropdown-menu form a { color: #d9534f; }
        .dropdown-menu form a:hover { background-color: #fdf2f2; }

        /* --- Konten (Slot) --- */
        .content-area {
            flex-grow: 1;
            padding: 30px;
            background-color: #f4f7f6;
        }

        /* --- Style Konten Generik (dipakai di dashboard, upload, dll) --- */
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
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

    </style>
</head>
<body>
    
    <div class="layout-container">

        <nav class="sidebar">
            <div class="sidebar-header">
                <img src="{{ asset('images/logo-layout.png') }}" alt="Logo IF">
                <div class="logo-text">
                    <span>Informatics</span>
                    <span>Ubaya</span>
                </div>
            </div>
            
            <ul class="sidebar-nav">
                <li>
                    <a href="{{ route('dosen.dashboard') }}" 
                       class="{{ request()->routeIs('dosen.dashboard') ? 'active' : '' }}">
                        <i class="fa-solid fa-house"></i>
                        Dashboard (Jadwal)
                    </a>
                </li>
                <li>
                    <a href="{{ route('dosen.bimbingan.index') }}"
                       class="{{ request()->routeIs('dosen.bimbingan.index') ? 'active' : '' }}">
                        <i class="fa-solid fa-users"></i>
                        Mahasiswa Bimbingan
                    </a>
                </li>
            </ul>
        </nav>

        <div class="main-wrapper">

            <header class="topbar">
                <div class="user-info" x-data="{ open: false }">
                    <button @click="open = ! open" class="user-info-trigger">
                        <div class="user-details">
                            <div class="user-name">{{ Auth::user()->dosen->nama_lengkap ?? 'Nama Dosen' }}</div>
                            <div class="user-nrp">{{ Auth::user()->dosen->npk ?? '11XXXXXX' }}</div>
                        </div>
                        <div class="user-avatar"></div>
                    </button>

                    <div x-show="open" @click.outside="open = false" x-transition class="dropdown-menu">
                        <a href="{{ route('profile.edit') }}">
                            Profil Saya
                        </a>
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

</body>
</html>
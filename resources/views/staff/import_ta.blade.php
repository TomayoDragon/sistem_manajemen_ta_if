<x-staff-layout>
    <x-slot name="title">Import Tugas Akhir Baru</x-slot>

    {{-- CUSTOM CSS --}}
    <style>
        /* Container Utama: Card Putih dengan Shadow Halus */
        .import-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            padding: 35px;
            border: 1px solid #e2e8f0;
            max-width: 950px;
            margin: 0 auto;
        }

        .page-header {
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }

        .page-title {
            color: #0f172a; /* Navy Blue Konsisten */
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }

        .page-subtitle {
            color: #64748b;
            font-size: 0.9rem;
            margin-top: 5px;
        }

        /* Alert Info Styling */
        .info-box {
            background: #eff6ff; /* Biru Muda */
            border-left: 5px solid #2563eb;
            padding: 15px 20px;
            border-radius: 6px;
            color: #1e3a8a;
            font-size: 0.9rem;
            margin-bottom: 30px;
            display: flex;
            align-items: flex-start;
        }
        .info-box i { margin-top: 3px; margin-right: 12px; font-size: 1.1rem; }

        /* Grid Layout untuk 2 Langkah */
        .steps-grid {
            display: grid;
            grid-template-columns: 1fr 1fr; /* Bagi 2 kolom */
            gap: 25px;
        }

        @media (max-width: 768px) {
            .steps-grid { grid-template-columns: 1fr; } /* Mobile: 1 kolom */
        }

        /* Kotak Langkah (Step Box) */
        .step-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 25px;
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .step-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border-color: #cbd5e1;
        }

        .step-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            font-weight: 700;
            color: #334155;
            font-size: 1.1rem;
        }

        .step-badge {
            background: #0f172a;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            margin-right: 10px;
        }

        .step-desc {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 20px;
            line-height: 1.5;
            flex-grow: 1; /* Dorong tombol ke bawah */
        }

        /* Buttons */
        .btn-custom {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            width: 100%;
            font-size: 0.95rem;
        }

        .btn-green {
            background-color: #10b981; 
            color: white;
        }
        .btn-green:hover { background-color: #059669; color: white; }

        .btn-navy {
            background-color: #0f172a; 
            color: white;
        }
        .btn-navy:hover { background-color: #1e293b; color: white; }

        /* File Upload Area Kustom */
        .upload-area {
            position: relative;
            border: 2px dashed #94a3b8;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            background: #fff;
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 15px;
        }

        .upload-area:hover {
            border-color: #2563eb;
            background: #f0f9ff;
        }

        .upload-area input[type="file"] {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .upload-icon {
            font-size: 2rem;
            color: #cbd5e1;
            margin-bottom: 10px;
        }
        
        .upload-text {
            color: #475569;
            font-weight: 500;
            font-size: 0.9rem;
        }

        /* Flash Messages */
        .msg-box { padding: 12px 15px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem; }
        .msg-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .msg-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .msg-error ul { padding-left: 20px; margin: 5px 0 0 0; }
    </style>

    <div class="import-card">
        
        {{-- Header --}}
        <div class="page-header">
            <h1 class="page-title">Tambah Tugas Akhir</h1>
            <p class="page-subtitle">Import data mahasiswa dan tugas akhir melalui file Excel.</p>
        </div>

        {{-- Info Alert --}}
        <div class="info-box">
            <i class="fa-solid fa-circle-info"></i>
            <div>
                <strong>Informasi Sistem:</strong><br>
                Fitur ini akan mendaftarkan mahasiswa ke periode aktif. Jika akun mahasiswa belum ada, 
                sistem akan membuatnya otomatis dengan password default: <strong>password123</strong>.
            </div>
        </div>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="msg-box msg-success">
                <i class="fa-solid fa-check-circle mr-2"></i> {{ session('success') }}
            </div>
        @endif

        @if (session('import_errors'))
            <div class="msg-box msg-error">
                <strong><i class="fa-solid fa-triangle-exclamation mr-2"></i> Gagal Import Data:</strong>
                <ul>
                    @foreach(session('import_errors') as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Content Grid --}}
        <div class="steps-grid">
            
            {{-- LANGKAH 1: DOWNLOAD --}}
            <div class="step-box">
                <div class="step-header">
                    <span class="step-badge">1</span> Download Template
                </div>
                <p class="step-desc">
                    Unduh template Excel standar. Pastikan Anda mengisi kolom <strong>NRP, Nama, Judul, dan NPK Dosen</strong> dengan format yang benar agar sistem dapat memproses data.
                </p>
                <div style="margin-top: auto;">
                    <a href="{{ route('staff.ta.template') }}" class="btn-custom btn-green">
                        <i class="fa-solid fa-file-excel mr-2"></i> Download Template .xlsx
                    </a>
                </div>
            </div>

            {{-- LANGKAH 2: UPLOAD --}}
            <div class="step-box">
                <form action="{{ route('staff.ta.import.process') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="step-header">
                        <span class="step-badge">2</span> Upload Data
                    </div>
                    <p class="step-desc">
                        Pilih file Excel yang sudah Anda lengkapi datanya, kemudian klik tombol Import untuk memproses.
                    </p>
                    
                    {{-- Custom File Input --}}
                    <div class="upload-area">
                        <input type="file" name="file_excel" accept=".xlsx" required onchange="updateFileName(this)">
                        <i class="fa-solid fa-cloud-arrow-up upload-icon"></i>
                        <div class="upload-text" id="fileNameDisplay">Klik atau tarik file ke sini</div>
                    </div>

                    <button type="submit" class="btn-custom btn-navy">
                        <i class="fa-solid fa-upload mr-2"></i> Import Data Sekarang
                    </button>
                </form>
            </div>

        </div>
    </div>

    {{-- Script Kecil untuk Nama File --}}
    <script>
        function updateFileName(input) {
            const display = document.getElementById('fileNameDisplay');
            if (input.files && input.files[0]) {
                display.innerHTML = 'File Terpilih: <strong>' + input.files[0].name + '</strong>';
                display.style.color = '#0f172a';
            } else {
                display.innerHTML = 'Klik atau tarik file ke sini';
                display.style.color = '#475569';
            }
        }
    </script>
</x-staff-layout>
<x-mahasiswa-layout>
    <x-slot name="title">
        Dashboard
    </x-slot>

    <!-- 
    Kita bisa mengatur <title> di layout dengan cara ini 
    (Opsional, tapi rapi) 
    -->
    <x-slot name="title">
        Dashboard
    </x-slot>

    <style>
        .ta-info-box h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px; /* Kurangi margin bawah */
        }
        
        /* --- STYLE BARU UNTUK PERIODE (SESUAI PERMINTAAN ANDA) --- */
        .periode-info {
            font-size: 1rem;
            font-weight: 600;
            color: #555;
            margin-bottom: 20px;
            font-style: italic;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }
        /* --- AKHIR STYLE BARU --- */

        .ta-info-box .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr; /* 2 kolom */
            gap: 15px;
        }
        .ta-info-box .info-item {
            font-size: 1rem;
        }
        .ta-info-box .info-label {
            display: block;
            font-size: 0.9rem;
            color: #777;
            margin-bottom: 4px;
        }
        .ta-info-box .info-value {
            font-weight: 700;
            color: #333;
        }
        .no-ta-box {
            text-align: center;
            padding: 40px;
            color: #777;
            font-size: 1.1rem;
            font-style: italic;
        }
    </style>

    <!-- Judul Konten -->
    <h1 class="content-title">Dashboard</h1>
    
    <!-- Tampilkan notifikasi (jika ada) -->
    @if (session('error'))
        <div class="content-box" style="background-color: #ffebeB; color: #a00; margin-bottom: 20px;">
            {{ session('error') }}
        </div>
    @endif

    <!-- Kotak Konten -->
    <div class="content-box ta-info-box">

        <!-- CEK APAKAH MAHASISWA SUDAH PUNYA TA? -->
        @if ($tugasAkhir)
            
            <!-- Judul TA -->
            <h3>{{ $tugasAkhir->judul }}</h3>

            <!-- ===== PERUBAHAN DI SINI ===== -->
            <!-- Tampilkan Info Periode -->
            @if ($tugasAkhir->periode)
                <p class="periode-info">
                    {{ $tugasAkhir->periode->nama }}
                </p>
            @endif
            <!-- ===== AKHIR PERUBAHAN ===== -->

            <!-- Info Grid (Dosbing & Status) -->
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Dosen Pembimbing 1:</span>
                    <span class="info-value">{{ $tugasAkhir->dosenPembimbing1->nama_lengkap }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Dosen Pembimbing 2:</span>
                    <span class="info-value">{{ $tugasAkhir->dosenPembimbing2->nama_lengkap }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status TA:</span>
                    <span class="info-value" style="color: #0a2e6c; font-weight: 700;">
                        {{ $tugasAkhir->status }}
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Persetujuan Dosbing 2:</span>
                    <span class="info-value">
                        @if($tugasAkhir->dosbing_1_approved_at)
                            <span style="color: green;">✓ Disetujui</span>
                        @else
                            <span style="color: orange;">- Menunggu</span>
                        @endif
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Persetujuan Dosbing 1:</span>
                    <span class="info-value">
                        @if($tugasAkhir->dosbing_2_approved_at)
                            <span style="color: green;">✓ Disetujui</span>
                        @else
                            <span style="color: orange;">- Menunggu</span>
                        @endif
                    </span>
                </div>
            </div>

        @else

            <!-- Jika tidak punya TA, tampilkan pesan -->
            <div class="no-ta-box">
                <p>Anda belum memiliki data Tugas Akhir yang aktif.</p>
            </div>

        @endif
    </div>

</x-mahasiswa-layout>
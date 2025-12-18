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
            margin-bottom: 5px;
            /* Kurangi margin bawah */
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
            grid-template-columns: 1fr 1fr;
            /* 2 kolom */
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                    <span class="text-sm text-gray-500 block">Dosen Pembimbing 1:</span>
                    <div class="font-bold text-gray-800 mb-2">{{ $tugasAkhir->dosenPembimbing1->nama_lengkap }}</div>

                    <span class="text-sm text-gray-500 block mb-1">Status Persetujuan:</span>

                    @if ($tugasAkhir->dosbing_1_approved_at)
                        <span class="inline-flex items-center text-green-600 font-bold text-sm">
                            <i class="fa-solid fa-check-circle mr-1"></i> Disetujui
                            <small class="ml-1 text-gray-400 font-normal">
                                ({{ \Carbon\Carbon::parse($tugasAkhir->dosbing_1_approved_at)->format('d M') }})
                            </small>
                        </span>
                    @else
                        <span class="inline-flex items-center text-orange-500 font-bold text-sm">
                            <i class="fa-solid fa-clock mr-1"></i> Menunggu
                        </span>
                    @endif
                </div>

                <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                    <span class="text-sm text-gray-500 block">Dosen Pembimbing 2:</span>
                    <div class="font-bold text-gray-800 mb-2">{{ $tugasAkhir->dosenPembimbing2->nama_lengkap }}</div>

                    <span class="text-sm text-gray-500 block mb-1">Status Persetujuan:</span>

                    @if ($tugasAkhir->dosbing_2_approved_at)
                        <span class="inline-flex items-center text-green-600 font-bold text-sm">
                            <i class="fa-solid fa-check-circle mr-1"></i> Disetujui
                            <small class="ml-1 text-gray-400 font-normal">
                                ({{ \Carbon\Carbon::parse($tugasAkhir->dosbing_2_approved_at)->format('d M') }})
                            </small>
                        </span>
                    @else
                        <span class="inline-flex items-center text-orange-500 font-bold text-sm">
                            <i class="fa-solid fa-clock mr-1"></i> Menunggu
                        </span>
                    @endif
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
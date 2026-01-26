<x-mahasiswa-layout>
    <x-slot name="title">
        Dashboard
    </x-slot>

    <style>
        .ta-info-box h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }

        .periode-info {
            font-size: 1rem;
            font-weight: 600;
            color: #555;
            margin-bottom: 20px;
            font-style: italic;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }

        .ta-info-box .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .no-ta-box {
            text-align: center;
            padding: 40px;
            color: #777;
            font-size: 1.1rem;
            font-style: italic;
        }
    </style>

    <h1 class="content-title">Dashboard</h1>

    @if (session('error'))
        <div class="content-box" style="background-color: #ffebeB; color: #a00; margin-bottom: 20px;">
            {{ session('error') }}
        </div>
    @endif

    <div class="content-box ta-info-box">
        @if ($tugasAkhir)

            <h3>{{ $tugasAkhir->judul }}</h3>

            @if ($tugasAkhir->periode)
                <p class="periode-info">
                    {{ $tugasAkhir->periode->nama }}
                </p>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">

                <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                    <span class="text-sm text-gray-500 block">Dosen Pembimbing 1:</span>
                    <div class="font-bold text-gray-800 text-lg">
                        {{ $tugasAkhir->dosenPembimbing1->nama_lengkap }}
                    </div>
                </div>

                <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                    <span class="text-sm text-gray-500 block">Dosen Pembimbing 2:</span>
                    <div class="font-bold text-gray-800 text-lg">
                        {{ $tugasAkhir->dosenPembimbing2->nama_lengkap ?? '-' }}
                    </div>
                </div>
            </div>

            <div class="mt-6 p-3 bg-blue-50 text-blue-700 rounded text-sm">
                <i class="fa-solid fa-info-circle mr-1"></i>
                Silakan langsung menuju menu <strong>Upload Berkas TA</strong> untuk melengkapi persyaratan sidang.
            </div>

        @else
            <div class="no-ta-box">
                <p>Anda belum memiliki data Tugas Akhir yang aktif.</p>
            </div>
        @endif

      
</x-mahasiswa-layout>
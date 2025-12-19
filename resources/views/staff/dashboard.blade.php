<x-staff-layout>
    <x-slot name="title">
        Dashboard Staf
    </x-slot>

    <style>
        .table-wrapper { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table-wrapper th, .table-wrapper td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        .table-wrapper th { background-color: #f4f4f4; font-weight: 700; }
        
        .btn-review {
            padding: 5px 12px; font-size: 0.9rem; text-decoration: none;
            color: white; background-color: #0a2e6c; border: none; border-radius: 5px; cursor: pointer;
            transition: background-color 0.2s;
        }
        .btn-review:hover {
            background-color: #082456;
        }
    </style>

    {{-- Notifikasi Sukses/Error --}}
    @if (session('success'))
        <div class="content-box" style="background-color: #eBffeb; color: #0a0; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="content-box" style="background-color: #ffebeB; color: #a00; margin-bottom: 20px;">
            {{ session('error') }}
        </div>
    @endif

    {{-- HEADER & TABEL VALIDASI --}}
    <h1 class="content-title">Validasi Pengajuan Sidang Tertunda</h1>
    
    <div class="content-box">
        <p class="text-gray-600 mb-4">
            Daftar di bawah ini adalah mahasiswa yang baru mengajukan berkas dan menunggu pemeriksaan kelengkapan.
        </p>

        <table class="table-wrapper">
            <thead>
                <tr> 
                    <th>Mahasiswa</th> 
                    <th>NRP</th> 
                    <th>Tgl Pengajuan</th> 
                    <th width="15%">Aksi</th> 
                </tr>
            </thead>
            <tbody>
                @forelse ($pendingPengajuans as $pengajuan)
                    <tr>
                        <td>{{ $pengajuan->tugasAkhir->mahasiswa->nama_lengkap }}</td>
                        <td>{{ $pengajuan->tugasAkhir->mahasiswa->nrp }}</td>
                        <td>{{ $pengajuan->created_at->format('d M Y, H:i') }}</td>
                        <td>
                            <a href="{{ route('staff.validasi.review', $pengajuan->id) }}" class="btn-review">
                                <i class="fa-solid fa-search"></i> Review Paket
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr> 
                        <td colspan="4" style="text-align: center; color: #777; padding: 30px;">
                            <i class="fa-solid fa-check-circle" style="font-size: 2rem; color: #2ecc71; margin-bottom: 10px; display: block;"></i>
                            Tidak ada paket pengajuan yang menunggu validasi saat ini.
                        </td> 
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</x-staff-layout>
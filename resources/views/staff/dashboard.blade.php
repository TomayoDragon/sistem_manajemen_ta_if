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
        }
        .btn-excel {
            display: inline-block; padding: 12px 25px; font-size: 1rem; font-weight: 700;
            color: #fff; border: none; border-radius: 8px; cursor: pointer;
            text-decoration: none; margin-bottom: 20px; margin-right: 10px;
        }
        .btn-export { background-color: #16a085; } /* Hijau Excel */
        .btn-import { background-color: #0a2e6c; } /* Biru IF */
    </style>

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

    <h1 class="content-title">Validasi Pengajuan Sidang Tertunda</h1>
    <div class="content-box">
        <table class="table-wrapper">
            <thead>
                <tr> <th>Mahasiswa</th> <th>NRP</th> <th>Tgl Pengajuan</th> <th>Aksi</th> </tr>
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
                    <tr> <td colspan="4" style="text-align: center; color: #777;">Tidak ada paket pengajuan yang menunggu validasi.</td> </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <h1 class="content-title" style="margin-top: 30px;">Pengajuan Disetujui (Siap Dijadwalkan)</h1>
    <div class="content-box">
        
        <p style="margin-bottom: 15px;">
            Total mahasiswa yang siap dijadwalkan (belum masuk gelombang): 
            <strong>{{ $acceptedPengajuans->count() }} mahasiswa</strong>
        </p>

        <a href="{{ route('staff.jadwal.export') }}" class="btn-excel btn-export">
            <i class="fa-solid fa-file-export"></i> 
            Langkah 1: Generate & Download Draf Jadwal
        </a>
        
        <a href="{{ route('staff.jadwal.import.form') }}" class="btn-excel btn-import">
            <i class="fa-solid fa-file-import"></i> 
            Langkah 2: Import Draf Final
        </a>

        <table class="table-wrapper" style="margin-top: 20px;">
            <thead>
                <tr> <th>Mahasiswa</th> <th>NRP</th> <th>Tgl Disetujui</th> </tr>
            </thead>
            <tbody>
                @forelse ($acceptedPengajuans as $pengajuan)
                    <tr>
                        <td>{{ $pengajuan->tugasAkhir->mahasiswa->nama_lengkap }}</td>
                        <td>{{ $pengajuan->tugasAkhir->mahasiswa->nrp }}</td>
                        <td>{{ $pengajuan->validated_at ? \Carbon\Carbon::parse($pengajuan->validated_at)->format('d M Y') : '-' }}</td>
                    </tr>
                @empty
                    <tr> <td colspan="3" style="text-align: center; color: #777;">Tidak ada pengajuan yang siap dijadwalkan.</td> </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
</x-staff-layout>
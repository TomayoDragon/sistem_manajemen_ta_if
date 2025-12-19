<x-staff-layout>
    <x-slot name="title">
        Atur Jadwal Sidang
    </x-slot>

    {{-- KITA PAKAI STYLE YANG SAMA DENGAN DASHBOARD LAMA BIAR KONSISTEN --}}
    <style>
        .content-box { 
            background: #fff; 
            padding: 25px; 
            border-radius: 10px; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.05); 
            border: 1px solid #eef2f7; 
        }
        
        /* Style Judul Halaman */
        .content-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        /* Style Tabel */
        .table-wrapper { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
            font-size: 0.95rem;
        }
        .table-wrapper th { 
            background-color: #f4f4f4; 
            font-weight: 700; 
            color: #333;
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .table-wrapper td { 
            padding: 12px; 
            border: 1px solid #ddd; 
            color: #555;
        }
        .table-wrapper tr:hover {
            background-color: #f9f9f9;
        }

        /* Style Tombol Aksi (Export/Import) */
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            margin-top: 20px;
        }
        .btn-excel {
            display: inline-flex;
            align-items: center;
            padding: 12px 20px;
            font-size: 0.95rem;
            font-weight: 600;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            transition: opacity 0.2s;
        }
        .btn-excel:hover { opacity: 0.9; color: white; }
        .btn-excel i { margin-right: 8px; font-size: 1.1rem; }
        
        .btn-export { background-color: #16a085; } /* Hijau Teal */
        .btn-import { background-color: #0a2e6c; } /* Biru Tua IF */

        /* Badge Count */
        .badge-count {
            background-color: #e3f2fd;
            color: #0d47a1;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
            border: 1px solid #bbdefb;
        }
    </style>

    {{-- JUDUL HALAMAN --}}
    <h1 class="content-title">Atur Jadwal Sidang</h1>

    {{-- KOTAK KONTEN UTAMA --}}
    <div class="content-box">
        
        {{-- HEADER SECTION: JUDUL & TOTAL MAHASISWA --}}
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 15px;">
            <div>
                <h3 style="margin: 0; font-size: 1.2rem; color: #333;">Pengajuan Disetujui (Siap Dijadwalkan)</h3>
                <p style="margin: 5px 0 0; color: #777; font-size: 0.9rem;">
                    Mahasiswa di bawah ini telah lolos validasi berkas dan menunggu plotting jadwal.
                </p>
            </div>
            <span class="badge-count">
                Total: {{ $siapDijadwalkan->count() }} Mahasiswa
            </span>
        </div>

        {{-- TOMBOL AKSI (LANGKAH 1 & 2) --}}
        @if($siapDijadwalkan->count() > 0)
            <div class="action-buttons">
                {{-- Tombol 1: Download --}}
                <a href="{{ route('staff.jadwal.export') }}" class="btn-excel btn-export">
                    <i class="fa-solid fa-file-excel"></i> 
                    Langkah 1: Generate & Download Draf Jadwal
                </a>
                
                {{-- Tombol 2: Import --}}
                <a href="{{ route('staff.jadwal.import.form') }}" class="btn-excel btn-import">
                    <i class="fa-solid fa-file-import"></i> 
                    Langkah 2: Import Draf Final
                </a>
            </div>
        @else
            {{-- Jika data kosong, tampilkan tombol Import saja (siapa tau mau re-upload) --}}
            <div class="action-buttons">
                <a href="{{ route('staff.jadwal.import.form') }}" class="btn-excel btn-import">
                    <i class="fa-solid fa-file-import"></i> 
                    Import File Excel Jadwal
                </a>
            </div>
        @endif

        {{-- TABEL DATA MAHASISWA --}}
        <table class="table-wrapper">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="25%">Mahasiswa</th>
                    <th width="15%">NRP</th>
                    <th>Judul Tugas Akhir</th>
                    <th width="15%">Tgl Disetujui</th>
                </tr>
            </thead>
            <tbody>
                @forelse($siapDijadwalkan as $pengajuan)
                    <tr>
                        <td style="text-align: center;">{{ $loop->iteration }}</td>
                        <td style="font-weight: 600;">{{ $pengajuan->tugasAkhir->mahasiswa->nama_lengkap }}</td>
                        <td>{{ $pengajuan->tugasAkhir->mahasiswa->nrp }}</td>
                        <td>
                            <span style="font-style: italic; color: #555;">
                                "{{ Str::limit($pengajuan->tugasAkhir->judul, 60) }}"
                            </span>
                        </td>
                        <td>
                            <i class="fa-regular fa-calendar" style="margin-right:5px; color:#888;"></i>
                            {{ \Carbon\Carbon::parse($pengajuan->validated_at)->format('d M Y') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px; color: #888;">
                            <i class="fa-solid fa-clipboard-check" style="font-size: 3rem; color: #ddd; margin-bottom: 15px; display: block;"></i>
                            Tidak ada pengajuan yang siap dijadwalkan saat ini.
                            <br>
                            <small>Semua pengajuan yang masuk sudah memiliki jadwal atau belum divalidasi.</small>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PESAN SUKSES (MISAL HABIS IMPORT) --}}
    @if(session('success'))
        <div style="margin-top: 20px; padding: 15px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px;">
            <i class="fa-solid fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

</x-staff-layout>
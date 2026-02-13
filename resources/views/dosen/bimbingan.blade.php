<x-dosen-layout>
    <x-slot name="title">
        Mahasiswa Bimbingan
    </x-slot>

    {{-- 1. LOAD CSS DATATABLES --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .content-box {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid #f3f4f6;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-yellow { background-color: #fef9c3; color: #854d0e; border: 1px solid #fde047; }
        .badge-red { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .badge-gray { color: #9ca3af; font-style: italic; }

        .btn-detail {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            font-size: 0.8rem;
            font-weight: 600;
            color: white !important;
            background-color: #2563eb; /* Blue */
            border-radius: 6px;
            text-decoration: none;
            transition: background-color 0.2s;
            border: none;
            cursor: pointer;
        }
        .btn-detail:hover { background-color: #1d4ed8; }

        table.dataTable thead th {
            background-color: #f9fafb;
            color: #6b7280;
            font-size: 0.75rem;
            text-transform: uppercase;
            border-bottom: 1px solid #e5e7eb !important;
            padding: 12px 16px !important;
        }
    </style>

    <div class="content-box">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Daftar Mahasiswa</h2>
        
        <table id="myTable" class="display responsive nowrap w-full text-left">
            <thead>
                <tr>
                    <th style="width: 35%;">Mahasiswa</th>
                    <th style="width: 45%;">Judul TA</th>
                    <th style="width: 20%; text-align: right;">Akses Berkas</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($mahasiswaBimbingan as $ta)
                    <tr>
                        <td>
                            <div class="font-bold text-gray-900">{{ $ta->mahasiswa->nama_lengkap ?? $ta->mahasiswa->nama }}</div>
                            <div class="text-xs text-gray-500 mt-1">{{ $ta->mahasiswa->nrp }}</div>
                        </td>
                        
                        <td>
                            <div class="text-sm text-gray-700 line-clamp-2">
                                {{ \Illuminate\Support\Str::limit($ta->judul_ta ?? $ta->judul, 80) }}
                            </div>
                        </td>

                        <td style="text-align: right;">
                            @php
                                // Ambil status pengajuan berkas terakhir
                                $pengajuan = $ta->pengajuanSidangs->sortByDesc('created_at')->first();
                            @endphp

                            @if ($pengajuan && $pengajuan->status_validasi == 'TERIMA')
                                {{-- HANYA TAMPILKAN TOMBOL DETAIL --}}
                                <a href="{{ route('dosen.bimbingan.show', $ta->id) }}" class="btn-detail">
                                    <i class="fa-solid fa-folder-open mr-1.5"></i> Detail & Berkas
                                </a>

                            @elseif ($pengajuan && $pengajuan->status_validasi == 'PENDING')
                                <span class="badge badge-yellow">
                                    <i class="fa-solid fa-hourglass-half mr-1.5"></i> Verifikasi Staff
                                </span>

                            @elseif ($pengajuan && $pengajuan->status_validasi == 'TOLAK')
                                <span class="badge badge-red">
                                    <i class="fa-solid fa-circle-xmark mr-1.5"></i> Ditolak Staff
                                </span>

                            @else
                                <span class="badge badge-gray">Belum tersedia</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    {{-- Kosong --}}
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- SCRIPT --}}
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#myTable').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json',
                    emptyTable: "Belum ada mahasiswa bimbingan."
                },
                columnDefs: [
                    { orderable: false, targets: 2 } // Matikan sort di kolom Aksi
                ]
            });
        });
    </script>
</x-dosen-layout>
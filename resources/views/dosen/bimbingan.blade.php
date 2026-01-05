<x-dosen-layout>
    <x-slot name="title">
        Mahasiswa Bimbingan
    </x-slot>

    {{-- 1. LOAD CSS DATATABLES --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">

    <style>
        /* Custom Styles untuk Tombol & Layout */
        .content-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Style Tombol Dokumen */
        .btn-download {
            display: inline-block;
            padding: 6px 12px;
            font-size: 0.85rem;
            text-decoration: none;
            color: white !important;
            border-radius: 4px;
            margin-right: 5px;
            margin-bottom: 5px;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .btn-download:hover { opacity: 0.9; }
        
        .btn-revisi { background-color: #e67e22; } /* Oranye */
        .btn-ba { background-color: #3498db; } /* Biru */

        /* Override Style DataTables agar sesuai tema */
        .dataTables_wrapper .dataTables_length select {
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .dataTables_wrapper .dataTables_filter input {
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ddd;
            margin-left: 5px;
        }
        table.dataTable thead th {
            background-color: #f4f4f4;
            color: #333;
            border-bottom: 2px solid #ddd;
        }
        table.dataTable.no-footer {
            border-bottom: 1px solid #ddd;
        }
    </style>

    @if (session('success'))
        <div class="content-box" style="background-color: #eBffeb; color: #0a0; margin-bottom: 20px; border: 1px solid #0a0;">
            {{ session('success') }}
        </div>
    @endif

    <h1 class="content-title" style="margin-bottom: 20px; font-size: 1.5rem; font-weight: bold;">Daftar Mahasiswa Bimbingan</h1>

    <div class="content-box">
        <p style="margin-bottom: 20px; color: #555;">
            Halaman ini digunakan untuk memantau progres mahasiswa bimbingan Anda. Gunakan kolom <strong>Cari</strong> di sebelah kanan untuk memfilter mahasiswa.
        </p>

        {{-- TABEL DENGAN ID UNTUK DATATABLES --}}
        <table id="myTable" class="display responsive nowrap" style="width:100%">
            <thead>
                <tr>
                    <th style="width: 30%;">Mahasiswa</th>
                    <th style="width: 50%;">Judul TA</th>
                    <th style="width: 20%; text-align: center;">Dokumen Sidang</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($mahasiswaBimbingan as $ta)
                    <tr>
                        <td>
                            <strong>{{ $ta->mahasiswa->nama_lengkap }}</strong>
                            <br><small style="color: #777;">{{ $ta->mahasiswa->nrp }}</small>
                        </td>
                        <td>{{ \Illuminate\Support\Str::limit($ta->judul, 100) }}</td>
                        
                        {{-- KOLOM DOKUMEN SIDANG --}}
                        <td style="text-align: center;">
                            @php
                                // Ambil sidang TERAKHIR mahasiswa ini
                                $sidangTerakhir = $ta->sidangs->sortByDesc('created_at')->first();
                            @endphp

                            @if ($sidangTerakhir && in_array($sidangTerakhir->status, ['LULUS', 'LULUS_REVISI', 'TIDAK_LULUS']))
                                
                                {{-- Tombol Lihat Revisi --}}
                                <a href="{{ route('dokumen.hasil-sidang', ['sidang' => $sidangTerakhir->id, 'jenis' => 'revisi', 'mode' => 'view']) }}" 
                                   target="_blank" class="btn-download btn-revisi" title="Lihat Lembar Revisi">
                                    <i class="fa-solid fa-eye"></i> Revisi
                                </a>

                                {{-- Tombol Lihat BA --}}
                                <a href="{{ route('dokumen.hasil-sidang', ['sidang' => $sidangTerakhir->id, 'jenis' => 'berita-acara', 'mode' => 'view']) }}" 
                                   target="_blank" class="btn-download btn-ba" title="Lihat Berita Acara">
                                    <i class="fa-solid fa-file-pdf"></i> BA
                                </a>

                            @elseif($sidangTerakhir && $sidangTerakhir->status == 'TERJADWAL')
                                <span style="font-size: 0.85rem; color: #3498db; font-style: italic; font-weight: bold;">
                                    Sidang Terjadwal
                                </span>
                            @else
                                <span style="font-size: 0.85rem; color: #999; font-style: italic;">
                                    Belum tersedia
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    {{-- Data kosong ditangani DataTables, tapi jika collection kosong dari controller --}}
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- 2. LOAD SCRIPT DATATABLES (JQUERY + DT JS) --}}
    {{-- Letakkan di sini agar langsung dieksekusi setelah tabel dirender --}}
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#myTable').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' // Bahasa Indonesia
                },
                columnDefs: [
                    { orderable: false, targets: 2 } // Matikan sorting di kolom Dokumen (index 2)
                ]
            });
        });
    </script>
</x-dosen-layout>
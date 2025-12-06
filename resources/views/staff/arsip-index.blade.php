<x-staff-layout>
    <x-slot name="title">
        Arsip Tugas Akhir
    </x-slot>

    <style>
        /* Kita sederhanakan style karena DataTables akan menangani layout tabel */
        .btn-detail {
            padding: 5px 12px; font-size: 0.85rem; text-decoration: none;
            color: white; background-color: #3498db; border: none;
            border-radius: 5px; cursor: pointer; display: inline-block;
        }
        .btn-detail:hover { background-color: #2980b9; color: white; }

        /* Custom styling untuk DataTables wrapper agar sesuai desain */
        #tableArsip_wrapper {
            padding: 0;
        }
        table.dataTable thead th {
            background-color: #f4f4f4;
            color: #333;
            font-weight: 700;
            border-bottom: 2px solid #ddd !important;
        }
        table.dataTable tbody td {
            vertical-align: top;
            padding: 12px 10px;
        }
        
        /* Filter Periode Manual (Opsional: Jika ingin tetap filter dari server) */
        .periode-filter {
            margin-bottom: 20px;
            display: flex;
            justify-content: flex-end;
        }
        .periode-filter select {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
    </style>

    <h1 class="content-title">Arsip Tugas Akhir</h1>

    <div class="content-box">
        
        {{-- 
            FILTER PERIODE (SERVER SIDE)
            Kita tetap simpan ini jika Anda ingin membatasi data yang dimuat dari database
            agar tidak terlalu berat (misal: hanya load tahun 2025).
        --}}
        <form method="GET" action="{{ route('staff.arsip.index') }}" class="periode-filter">
            <select name="periode_id" onchange="this.form.submit()">
                <option value="">-- Semua Periode --</option>
                @foreach ($periodes as $periode)
                    <option value="{{ $periode->id }}" 
                        {{ (string)$selectedPeriodeId == (string)$periode->id ? 'selected' : '' }}>
                        {{ $periode->nama }}
                    </option>
                @endforeach
            </select>
        </form>

        {{-- TABEL DATATABLES --}}
        <table id="tableArsip" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>NRP/Nama</th>
                    <th width="30%">Judul Tugas Akhir</th>
                    <th>Pembimbing 1 & 2</th>
                    <th>Status TA</th>
                    <th>Periode</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($arsipTugasAkhir as $ta)
                    <tr>
                        <td>
                            <strong>{{ $ta->mahasiswa->nama_lengkap }}</strong>
                            <br><small style="color: #777;">{{ $ta->mahasiswa->nrp }}</small>
                        </td>
                        <td>{{ \Illuminate\Support\Str::limit($ta->judul, 80) }}</td>
                        <td>
                            <div style="font-size: 0.9em;">
                                1. {{ $ta->dosenPembimbing1->nama_lengkap }}<br>
                                2. {{ $ta->dosenPembimbing2->nama_lengkap }}
                            </div>
                        </td>
                        <td>
                            <span style="font-weight: 700; color: #0a2e6c;">
                                {{ $ta->status }}
                            </span>
                        </td>
                        <td>
                            {{ $ta->periode->nama ?? '-' }}
                        </td>
                        <td class="text-center">
                            <a href="{{ route('staff.arsip.show', $ta->id) }}" class="btn-detail">
                                <i class="fa-solid fa-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>

    {{-- SCRIPT DATATABLES --}}
    @push('scripts')
    <script>
        $(document).ready(function() {
            $('#tableArsip').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
                },
                "columnDefs": [
                    { "orderable": false, "targets": [5] } // Matikan sorting di kolom Aksi
                ],
                "pageLength": 10
            });
        });
    </script>
    @endpush

</x-staff-layout>
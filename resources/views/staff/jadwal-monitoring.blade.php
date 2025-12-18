<x-staff-layout>
    <x-slot name="title">
        Monitoring Jadwal Sidang
    </x-slot>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    
    <style>
        .content-box { background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #eef2f7; }
        table.dataTable thead th { background-color: #0a2e6c !important; color: #ffffff !important; font-weight: 600; padding: 12px; border-bottom: none; }
        table.dataTable tbody td { padding: 12px; vertical-align: middle; font-size: 0.95rem; color: #333; border-bottom: 1px solid #f0f0f0; }
        table.dataTable tbody tr:hover { background-color: #f8f9fa !important; }

        .badge { display: inline-block; padding: 5px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-lsta { background-color: #e3f2fd; color: #1565c0; border: 1px solid #bbdefb; }
        .badge-sidang { background-color: #fff3e0; color: #e65100; border: 1px solid #ffe0b2; }
        .badge-terjadwal { background-color: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
        .badge-selesai { background-color: #eceff1; color: #546e7a; border: 1px solid #cfd8dc; }

        .dataTables_filter input { border: 1px solid #ddd; padding: 8px 12px; border-radius: 6px; outline: none; transition: border-color 0.3s; }
        .dataTables_filter input:focus { border-color: #0a2e6c; }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current { background: #0a2e6c !important; color: white !important; border: 1px solid #0a2e6c !important; border-radius: 4px; }
    </style>

    <div class="flex justify-between items-center mb-6">
        <h1 class="content-title" style="margin-bottom: 0;">Monitoring Seluruh Jadwal</h1>
        <a href="{{ request()->url() }}" class="btn-refresh" style="color: #0a2e6c; text-decoration: none; font-weight: 600;">
            <i class="fa-solid fa-sync"></i> Refresh Data
        </a>
    </div>

    <div class="content-box">
        <p class="text-gray-600 mb-4">
            Data mencakup seluruh jadwal <strong>LSTA</strong> dan <strong>Sidang TA</strong> pada periode aktif.
        </p>

        <table id="monitoringTable" class="display responsive nowrap" style="width:100%">
            <thead>
                <tr>
                    <th width="5%">Tipe</th>
                    <th width="12%">Waktu & Ruang</th>
                    <th width="20%">Mahasiswa</th>
                    <th width="20%">Dosen Pembimbing</th> <th width="15%">Dosen Penguji</th>
                    <th width="8%">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($jadwals as $jadwal)
                    <tr>
                        <td class="text-center">
                            @if($jadwal['tipe'] == 'LSTA')
                                <span class="badge badge-lsta">LSTA</span>
                            @else
                                <span class="badge badge-sidang">SIDANG TA</span>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight: 700; color: #2c3e50;">{{ $jadwal['tanggal'] }}</div>
                            <div style="font-size: 0.9rem; margin-top: 4px;"><i class="fa-regular fa-clock"></i> {{ $jadwal['jam'] }} WIB</div>
                            <div style="font-size: 0.85rem; color: #555; margin-top: 4px;"><i class="fa-solid fa-location-dot"></i> {{ $jadwal['ruangan'] }}</div>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: #000;">{{ explode(' (', $jadwal['mahasiswa'])[0] }}</div>
                            <div style="font-size: 0.85rem; color: #666;">{{ Str::between($jadwal['mahasiswa'], '(', ')') }}</div>
                            <div style="font-size: 0.8rem; color: #888; margin-top: 5px; font-style: italic; white-space: normal;">
                                "{{ Str::limit($jadwal['judul'], 60) }}"
                            </div>
                        </td>
                        
                        <td style="font-size: 0.9rem; line-height: 1.5;">
                            {!! $jadwal['pembimbing'] !!}
                        </td>

                        <td style="font-size: 0.9rem; line-height: 1.5;">
                            {!! $jadwal['penguji'] !!}
                        </td>
                        
                        <td class="text-center">
                            <span class="badge {{ $jadwal['status'] == 'TERJADWAL' ? 'badge-terjadwal' : 'badge-selesai' }}">
                                {{ $jadwal['status'] }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#monitoringTable').DataTable({
                responsive: true,
                order: [[ 1, "asc" ]],
                pageLength: 10,
                language: {
                    search: "Cari Data:",
                    searchPlaceholder: "Nama / Dosen / Ruang...",
                    lengthMenu: "Tampilkan _MENU_ baris",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                    infoEmpty: "Data kosong",
                    infoFiltered: "(filter dari _MAX_ total)",
                    zeroRecords: "Tidak ditemukan",
                    paginate: { first: "<<", last: ">>", next: ">", previous: "<" }
                },
                columnDefs: [
                    { responsivePriority: 1, targets: 0 },
                    { responsivePriority: 2, targets: 2 },
                    { responsivePriority: 3, targets: 5 }
                ]
            });
        });
    </script>
</x-staff-layout>
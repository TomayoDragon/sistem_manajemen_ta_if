<x-dosen-layout>
    <x-slot name="title">
        Dashboard Dosen
    </x-slot>

    <style>
        .table-wrapper {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table-wrapper th,
        .table-wrapper td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        .table-wrapper th {
            background-color: #f4f4f4;
            font-weight: 700;
        }

        .btn-penilaian {
            padding: 5px 12px;
            font-size: 0.9rem;
            text-decoration: none;
            color: white;
            background-color: #0a2e6c;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            display: block;
            text-align: center;
        }

        /* Style btn-integritas dihapus atau dibiarkan tidak masalah karena tidak dipakai lagi */
    </style>

    @if (session('success'))
        <div class="content-box" style="background-color: #eBffeb; color: #0a0; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    <h1 class="content-title">Jadwal Menguji LSTA</h1>
    <div class="content-box">
        <table class="table-wrapper">
            <thead>
                <tr>
                    <th>Mahasiswa</th>
                    <th>Judul TA</th>
                    <th>Jadwal</th>
                    <th>Ruangan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($jadwalLsta as $lsta)
                    <tr>
                        <td>{{ $lsta->tugasAkhir->mahasiswa->nama_lengkap }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($lsta->tugasAkhir->judul, 40) }}</td>
                        <td>{{ \Carbon\Carbon::parse($lsta->jadwal)->format('d M Y, H:i') }}</td>
                        <td>{{ $lsta->ruangan }}</td>
                        <td style="width: 25%;">
                            @if($lsta->dosen_penguji_id == Auth::user()->dosen_id)
                                <a href="{{ route('dosen.penilaian.show', ['type' => 'lsta', 'id' => $lsta->id]) }}"
                                    class="btn-penilaian">
                                    <i class="fa-solid fa-file-pen"></i> Beri Nilai
                                </a>
                            @else
                                <span style="color: #777; font-size: 0.9rem; display:block; text-align:center;">
                                    (Pembimbing)
                                </span>
                            @endif
                            {{-- Dokumen link dihapus --}}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #777;">Anda tidak memiliki jadwal menguji LSTA.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <h1 class="content-title" style="margin-top: 30px;">Jadwal Menguji Sidang TA</h1>
    <div class="content-box">
        <table class="table-wrapper">
            <thead>
                <tr>
                    <th>Mahasiswa</th>
                    <th>Judul TA</th>
                    <th>Jadwal</th>
                    <th>Ruangan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($jadwalSidang as $sidang)
                    <tr>
                        <td>{{ $sidang->tugasAkhir->mahasiswa->nama_lengkap }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($sidang->tugasAkhir->judul, 40) }}</td>
                        <td>{{ \Carbon\Carbon::parse($sidang->jadwal)->format('d M Y, H:i') }}</td>
                        <td>{{ $sidang->ruangan }}</td>
                        <td style="width: 25%;"> 
                            <a href="{{ route('dosen.penilaian.show', ['type' => 'sidang', 'id' => $sidang->id]) }}"
                                class="btn-penilaian">
                                <i class="fa-solid fa-file-pen"></i> Beri Nilai
                            </a>
                            {{-- Dokumen link dihapus --}}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #777;">Anda tidak memiliki jadwal menguji Sidang TA.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</x-dosen-layout>
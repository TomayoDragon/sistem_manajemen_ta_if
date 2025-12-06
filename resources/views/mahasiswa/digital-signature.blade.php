<x-mahasiswa-layout>
    <x-slot name="title">
        Digital Signature
    </x-slot>

    <style>
        .table-wrapper {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table-wrapper th,
        .table-wrapper td {
            border: 1px solid #ddd;
            padding: 12px 15px;
            text-align: left;
        }

        .table-wrapper th {
            background-color: #0a2e6c;
            color: white;
            font-weight: 700;
        }

        /* Style ini akan kita gunakan untuk Hash DAN Signature */
        .crypto-cell {
            font-family: monospace;
            font-size: 0.9rem;
            max-width: 200px;
            /* Beri ruang lebih sedikit */
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .btn-check {
            padding: 5px 12px;
            font-size: 0.9rem;
            text-decoration: none;
            color: white;
            background-color: #0a2e6c;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

      

        .search-bar {
            display: flex;
            margin-bottom: 20px;
        }

        .search-bar input[type="text"] {
            flex-grow: 1;
            padding: 10px 15px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 5px 0 0 5px;
        }

        .search-bar button {
            padding: 10px 15px;
            background-color: #0a2e6c;
            color: white;
            border: none;
            border-radius: 0 5px 5px 0;
            cursor: pointer;
        }

        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .pagination-links {
            margin-top: 20px;
        }
    </style>

    <div class="header-flex">
        <h1 class="content-title">Berkas Ditandatangani</h1>
      
    </div>

    <div class="content-box">
        <table class="table-wrapper">
            <thead>
                <tr>
                    <th>Nama File (Download)</th>
                    <th>Tipe Dokumen</th>
                    <th>Hash (Tersimpan)</th>
                    <th>Digital Signature (Base64)</th>
                    <th>Verifikasi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dokumenTertanda as $dokumen)
                    <tr>
                        <td>
                            <a href="{{ route('dokumen.download', $dokumen->id) }}" target="_blank"
                                style="color: #0a2e6c; text-decoration: none; font-weight: 600;">
                                {{ $dokumen->nama_file_asli }}
                            </a>
                        </td>
                        <td>{{ $dokumen->tipe_dokumen }}</td>

                        <td>
                            <div class="crypto-cell" title="{{ $dokumen->hash_combined }}">
                                {{ $dokumen->hash_combined }}
                            </div>
                        </td>

                        <td>
                            <div class="crypto-cell" title="{{ $dokumen->signature_base64 }}">
                                {{ $dokumen->signature_base64 }}
                            </div>
                        </td>

                        <td>
                            <a href="{{ route('integritas.show', $dokumen->id) }}" class="btn-check" target="_blank">
                                <i class="fa-solid fa-shield-halved"></i> Cek
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #777;"> Tidak ada berkas yang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>

</x-mahasiswa-layout>
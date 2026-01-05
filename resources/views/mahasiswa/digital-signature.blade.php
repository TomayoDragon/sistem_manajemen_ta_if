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

        .crypto-cell {
            font-family: monospace;
            font-size: 0.9rem;
            max-width: 200px;
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

        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>

    <div class="header-flex">
        <h1 class="content-title" style="font-size: 1.5rem; font-weight: bold;">Berkas Ditandatangani</h1>
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
                            {{-- Gunakan download_url yang disiapkan controller --}}
                            <a href="{{ $dokumen->download_url }}" target="_blank"
                               style="color: #0a2e6c; text-decoration: none; font-weight: 600;">
                                {{ $dokumen->nama_file_asli }}
                            </a>
                        </td>
                        
                        <td>
                            @if(isset($dokumen->is_system) && $dokumen->is_system)
                                <span style="color: #059669; font-weight: bold;">{{ $dokumen->tipe_dokumen }}</span>
                            @else
                                {{ $dokumen->tipe_dokumen }}
                            @endif
                        </td>

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
                            {{-- Gunakan verify_url yang disiapkan controller --}}
                            <a href="{{ $dokumen->verify_url }}" class="btn-check" target="_blank">
                                <i class="fa-solid fa-shield-halved"></i> Cek
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #777; padding: 20px;"> 
                            Tidak ada berkas yang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</x-mahasiswa-layout>
<x-staff-layout>
    <x-slot name="title">
        Detail Arsip {{ $ta->mahasiswa->nrp }}
    </x-slot>

    <style>
        .detail-card { 
            margin-bottom: 25px; 
            background: #fff; 
            border: 1px solid #e0e0e0; 
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .detail-card-header {
            padding: 20px 25px;
            border-bottom: 1px solid #eee;
        }
        .detail-card-header h3 { font-size: 1.5rem; color: #0a2e6c; margin: 0; }
        .detail-card-body {
            padding: 25px;
        }

        .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .detail-item { margin-bottom: 15px; }
        .detail-label { display: block; font-size: 0.9rem; color: #777; }
        .detail-value { font-weight: 700; font-size: 1.1rem; }

        .history-table-small { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .history-table-small th, .history-table-small td { border: 1px solid #ddd; padding: 8px 10px; font-size: 0.9rem; }
        .history-table-small th { background-color: #f9f9f9; text-align: center; } /* Header rata tengah */
        
        .status-terima { color: green; font-weight: 700; }
        .status-tolak { color: red; font-weight: 700; }
        .status-pending { color: orange; font-weight: 700; }
        
        .file-sub-list { margin-top: 10px; padding-left: 20px; }
        .file-sub-list li { margin-bottom: 5px; font-size: 0.9rem; }
        .link-verifikasi { color: #0a2e6c; text-decoration: none; font-weight: 600; }

        /* --- STYLE TAMBAHAN UNTUK TOMBOL DOWNLOAD --- */
        .btn-download {
            display: inline-block;
            padding: 5px 10px;
            margin: 0 2px;
            border-radius: 4px;
            text-decoration: none;
            color: #fff;
            font-size: 0.8rem;
            font-weight: bold;
            transition: background-color 0.2s;
        }
        .btn-download:hover { opacity: 0.9; color: white; }
        .btn-revisi { background-color: #e67e22; } /* Oranye */
        .btn-ba { background-color: #2980b9; } /* Biru */
        .text-muted { color: #aaa; font-style: italic; font-size: 0.8rem; }
    </style>

    <h1 class="content-title">Detail Arsip Tugas Akhir</h1>
    
    <div class="detail-card">
        <div class="detail-card-header">
            <h3>Informasi Tugas Akhir</h3>
        </div>
        <div class="detail-card-body">
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">NRP / Nama:</span>
                    <span class="detail-value">{{ $ta->mahasiswa->nrp }} / {{ $ta->mahasiswa->nama_lengkap }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Status Akhir:</span>
                    <span class="detail-value">{{ $ta->status }}</span>
                </div>
            </div>
            <div class="detail-item">
                <span class="detail-label">Judul Tugas Akhir:</span>
                <span class="detail-value">{{ $ta->judul }}</span>
            </div>
            <div class="detail-grid" style="margin-top: 20px;">
                <div class="detail-item">
                    <span class="detail-label">Pembimbing 1:</span>
                    <span class="detail-value">{{ $ta->dosenPembimbing1->nama_lengkap }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Pembimbing 2:</span>
                    <span class="detail-value">{{ $ta->dosenPembimbing2->nama_lengkap }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="detail-card">
        <div class="detail-card-header">
            <h3>Riwayat Pengajuan Berkas</h3>
        </div>
        <div class="detail-card-body" style="padding: 10px 0 0 0;">
            @forelse ($ta->pengajuanSidangs as $pengajuan)
                <div style="padding: 15px 25px; border-bottom: 1px solid #eee;">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Tgl Pengajuan:</span>
                            <span class="detail-value">{{ $pengajuan->created_at->format('d M Y, H:i') }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Status Validasi:</span>
                            <span class="detail-value status-{{ strtolower($pengajuan->status_validasi) }}">
                                {{ $pengajuan->status_validasi }}
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Divalidasi Oleh:</span>
                            <span class="detail-value">{{ $pengajuan->validator->nama_lengkap ?? '-' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Catatan:</span>
                            <span class="detail-value" style="font-size: 1rem; font-style: italic;">
                                {{ $pengajuan->catatan_validasi ?? '-' }}
                            </span>
                        </div>
                    </div>
                    
                    <h4 style="margin-top: 15px; margin-bottom: 5px; font-size: 1rem;">Dokumen Terlampir:</h4>
                    <ul class="file-sub-list">
                        @forelse ($pengajuan->dokumen as $dokumen)
                            <li>
                                <strong>{{ $dokumen->tipe_dokumen }}</strong> ({{ $dokumen->nama_file_asli }}) - 
                                <a href="{{ route('dokumen.download', $dokumen->id) }}" target="_blank" class="link-verifikasi">Download</a> |
                                <a href="{{ route('integritas.show', $dokumen->id) }}" target="_blank" class="link-verifikasi">Cek Integritas</a>
                            </li>
                        @empty
                            <li style="color: red;">Tidak ada dokumen terlampir.</li>
                        @endforelse
                    </ul>
                </div>
            @empty
                <div style="padding: 25px; text-align: center; color: #777;">
                    Tidak ada riwayat pengajuan berkas.
                </div>
            @endforelse
        </div>
    </div>

    <div class="detail-card">
        <div class="detail-card-header">
            <h3>Riwayat Sidang & LSTA</h3>
        </div>
        <div class="detail-card-body">
            <table class="history-table-small">
                <thead>
                    <tr>
                        <th>Jenis</th>
                        <th>Jadwal</th>
                        <th>Ruangan</th>
                        <th>Hasil Ujian</th>
                        {{-- KOLOM BARU --}}
                        <th>Dokumen Hasil</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ta->lstas as $lsta)
                        <tr>
                            <td>LSTA</td>
                            <td>{{ \Carbon\Carbon::parse($lsta->jadwal)->format('d M Y') }}</td>
                            <td>{{ $lsta->ruangan }}</td>
                            <td>{{ $lsta->status }}</td>
                            <td style="text-align: center;">-</td>
                        </tr>
                    @empty
                        @endforelse
                    
                    @forelse ($ta->sidangs as $sidang)
                        <tr>
                            <td>Sidang TA</td>
                            <td>{{ \Carbon\Carbon::parse($sidang->jadwal)->format('d M Y') }}</td>
                            <td>{{ $sidang->ruangan }}</td>
                            <td style="font-weight: bold; color: {{ $sidang->status == 'LULUS' ? 'green' : ($sidang->status == 'TIDAK_LULUS' ? 'red' : 'orange') }}">
                                {{ str_replace('_', ' ', $sidang->status) }}
                            </td>
                            
                            {{-- LOGIKA TOMBOL DOWNLOAD (MENGGUNAKAN RUTE GLOBAL) --}}
                            <td style="text-align: center;">
                                @if(in_array($sidang->status, ['LULUS', 'LULUS_REVISI', 'TIDAK_LULUS']))
                                    <a href="{{ route('dokumen.hasil-sidang', ['sidang' => $sidang->id, 'jenis' => 'revisi']) }}" 
                                       target="_blank" class="btn-download btn-revisi" title="Download Revisi">
                                       Revisi
                                    </a>
                                    <a href="{{ route('dokumen.hasil-sidang', ['sidang' => $sidang->id, 'jenis' => 'berita-acara']) }}" 
                                       target="_blank" class="btn-download btn-ba" title="Download Berita Acara">
                                       BA
                                    </a>
                                @else
                                    <span class="text-muted">Belum tersedia</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        @endforelse

                    @if($ta->lstas->isEmpty() && $ta->sidangs->isEmpty())
                        <tr><td colspan="5" style="text-align: center; color: #777;">Tidak ada riwayat LSTA/Sidang.</td></tr>
                    @endif
                </tbody>

                
            </table>
        </div>
    </div>
</x-staff-layout>
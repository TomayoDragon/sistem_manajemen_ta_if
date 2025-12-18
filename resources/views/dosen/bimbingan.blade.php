<x-dosen-layout>
    <x-slot name="title">
        Mahasiswa Bimbingan
    </x-slot>

    <style>
        .table-wrapper { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table-wrapper th, .table-wrapper td { border: 1px solid #ddd; padding: 12px; text-align: left; vertical-align: top; }
        .table-wrapper th { background-color: #f4f4f4; font-weight: 700; }
        
        .btn-approve {
            padding: 5px 12px; font-size: 0.9rem; text-decoration: none;
            color: white; background-color: #2ecc71; border: none;
            border-radius: 5px; cursor: pointer; transition: background-color 0.2s;
        }
        .btn-approve:hover { background-color: #27ae60; }

        .btn-disabled {
            padding: 5px 12px; font-size: 0.9rem; color: #777;
            background-color: #eee; border: 1px solid #ccc;
            border-radius: 5px; cursor: not-allowed; display: inline-block;
        }
        
        /* --- STYLE BARU UNTUK TOMBOL DOWNLOAD --- */
        .btn-download {
            display: inline-block;
            padding: 5px 10px;
            font-size: 0.85rem;
            text-decoration: none;
            color: white;
            border-radius: 5px;
            margin-right: 5px;
            margin-bottom: 5px;
            transition: background-color 0.2s;
        }
        .btn-download:hover { color: white; opacity: 0.9; }
        
        .btn-revisi { background-color: #e67e22; } /* Oranye */
        .btn-revisi:hover { background-color: #d35400; }
        
        .btn-ba { background-color: #3498db; } /* Biru */
        .btn-ba:hover { background-color: #2980b9; }

        /* Style untuk Status Persetujuan */
        .approval-status { font-size: 0.9rem; margin-bottom: 5px; }
        .status-approved { color: #2ecc71; font-weight: 700; }
        .status-pending { color: #f39c12; font-weight: 700; }
    </style>

    @if (session('success'))
        <div class="content-box" style="background-color: #eBffeb; color: #0a0; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    <h1 class="content-title">Daftar Mahasiswa Bimbingan</h1>

    <div class="content-box">
        <p style="margin-bottom: 20px; color: #555;">
            Halaman ini digunakan untuk memantau progres mahasiswa bimbingan Anda.
            <br>
            Kedua pembimbing (Dosbing 1 & 2) harus memberikan persetujuan sebelum mahasiswa dapat mengupload berkas sidang.
        </p>

        <div style="overflow-x: auto;">
            <table class="table-wrapper">
                <thead>
                    <tr>
                        <th>Mahasiswa</th>
                        <th>Judul TA</th>
                        <th>Status Persetujuan</th>
                        <th>Aksi Anda</th>
                        <th style="text-align: center;">Dokumen Sidang</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($mahasiswaBimbingan as $ta)
                        <tr>
                            <td>
                                {{ $ta->mahasiswa->nama_lengkap }}
                                <br><small style="color: #777;">{{ $ta->mahasiswa->nrp }}</small>
                            </td>
                            <td>{{ \Illuminate\Support\Str::limit($ta->judul, 60) }}</td>
                            <td>
                                <div class="approval-status">
                                    @if ($ta->dosbing_1_approved_at)
                                        <span class="status-approved">✓ Dosbing 1 Disetujui</span>
                                    @else
                                        <span class="status-pending">! Dosbing 1 (Pending)</span>
                                    @endif
                                </div>
                                <div class="approval-status">
                                    @if ($ta->dosbing_2_approved_at)
                                        <span class="status-approved">✓ Dosbing 2 Disetujui</span>
                                    @else
                                        <span class="status-pending">! Dosbing 2 (Pending)</span>
                                    @endif
                                </div>
                            </td>
                            <td style="width: 15%;">
                                @php
                                    $isDosbing1 = (Auth::user()->dosen_id === $ta->dosen_pembimbing_1_id);
                                    $isDosbing2 = (Auth::user()->dosen_id === $ta->dosen_pembimbing_2_id);
                                @endphp

                                @if ($isDosbing1 && !$ta->dosbing_1_approved_at)
                                    <form action="{{ route('dosen.bimbingan.approve', $ta->id) }}" method="POST"
                                          onsubmit="return confirm('Anda yakin ingin memberi izin sidang sebagai Dosbing 1?');">
                                        @csrf
                                        <button type="submit" class="btn-approve">
                                            <i class="fa-solid fa-check"></i> Beri Izin
                                        </button>
                                    </form>
                                @elseif ($isDosbing2 && !$ta->dosbing_2_approved_at)
                                    <form action="{{ route('dosen.bimbingan.approve', $ta->id) }}" method="POST"
                                          onsubmit="return confirm('Anda yakin ingin memberi izin sidang sebagai Dosbing 2?');">
                                        @csrf
                                        <button type="submit" class="btn-approve">
                                            <i class="fa-solid fa-check"></i> Beri Izin
                                        </button>
                                    </form>
                                @else
                                    <span class="btn-disabled" title="Anda sudah menyetujui atau bukan pembimbing di TA ini">
                                        <i class="fa-solid fa-check-double"></i> Disetujui
                                    </span>
                                @endif
                            </td>
                            
                            {{-- KOLOM DOKUMEN SIDANG (DIPERBAIKI) --}}
                            <td style="text-align: center; width: 18%;">
                                @php
                                    // Ambil sidang TERAKHIR mahasiswa ini
                                    $sidangTerakhir = $ta->sidangs->sortByDesc('created_at')->first();
                                @endphp

                                @if ($sidangTerakhir && in_array($sidangTerakhir->status, ['LULUS', 'LULUS_REVISI', 'TIDAK_LULUS']))
                                    
                                    {{-- Tombol Lihat Revisi --}}
                                    {{-- PERUBAHAN: Menambahkan 'mode' => 'view' agar tidak langsung download --}}
                                    <a href="{{ route('dokumen.hasil-sidang', ['sidang' => $sidangTerakhir->id, 'jenis' => 'revisi', 'mode' => 'view']) }}" 
                                       target="_blank" class="btn-download btn-revisi" title="Lihat Lembar Revisi">
                                        <i class="fa-solid fa-eye"></i> Revisi
                                    </a>

                                    {{-- Tombol Lihat BA --}}
                                    {{-- PERUBAHAN: Menambahkan 'mode' => 'view' agar tidak langsung download --}}
                                    <a href="{{ route('dokumen.hasil-sidang', ['sidang' => $sidangTerakhir->id, 'jenis' => 'berita-acara', 'mode' => 'view']) }}" 
                                       target="_blank" class="btn-download btn-ba" title="Lihat Berita Acara">
                                        <i class="fa-solid fa-file-pdf"></i> BA
                                    </a>

                                @elseif($sidangTerakhir && $sidangTerakhir->status == 'TERJADWAL')
                                    <span style="font-size: 0.85rem; color: #3498db; font-style: italic;">
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
                        <tr>
                            <td colspan="5" style="text-align: center; color: #777; padding: 20px;">
                                Anda tidak memiliki mahasiswa bimbingan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-dosen-layout>
<x-staff-layout>
    <x-slot name="title">
        Review Pengajuan (ID: {{ $pengajuan->id }})
    </x-slot>

    <style>
        /* Layout Grid Utama */
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 2fr; /* Kolom Kiri 1 bagian, Kanan 2 bagian */
            gap: 25px;
        }

        /* Kotak Putih Standar */
        .detail-box {
            background-color: #fff;
            border-radius: 10px;
            border: 1px solid #eef2f7; /* Border lebih halus */
            padding: 30px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.03); /* Shadow lebih modern */
        }

        .detail-box h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 25px;
            border-bottom: 2px solid #f0f2f5;
            padding-bottom: 15px;
        }

        /* Typography Info */
        .info-item { margin-bottom: 18px; }
        .info-label { display: block; font-size: 0.85rem; color: #7f8c8d; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
        .info-value { font-weight: 700; color: #2c3e50; font-size: 1.05rem; }

        /* --- STYLE TOMBOL FILE (REVISI) --- */
        .file-list-header {
            font-size: 1rem; font-weight: 700; color: #2c3e50;
            margin-top: 30px; margin-bottom: 15px;
        }
        
        .btn-file-preview {
            display: flex;
            align-items: center;
            justify-content: space-between; /* Ikon kiri, teks kanan */
            padding: 12px 15px;
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            text-decoration: none;
            color: #34495e;
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 10px;
            transition: all 0.2s ease;
        }
        
        .btn-file-preview:hover {
            background-color: #e3f2fd; /* Biru muda saat hover */
            border-color: #bbdefb;
            color: #0a2e6c;
            transform: translateY(-2px); /* Efek angkat sedikit */
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .btn-file-preview .file-icon {
            margin-right: 12px;
            color: #e74c3c; /* Warna Merah PDF */
            font-size: 1.2rem;
        }

        .btn-file-preview .external-icon {
            font-size: 0.8rem;
            color: #95a5a6;
        }

        /* Form Validasi */
        .form-group { margin-bottom: 25px; }
        .form-group label { display: block; font-weight: 700; margin-bottom: 8px; color: #34495e; }
        
        .form-textarea {
            width: 100%; height: 120px; padding: 12px;
            font-size: 1rem; border: 1px solid #ced4da; border-radius: 8px;
            transition: border-color 0.2s;
        }
        .form-textarea:focus { border-color: #3498db; outline: none; }

        /* Tombol Aksi */
        .action-buttons {
            display: flex; gap: 15px; margin-top: 10px;
        }
        
        .btn-action {
            flex: 1; /* Tombol memenuhi lebar container */
            padding: 14px;
            font-size: 1rem; font-weight: 700; color: #fff;
            border: none; border-radius: 8px; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: background-color 0.3s;
        }

        .btn-approve { background-color: #27ae60; }
        .btn-approve:hover { background-color: #219150; }

        .btn-reject { background-color: #e74c3c; }
        .btn-reject:hover { background-color: #c0392b; }

        /* Responsif */
        @media (max-width: 768px) {
            .detail-grid { grid-template-columns: 1fr; }
        }
    </style>

    <h1 class="content-title">Review Paket Pengajuan Sidang</h1>

    <div class="detail-grid">
        <div class="detail-box">
            <h3>Informasi Pengajuan</h3>
            <div class="info-item">
                <span class="info-label">Mahasiswa</span>
                <span class="info-value">{{ $pengajuan->tugasAkhir->mahasiswa->nama_lengkap }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">NRP</span>
                <span class="info-value">{{ $pengajuan->tugasAkhir->mahasiswa->nrp }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Judul Tugas Akhir</span>
                <span class="info-value" style="font-size: 1rem; line-height: 1.4;">
                    {{ $pengajuan->tugasAkhir->judul }}
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">Tanggal Pengajuan</span>
                <span class="info-value">{{ $pengajuan->created_at->format('d M Y, H:i') }}</span>
            </div>

            <h4 class="file-list-header">Berkas Lampiran</h4>
            <div class="file-list">
                @forelse ($pengajuan->dokumen as $dokumen)
                    {{-- 
                        PERBAIKAN FITUR: 
                        Menggunakan parameter ['mode' => 'view'] dan target="_blank"
                        agar file terbuka di tab baru (Preview), bukan download.
                    --}}
                    <a href="{{ route('dokumen.download', ['dokumen' => $dokumen->id, 'mode' => 'view']) }}" 
                       target="_blank" 
                       class="btn-file-preview"
                       title="Klik untuk melihat dokumen">
                        
                        <div style="display: flex; align-items: center;">
                            <i class="fa-solid fa-file-pdf file-icon"></i>
                            <div>
                                {{ $dokumen->tipe_dokumen }}
                                <div style="font-size: 0.75rem; color: #7f8c8d; font-weight: normal;">
                                    {{ Str::limit($dokumen->nama_file_asli, 25) }}
                                </div>
                            </div>
                        </div>
                        <i class="fa-solid fa-arrow-up-right-from-square external-icon"></i>
                    </a>
                @empty
                    <div style="padding: 15px; background: #fff5f5; border: 1px solid #feb2b2; color: #c53030; border-radius: 6px;">
                        <i class="fa-solid fa-triangle-exclamation"></i> Error: Tidak ada dokumen ditemukan.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="detail-box">
            <h3>Keputusan Validasi</h3>
            
            <form action="{{ route('staff.validasi.process', $pengajuan->id) }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label for="catatan_validasi">Catatan Validasi</label>
                    <textarea id="catatan_validasi" name="catatan_validasi" class="form-textarea" 
                              placeholder="Berikan catatan jika ada revisi (Wajib diisi jika Ditolak)...">{{ old('catatan_validasi') }}</textarea>
                    <small style="color: #7f8c8d; margin-top: 5px; display: block;">
                        * Catatan ini akan muncul di dashboard mahasiswa.
                    </small>
                </div>

                @if ($errors->any())
                    <div style="margin-bottom: 20px; background: #fee2e2; border: 1px solid #fca5a5; padding: 15px; border-radius: 8px; color: #b91c1c;">
                        <strong><i class="fa-solid fa-circle-exclamation"></i> Terdapat Kesalahan:</strong>
                        <ul style="margin-left: 20px; margin-top: 5px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="action-buttons">
                    <button type="submit" name="keputusan" value="TERIMA" class="btn-action btn-approve"
                            onclick="return confirm('Apakah Anda yakin data ini sudah BENAR dan LENGKAP? Mahasiswa akan masuk antrean sidang.')">
                        <i class="fa-solid fa-check-circle"></i> Terima Berkas
                    </button>
                    
                    <button type="submit" name="keputusan" value="TOLAK" class="btn-action btn-reject"
                            onclick="return confirm('Apakah Anda yakin ingin MENOLAK pengajuan ini?')">
                        <i class="fa-solid fa-circle-xmark"></i> Tolak Berkas
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-staff-layout>
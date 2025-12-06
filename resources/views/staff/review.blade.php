<x-staff-layout>
    <x-slot name="title">
        Review Pengajuan (ID: {{ $pengajuan->id }})
    </x-slot>

    <style>
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 2fr; /* 2 kolom */
            gap: 20px;
        }
        .detail-box {
            background-color: #fff;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            padding: 25px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .detail-box h3 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .info-item {
            margin-bottom: 15px;
        }
        .info-label {
            display: block;
            font-size: 0.9rem;
            color: #777;
            margin-bottom: 4px;
        }
        .info-value {
            font-weight: 700;
            color: #333;
            font-size: 1.1rem;
        }

        /* Style Link Download (baru) */
        .file-list-header {
            font-size: 1.1rem;
            font-weight: 700;
            color: #333;
            margin-top: 25px; /* Jarak dari 'Ruangan' */
            margin-bottom: 10px;
        }
        .file-list a {
            display: block;
            padding: 12px 15px;
            background-color: #f4f7f6;
            border: 1px solid #ddd;
            border-radius: 8px;
            text-decoration: none;
            color: #0a2e6c;
            font-weight: 700;
            font-size: 0.9rem;
            margin-bottom: 8px;
            transition: background-color 0.2s;
        }
        .file-list a:hover {
            background-color: #eef5ff;
        }
        .file-list a i {
            margin-right: 10px;
            color: #3498db;
        }

        /* Form Validasi */
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .form-textarea {
            width: 100%;
            height: 120px;
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        .btn-approve {
            padding: 12px 25px;
            font-size: 1rem;
            font-weight: 700;
            color: #fff;
            background-color: #2ecc71; /* Hijau */
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        .btn-reject {
            padding: 12px 25px;
            font-size: 1rem;
            font-weight: 700;
            color: #fff;
            background-color: #e74c3c; /* Merah */
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
    </style>

    <h1 class="content-title">Review Paket Pengajuan Sidang</h1>

    <div class="detail-grid">
        <div class="detail-box">
            <h3>Informasi Pengajuan</h3>
            <div class="info-item">
                <span class="info-label">Mahasiswa:</span>
                <span class="info-value">{{ $pengajuan->tugasAkhir->mahasiswa->nama_lengkap }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">NRP:</span>
                <span class="info-value">{{ $pengajuan->tugasAkhir->mahasiswa->nrp }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Judul TA:</span>
                <span class="info-value">{{ $pengajuan->tugasAkhir->judul }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Tanggal Pengajuan:</span>
                <span class="info-value">{{ $pengajuan->created_at->format('d M Y, H:i') }}</span>
            </div>

            <h3 style="margin-top: 30px;">Berkas Pengajuan</h3>
            <div class="file-list">
                @forelse ($pengajuan->dokumen as $dokumen)
                    <a href="{{ route('dokumen.download', $dokumen->id) }}" target="_blank">
                        <i class="fa-solid fa-file-pdf"></i>
                        {{ $dokumen->tipe_dokumen }} ({{ $dokumen->nama_file_asli }})
                    </a>
                @empty
                    <p style="color: red;">Error: Tidak ada dokumen yang terhubung dengan pengajuan ini.</p>
                @endforelse
            </div>
        </div>

        <div class="detail-box">
            <h3>Keputusan Validasi</h3>
            
            <form action="{{ route('staff.validasi.process', $pengajuan->id) }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label for="catatan_validasi">Catatan (Wajib diisi jika ditolak)</label>
                    <textarea id="catatan_validasi" name="catatan_validasi" class="form-textarea" 
                              placeholder="Contoh: KHS buram, silakan upload ulang.">{{ old('catatan_validasi') }}</textarea>
                </div>

                @if ($errors->any())
                    <div style="color: red; margin-bottom: 15px; background: #ffebeb; padding: 10px; border-radius: 8px;">
                        <strong>Error:</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div style="display: flex; gap: 10px;">
                    <button type="submit" name="keputusan" value="TERIMA" class="btn-approve">
                        <i class="fa-solid fa-check"></i> Terima
                    </button>
                    <button type="submit" name="keputusan" value="TOLAK" class="btn-reject">
                        <i class="fa-solid fa-times"></i> Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-staff-layout>
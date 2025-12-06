<x-dosen-layout>
    <x-slot name="title">
        Form Penilaian {{ Str::upper($type) }}
    </x-slot>

    <style>
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 2fr; /* 2 kolom: Info (kecil) & Form (besar) */
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
        .info-item { margin-bottom: 15px; }
        .info-label { display: block; font-size: 0.9rem; color: #777; margin-bottom: 4px; }
        .info-value { font-weight: 700; color: #333; font-size: 1.1rem; }
        
        /* Link Download */
        .file-list-header { font-size: 1.1rem; font-weight: 700; color: #333; margin-top: 25px; margin-bottom: 10px; }
        .file-list a { display: block; padding: 12px 15px; background-color: #f4f7f6; border: 1px solid #ddd; border-radius: 8px; text-decoration: none; color: #0a2e6c; font-weight: 700; font-size: 0.9rem; margin-bottom: 8px; transition: background-color 0.2s; }
        .file-list a:hover { background-color: #eef5ff; }
        .file-list a i { margin-right: 10px; color: #3498db; }

        /* Form Penilaian */
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 700; margin-bottom: 8px; }
        .form-input-number { width: 100px; padding: 10px 15px; font-size: 1rem; border: 1px solid #ccc; border-radius: 8px; }
        .form-textarea { width: 100%; height: 120px; padding: 10px; font-size: 1rem; border: 1px solid #ccc; border-radius: 8px; }
        .btn-submit { padding: 12px 25px; font-size: 1rem; font-weight: 700; color: #fff; background-color: #0a2e6c; border: none; border-radius: 8px; cursor: pointer; transition: background-color 0.3s; }
        .btn-submit:hover { background-color: #082557; }

        /* Responsif untuk layar kecil */
        @media (max-width: 768px) {
            .detail-grid { grid-template-columns: 1fr; }
        }
    </style>

    <h1 class="content-title">Form Penilaian {{ Str::upper($type) }}</h1>

    <div class="detail-grid">
        <!-- Kolom Kiri: Informasi Mahasiswa -->
        <div class="detail-box">
            <h3>Informasi Mahasiswa</h3>
            <div class="info-item">
                <span class="info-label">Mahasiswa:</span>
                <span class="info-value">{{ $event->tugasAkhir->mahasiswa->nama_lengkap }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">NRP:</span>
                <span class="info-value">{{ $event->tugasAkhir->mahasiswa->nrp }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Judul TA:</span>
                <span class="info-value">{{ $event->tugasAkhir->judul }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Jadwal:</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($event->jadwal)->format('d M Y, H:i') }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Ruangan:</span>
                <span class="info-value">{{ $event->ruangan }}</span>
            </div>

           @if ($event->pengajuanSidang && $event->pengajuanSidang->dokumen->isNotEmpty())
                <h4 class="file-list-header">Berkas Mahasiswa</h4>
                <div class="file-list">
                    @foreach ($event->pengajuanSidang->dokumen as $dokumen)
                        
                        <a href="{{ route('dokumen.download', ['dokumen' => $dokumen->id, 'mode' => 'view']) }}" 
                           target="_blank"
                           title="Klik untuk melihat dokumen">
                            
                            <i class="fa-solid fa-eye"></i>
                            
                            {{ $dokumen->tipe_dokumen }}
                        </a>
                        @endforeach
                </div>
            @endif
        </div>

        <!-- Kolom Kanan: Form Penilaian -->
        <div class="detail-box">
            <h3>Lembar Penilaian</h3>
            
            <form action="{{ route('dosen.penilaian.store', ['type' => $type, 'id' => $event->id]) }}" method="POST">
                @csrf
                
                @if ($errors->any())
                    <div style="color: #721c24; margin-bottom: 20px; background: #f8d7da; padding: 15px; border-radius: 8px; border: 1px solid #f5c6cb;">
                        <strong><i class="fa-solid fa-triangle-exclamation"></i> Terdapat Kesalahan:</strong>
                        <ul style="margin-left: 20px; margin-top: 5px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- PENTING: Atribut max="100" ditambahkan di semua input angka -->
                
                <div class="form-group">
                    <label for="nilai_materi">Materi Tugas Akhir (Bobot 15%)</label>
                    <input type="number" id="nilai_materi" name="nilai_materi" class="form-input-number" 
                           min="0" max="100" 
                           placeholder="0-100"
                           value="{{ old('nilai_materi', $existingScore->nilai_materi ?? 0) }}" required>
                </div>

                <div class="form-group">
                    <label for="nilai_sistematika">Sistematika & Penulisan (Bobot 10%)</label>
                    <input type="number" id="nilai_sistematika" name="nilai_sistematika" class="form-input-number" 
                           min="0" max="100" 
                           placeholder="0-100"
                           value="{{ old('nilai_sistematika', $existingScore->nilai_sistematika ?? 0) }}" required>
                </div>

                <div class="form-group">
                    <label for="nilai_mempertahankan">Mempertahankan Tugas Akhir (Bobot 50%)</label>
                    <input type="number" id="nilai_mempertahankan" name="nilai_mempertahankan" class="form-input-number" 
                           min="0" max="100" 
                           placeholder="0-100"
                           value="{{ old('nilai_mempertahankan', $existingScore->nilai_mempertahankan ?? 0) }}" required>
                </div>

                <div class="form-group">
                    <label for="nilai_pengetahuan_bidang">Pengetahuan Bidang Studi (Bobot 15%)</label>
                    <input type="number" id="nilai_pengetahuan_bidang" name="nilai_pengetahuan_bidang" class="form-input-number" 
                           min="0" max="100" 
                           placeholder="0-100"
                           value="{{ old('nilai_pengetahuan_bidang', $existingScore->nilai_pengetahuan_bidang ?? 0) }}" required>
                </div>
                
                <div class="form-group">
                    <label for="nilai_karya_ilmiah">Karya Ilmiah (Bobot 10%)</label>
                    <input type="number" id="nilai_karya_ilmiah" name="nilai_karya_ilmiah" class="form-input-number" 
                           min="0" max="100" 
                           placeholder="0-100"
                           value="{{ old('nilai_karya_ilmiah', $existingScore->nilai_karya_ilmiah ?? 0) }}" required>
                </div>

                <hr style="margin: 30px 0; border: 0; border-top: 1px solid #eee;">

                <div class="form-group">
                    <label for="komentar_revisi">Komentar / Revisi</label>
                    <textarea id="komentar_revisi" name="komentar_revisi" class="form-textarea" 
                              placeholder="Tuliskan komentar atau revisi untuk mahasiswa...">{{ old('komentar_revisi', $existingScore->komentar_revisi ?? '') }}</textarea>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fa-solid fa-save"></i> Simpan Penilaian
                </button>
            </form>
        </div>
    </div>

    <!-- Script JavaScript untuk memaksa nilai max 100 secara realtime -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const numberInputs = document.querySelectorAll('.form-input-number');
            
            numberInputs.forEach(input => {
                // Saat user mengetik (input event)
                input.addEventListener('input', function() {
                    let value = parseInt(this.value);
                    
                    if (value > 100) {
                        this.value = 100; // Kembalikan ke 100 jika lebih
                    } else if (value < 0) {
                        this.value = 0;   // Kembalikan ke 0 jika kurang
                    }
                });

                // Saat user selesai mengetik/pindah kolom (change event)
                input.addEventListener('change', function() {
                     let value = parseInt(this.value);
                     if (value > 100) this.value = 100;
                     if (value < 0) this.value = 0;
                });
            });
        });
    </script>
</x-dosen-layout>
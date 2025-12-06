<x-dynamic-component :component="$layout">
    <x-slot name="title">
        Cek Integritas Dokumen
    </x-slot>

    <style>
        /* ... (Semua CSS Anda tetap sama) ... */
        .check-box { padding: 30px; border-radius: 8px; border: 1px solid #ddd; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .check-box h3 { font-size: 1.3rem; margin-bottom: 15px; }
        .hash-label { font-size: 0.9rem; color: #777; margin-bottom: 5px; }
        .hash-value { font-family: monospace; font-size: 1.1rem; word-break: break-all; padding: 10px; background: #f4f4f4; border-radius: 5px; }
        .form-input { width: 100%; padding: 12px 15px; font-size: 1rem; border: 1px solid #ccc; border-radius: 8px; margin-top: 10px; }
        .btn-check { padding: 12px 25px; font-size: 1rem; font-weight: 700; color: #fff; background-color: #0a2e6c; border: none; border-radius: 8px; cursor: pointer; margin-top: 20px; }
        .result-box { margin-top: 20px; padding: 20px; text-align: center; border-radius: 8px; }
        .result-match { background-color: #eBffeb; border: 1px solid #2ecc71; }
        .result-mismatch { background-color: #ffebeB; border: 1px solid #e74c3c; }
    </style>

    <h1 class="content-title">Verifikasi Integritas Dokumen</h1>

    <div class="content-box">
        <div class="check-box">
            <h3>Hash Orisinal (Tersimpan di Database)</h3>
            <p style="margin-bottom: 15px;">
                Anda sedang memverifikasi file: 
                <strong style="color: #0a2e6c;">{{ $dokumen->nama_file_asli }}</strong>
                ({{ $dokumen->tipe_dokumen }})
            </p>
            
            <div class="hash-label">Hash Gabungan (SHA-512 + BLAKE2b):</div>
            <div class="hash-value">{{ $dokumen->hash_combined }}</div>
            <small style="color: #777; margin-top: 10px; display: block;">
                Dimiliki oleh: <strong>{{ $dokumen->pengajuanSidang->tugasAkhir->mahasiswa->nama_lengkap }}</strong>
                <br>
                Ditandatangani pada: {{ $dokumen->created_at->format('d M Y, H:i') }}
            </small>
        </div>

        <hr style="margin: 30px 0; border: none;">

        <div class="check-box">
            <h3>Alat Pengecekan</h3>
            <p style="color: #555;">
                Upload file yang ingin Anda cek keasliannya:
            </p>
            
            <form action="{{ route('integritas.verify', $dokumen->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div style="margin-top: 15px;">
                    <label>Upload File Pembanding</label>
                    <input type="file" name="file_cek" class="form-input" required>
                </div>
                <button type="submit" class="btn-check">
                    <i class="fa-solid fa-shield-halved"></i> Verifikasi Sekarang
                </button>
            </form>

            @if (session('checkResult') === true)
                <div class="result-box result-match">
                    <h3 style="color: #27ae60;">VERIFIKASI BERHASIL</h3>
                    <p>
                        Tanda tangan digital valid.

                    </p>
                </div>
            @elseif (session('checkResult') === false)
                <div class="result-box result-mismatch">
                    <h3 style="color: #c0392b;">VERIFIKASI GAGAL</h3>
                    <p>
                        Tanda tangan digital tidak valid.

                    </p>
                    <div class="hash-label" style="margin-top: 15px;">Hash Baru (dari file upload):</div>
                    <div class="hash-value" style="background: #fff2f2;">{{ session('newHash') }}</div>
                </div>
            @endif
        </div>
    </div>
</x-dynamic-component>
<x-dynamic-component :component="$layout">
    <x-slot name="title">
        Cek Integritas Dokumen
    </x-slot>

    {{-- --- LOGIC PRE-PROCESSING (FIX ERROR RELASI) --- --}}
    @php
        // Default values
        $namaMahasiswa = '-';
        $waktuUpload = $dokumen->created_at;

        // Cek Source untuk menentukan jalur relasi database
        if (isset($source) && $source == 'system') {
            // Jalur: DokumenHasilSidang -> Sidang -> TugasAkhir -> Mahasiswa
            $namaMahasiswa = $dokumen->sidang->tugasAkhir->mahasiswa->nama_lengkap ?? 'Data Mahasiswa Tidak Ditemukan';
        } else {
            // Jalur: DokumenPengajuan (Upload) -> PengajuanSidang -> TugasAkhir -> Mahasiswa
            $namaMahasiswa = $dokumen->pengajuanSidang->tugasAkhir->mahasiswa->nama_lengkap ?? 'Data Mahasiswa Tidak Ditemukan';
        }
    @endphp

    <style>
        .check-box {
            padding: 30px; border-radius: 8px; border: 1px solid #ddd;
            background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .check-box h3 { font-size: 1.3rem; margin-bottom: 15px; color: #0a2e6c; }
        .label { font-size: 0.9rem; color: #777; margin-bottom: 5px; font-weight: bold; }
        .crypto-value { 
            font-family: monospace; font-size: 0.9rem; word-break: break-all; 
            padding: 15px; background: #f8f9fa; border-radius: 5px;
            border-left: 5px solid #0a2e6c; color: #333;
        }
        .form-input { 
            width: 100%; padding: 12px 15px; font-size: 1rem; border: 1px solid #ccc;
            border-radius: 8px; margin-top: 10px;
        }
        .btn-check { 
            padding: 12px 25px; font-size: 1rem; font-weight: 700; color: #fff; 
            background-color: #0a2e6c; border: none; border-radius: 8px; cursor: pointer;
            margin-top: 20px; width: 100%;
        }
        .btn-check:hover { background-color: #082456; }
        
        .result-box { 
            margin-top: 20px; padding: 20px; text-align: center; border-radius: 8px;
        }
        .result-match { background-color: #d1fae5; border: 1px solid #34d399; color: #065f46; }
        .result-mismatch { background-color: #fee2e2; border: 1px solid #f87171; color: #991b1b; }
        
        .badge-source {
            font-size: 0.75rem; padding: 3px 8px; border-radius: 4px; font-weight: bold; text-transform: uppercase;
        }
        .badge-system { background-color: #e0f2fe; color: #0369a1; }
        .badge-upload { background-color: #f3f4f6; color: #374151; }
    </style>

    <h1 class="content-title">Verifikasi Integritas Dokumen</h1>

    <div class="content-box">
        
        {{-- BAGIAN 1: INFO DOKUMEN & SIGNATURE --}}
        <div class="check-box">
            <h3><i class="fa-solid fa-file-signature"></i> Digital Signature Asli (Tersimpan)</h3>
            
            <p style="margin-bottom: 15px; line-height: 1.6;">
                Anda sedang memverifikasi dokumen: <br>
                <strong style="color: #0a2e6c; font-size: 1.1rem;">{{ $dokumen->nama_file_asli }}</strong>
                <br>
                @if(isset($source) && $source == 'system')
                    <span class="badge-source badge-system">System Generated</span>
                @else
                    <span class="badge-source badge-upload">Mahasiswa Upload</span>
                @endif
                <span style="color: #666;">({{ $dokumen->tipe_dokumen ?? $dokumen->jenis_dokumen }})</span>
            </p>
            
            <div class="label">Digital Signature (EdDSA - Base64):</div>
            <div class="crypto-value">
                {{ $dokumen->signature_base64 ?? $dokumen->signature_data }}
            </div>
            
        </div>

        {{-- BAGIAN 2: FORM PENGECEKAN --}}
        <div class="check-box" style="border-top: 4px solid #0a2e6c;">
            <h3><i class="fa-solid fa-magnifying-glass"></i> Alat Pengecekan</h3>
            <p style="color: #555; margin-bottom: 20px;">
                Sistem akan memverifikasi apakah file yang Anda unggah memiliki pasangan <strong>Hash + Private Key</strong> yang valid dan cocok dengan Signature di atas.
            </p>
            
            <form action="{{ route('integritas.verify', $dokumen->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                {{-- [PENTING] Kirim parameter source agar Controller tidak bingung --}}
                <input type="hidden" name="source" value="{{ $source ?? 'upload' }}">

                <div>
                    <label class="label">Upload File Pembanding</label>
                    <input type="file" name="file_cek" class="form-input" required accept=".pdf, .zip, .mp4">
                    @error('file_cek')
                        <div style="color: red; font-size: 0.85rem; margin-top: 5px;">{{ $message }}</div>
                    @enderror
                </div>
                
                <button type="submit" class="btn-check">
                    <i class="fa-solid fa-shield-halved"></i> Verifikasi Signature
                </button>
            </form>

            {{-- BAGIAN 3: HASIL PENGECEKAN --}}
            @if (session()->has('checkResult'))
                @php $result = session('checkResult'); @endphp

                @if ($result === true)
                    <div class="result-box result-match">
                        <h3 style="margin: 0;"><i class="fa-solid fa-circle-check"></i> VERIFIKASI BERHASIL (VALID)</h3>
                        <p style="margin-top: 10px;">
                            <strong>Tanda Tangan Digital VALID.</strong><br>
                            File ini otentik, berasal dari pemilik kunci privat yang sah, dan isinya tidak pernah dimodifikasi sejak ditandatangani.
                        </p>
                    </div>
                @else
                    <div class="result-box result-mismatch">
                        <h3 style="margin: 0;"><i class="fa-solid fa-triangle-exclamation"></i> VERIFIKASI GAGAL (INVALID)</h3>
                        <p style="margin-top: 10px;">
                            <strong>Tanda Tangan Digital TIDAK VALID.</strong><br>
                            File yang Anda unggah berbeda dengan data yang tersimpan di server.
                            <br>Kemungkinan file rusak, telah diedit isinya, atau merupakan file yang salah.
                        </p>
                        @if(session('newHash'))
                            <div style="margin-top: 15px; text-align: left;">
                                <small>Hash File Upload (SHA-512):</small>
                                <div class="crypto-value" style="background: rgba(255,255,255,0.7); font-size: 0.8rem;">
                                    {{ session('newHash') }}
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-dynamic-component>
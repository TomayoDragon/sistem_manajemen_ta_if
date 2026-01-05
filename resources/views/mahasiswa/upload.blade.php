<x-mahasiswa-layout>
    <x-slot name="title">
        Upload Berkas Sidang TA
    </x-slot>

    <style>
        /* --- Container & Layout --- */
        .content-box {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.04);
            border: 1px solid #f0f2f5;
            margin-bottom: 30px;
        }

        .content-title {
            font-size: 1.75rem;
            font-weight: 800;
            color: #2c3e50;
            margin-bottom: 25px;
            position: relative;
        }

        /* --- Status Box (Alerts) --- */
        .status-box {
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 30px;
            border: 2px dashed transparent;
        }

        .status-box .icon {
            font-size: 4rem;
            margin-bottom: 20px;
            display: block;
        }

        .status-box h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .status-box p {
            color: #666;
            font-size: 1rem;
            line-height: 1.6;
        }

        .pending-box {
            background-color: #fffaf0;
            border-color: #f39c12;
            color: #d35400;
        }

        .pending-box .icon {
            color: #f39c12;
        }

        .accept-box {
            background-color: #f0fff4;
            border-color: #2ecc71;
            color: #27ae60;
        }

        .accept-box .icon {
            color: #2ecc71;
        }

        .reject-box {
            background-color: #fff5f5;
            border-color: #e74c3c;
            color: #c0392b;
        }

        .reject-box .icon {
            color: #e74c3c;
        }

        .reject-notes {
            margin-top: 20px;
            background: #fff;
            padding: 15px;
            border-left: 4px solid #c0392b;
            text-align: left;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        /* --- Form Upload --- */
        .upload-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .file-input-wrapper {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            transition: all 0.2s;
        }

        .file-input-wrapper:hover {
            border-color: #3498db;
            background: #fff;
            box-shadow: 0 4px 6px rgba(52, 152, 219, 0.1);
        }

        .file-input-wrapper label {
            display: block;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
            font-size: 0.95rem;
        }

        .file-desc {
            font-size: 0.8rem;
            color: #7f8c8d;
            margin-bottom: 10px;
            display: block;
            line-height: 1.4;
        }

        .form-input {
            width: 100%;
            padding: 8px;
            font-size: 0.9rem;
            border: 1px solid #ced4da;
            border-radius: 6px;
            background: #fff;
        }

        .btn-submit {
            display: block;
            width: 100%;
            padding: 15px;
            font-size: 1.1rem;
            font-weight: 700;
            color: #fff;
            background-color: #0a2e6c;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            margin-top: 30px;
            transition: background-color 0.2s, transform 0.1s;
            box-shadow: 0 4px 6px rgba(10, 46, 108, 0.2);
        }

        .btn-submit:hover {
            background-color: #082456;
            transform: translateY(-2px);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* --- History Table --- */
        .history-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .history-table th {
            background-color: #f8f9fa;
            color: #2c3e50;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.85rem;
            padding: 12px;
            border-bottom: 2px solid #e9ecef;
            text-align: left;
        }

        .history-table td {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
            color: #333;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-pending {
            background: #fff3cd;
            color: #856404;
        }

        .badge-terima {
            background: #d4edda;
            color: #155724;
        }

        .badge-tolak {
            background: #f8d7da;
            color: #721c24;
        }
    </style>

    <h1 class="content-title">Upload Paket Berkas Sidang</h1>

    {{-- ALERT VALIDASI (ERROR DARI LARAVEL VALIDATION) --}}
    @if ($errors->any())
        <div style="background: #fee2e2; color: #b91c1c; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #fca5a5;">
            <strong><i class="fa-solid fa-triangle-exclamation"></i> Terdapat Kesalahan:</strong>
            <ul style="margin-top: 5px; margin-left: 20px;">
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    {{-- ALERT ERROR SYSTEM (DARI CONTROLLER CATCH / LOGIC) --}}
    {{-- INI YANG SEBELUMNYA HILANG --}}
    @if (session('error'))
        <div style="background: #fee2e2; color: #b91c1c; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #fca5a5;">
            <strong><i class="fa-solid fa-circle-xmark"></i> Gagal:</strong>
            {{ session('error') }}
        </div>
    @endif

    {{-- ALERT SUKSES --}}
    @if (session('success'))
        <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #86efac;">
            <i class="fa-solid fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <div class="content-box">
        @if ($pengajuanTerbaru && $pengajuanTerbaru->status_validasi == 'PENDING')
            <div class="status-box pending-box">
                <i class="fa-solid fa-hourglass-half icon animate-pulse"></i>
                <h3>Paket Berkas Sedang Diverifikasi</h3>
                <p>
                    Anda mengirimkan paket pada <strong>{{ $pengajuanTerbaru->created_at->format('d M Y, H:i') }}</strong>.
                    <br>Mohon tunggu Staf PAJ memeriksa kelengkapan berkas Anda.
                </p>
            </div>

        @elseif ($pengajuanTerbaru && $pengajuanTerbaru->status_validasi == 'TERIMA')
            <div class="status-box accept-box">
                <i class="fa-solid fa-circle-check icon"></i>
                <h3>Selamat! Berkas Anda Disetujui</h3>
                <p>
                    Paket berkas Anda telah valid. Silakan cek menu <strong>"Sidang / LSTA"</strong> secara berkala
                    untuk melihat jadwal sidang Anda.
                </p>
            </div>

        @else
            @if ($pengajuanTerbaru && $pengajuanTerbaru->status_validasi == 'TOLAK')
                <div class="status-box reject-box">
                    <i class="fa-solid fa-circle-xmark icon"></i>
                    <h3>Pengajuan Sebelumnya Ditolak</h3>
                    <p>Mohon perbaiki berkas sesuai catatan revisi di bawah ini, lalu upload ulang seluruh paket.</p>
                    <div class="reject-notes">
                        <strong><i class="fa-solid fa-comment-dots"></i> Catatan Staf PAJ:</strong>
                        <p style="margin-top: 5px; color: #333;">{{ $pengajuanTerbaru->catatan_validasi }}</p>
                    </div>
                </div>
            @endif

            <form action="{{ route('mahasiswa.upload.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="upload-grid">
                    <div class="file-input-wrapper">
                        <label>1. Naskah TA (Folder .zip)</label>
                        <span class="file-desc">Folder berisi seluruh file naskah, dikompres menjadi .zip/.rar (Max 50MB).</span>
                        <input type="file" name="naskah_ta" class="form-input" accept=".zip,.rar" required>
                    </div>

                    <div class="file-input-wrapper">
                        <label>2. Proposal + Bukti Penetapan</label>
                        <span class="file-desc">Proposal TA & Screenshot penetapan Dosbing digabung jadi 1 PDF.</span>
                        <input type="file" name="proposal_ta" class="form-input" accept=".pdf" required>
                    </div>

                    <div class="file-input-wrapper">
                        <label>3. Artikel Jurnal TA</label>
                        <span class="file-desc">Sesuai template Ubaya. Format PDF.</span>
                        <input type="file" name="artikel_jurnal" class="form-input" accept=".pdf" required>
                    </div>

                    <div class="file-input-wrapper">
                        <label>4. Kartu Studi Terakhir</label>
                        <span class="file-desc">Scan/Print out KS semester terakhir. Format PDF.</span>
                        <input type="file" name="kartu_studi" class="form-input" accept=".pdf" required>
                    </div>

                    <div class="file-input-wrapper">
                        <label>5. Surat Tugas TA</label>
                        <span class="file-desc">File Surat Tugas resmi. Format PDF.</span>
                        <input type="file" name="surat_tugas" class="form-input" accept=".pdf" required>
                    </div>

                    <div class="file-input-wrapper">
                        <label>6. Kartu & Bukti Bimbingan</label>
                        <span class="file-desc">Minimal 8x bimbingan per dosen pembimbing. Format PDF.</span>
                        <input type="file" name="bukti_bimbingan" class="form-input" accept=".pdf" required>
                    </div>

                    <div class="file-input-wrapper">
                        <label>7. Sertifikat LSTA</label>
                        <span class="file-desc">Bukti Pendengar (1x) & Penyaji (1x) digabung. Format PDF.</span>
                        <input type="file" name="sertifikat_lsta" class="form-input" accept=".pdf" required>
                    </div>

                    {{-- FILE KE-8 (YANG SUDAH DIPERBAIKI) --}}
                    <div class="file-input-wrapper">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            8. Screenshot Bukti Persetujuan Dosbing
                        </label>
                        <span class="file-desc">Format: PDF (Max 5MB)</span>
                        <input type="file" name="bukti_persetujuan" accept=".pdf" required
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <div class="file-input-wrapper" style="grid-column: 1 / -1;">
                        <label>9. Video Promosi (MP4)</label>
                        <span class="file-desc">Durasi 1-3 menit. Memuat Logo Ubaya, NRP, Nama Mhs & Dosen. (Max 100MB).</span>
                        <input type="file" name="video_promosi" class="form-input" accept=".mp4" required>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fa-solid fa-file-signature"></i> Upload & Tanda Tangani Paket Berkas
                </button>
            </form>
        @endif
    </div>

    <h2 class="content-title" style="font-size: 1.3rem; margin-top: 40px;">Riwayat Pengajuan</h2>
    <div class="content-box">
        <table class="history-table">
            <thead>
                <tr>
                    <th>Tgl Pengajuan</th>
                    <th>Status</th>
                    <th>Catatan Validasi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($riwayatPengajuan as $pengajuan)
                    <tr>
                        <td>{{ $pengajuan->created_at->format('d M Y, H:i') }}</td>
                        <td>
                            @if($pengajuan->status_validasi == 'PENDING')
                                <span class="badge badge-pending">Pending</span>
                            @elseif($pengajuan->status_validasi == 'TERIMA')
                                <span class="badge badge-terima">Diterima</span>
                            @else
                                <span class="badge badge-tolak">Ditolak</span>
                            @endif
                        </td>
                        <td style="color: #555;">{{ $pengajuan->catatan_validasi ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" style="text-align: center; color: #999; padding: 20px;">
                            Belum ada riwayat pengajuan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</x-mahasiswa-layout>
<x-mahasiswa-layout>
    <x-slot name="title">
        Jadwal Sidang & LSTA
    </x-slot>

    <style>
        .table-wrapper {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table-wrapper th,
        .table-wrapper td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        .table-wrapper th {
            background-color: #0a2e6c;
            color: white;
            font-weight: 700;
        }

        .table-wrapper td.empty {
            text-align: center;
            color: #777;
            font-style: italic;
        }

        .status-message-box {
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            background-color: #f4f7f6;
            border: 2px dashed #ccc;
        }

        .status-message-box .icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        .status-message-box h3 {
            font-size: 1.3rem;
            margin-bottom: 10px;
        }

        .status-message-box p {
            color: #777;
        }

        .status-pending {
            border-color: #f39c12;
            background-color: #fffaf0;
        }

        .status-pending .icon {
            color: #f39c12;
        }

        .status-pending h3 {
            color: #d35400;
        }

        .status-reject {
            border-color: #e74c3c;
            background-color: #fff2f2;
        }

        .status-reject .icon {
            color: #e74c3c;
        }

        .status-reject h3 {
            color: #c0392b;
        }

        /* --- STYLE BARU UNTUK HASIL BA --- */
        .hasil-ba-box {
            padding: 30px;
            border-radius: 8px;
            text-align: center;
            margin-top: 20px;
        }

        .hasil-ba-box .icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        .hasil-ba-box h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .hasil-ba-box p {
            font-size: 1.1rem;
            margin-bottom: 25px;
        }

        .hasil-ba-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            max-width: 400px;
            margin: 0 auto;
            text-align: left;
            padding: 20px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 8px;
        }

        .hasil-ba-label {
            display: block;
            font-size: 0.9rem;
            color: #555;
        }

        .hasil-ba-value {
            font-size: 1.3rem;
            font-weight: 700;
        }

        /* Warna untuk Lulus/Gagal */
        .status-lulus {
            background-color: #f0fff4;
            border: 1px solid #2ecc71;
        }

        .status-lulus .icon {
            color: #2ecc71;
        }

        .status-lulus h3 {
            color: #27ae60;
        }

        .status-lulus .hasil-ba-value {
            color: #27ae60;
        }

        .status-tidak_lulus {
            background-color: #fff2f2;
            border: 1px solid #e74c3c;
        }

        .status-tidak_lulus .icon {
            color: #e74c3c;
        }

        .status-tidak_lulus h3 {
            color: #c0392b;
        }

        .status-tidak_lulus .hasil-ba-value {
            color: #c0392b;
        }

        /* Container untuk tombol action */
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .btn-custom {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 25px;
            font-size: 1rem;
            font-weight: 700;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            gap: 10px;
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-blue {
            background-color: #0a2e6c;
        }

        .btn-blue:hover {
            background-color: #082456;
        }

        .btn-orange {
            background-color: #d35400;
        }

        .btn-orange:hover {
            background-color: #b04600;
        }
    </style>

    <h1 class="content-title">Sidang / LSTA</h1>

    @if ($pengajuanTerbaru && $pengajuanTerbaru->status_validasi == 'TERIMA')

        <h2 class="content-title" style="font-size: 1.5rem; margin-top: 10px;">LSTA</h2>
        <div class="content-box">
            <table class="table-wrapper">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Ruangan</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($lsta)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($lsta->jadwal)->format('d F Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($lsta->jadwal)->format('H:i') }} WIB</td>
                            <td>{{ $lsta->ruangan }}</td>
                        </tr>
                    @else
                        <tr>
                            <td colspan="3" class="empty">
                                Berkas Anda telah disetujui. Harap tunggu Staf PAJ mem-publish jadwal LSTA Anda.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <h2 class="content-title" style="font-size: 1.5rem; margin-top: 30px;">Sidang</h2>
        <div class="content-box">
            @if ($sidang)

                @if ($sidang->status == 'TERJADWAL')

                    <table class="table-wrapper">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Waktu</th>
                                <th>Ruangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($sidang->jadwal)->format('d F Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($sidang->jadwal)->format('H:i') }} WIB</td>
                                <td>{{ $sidang->ruangan }}</td>
                            </tr>
                        </tbody>
                    </table>

                @elseif ($sidang->status == 'LULUS' || $sidang->status == 'TIDAK_LULUS')

                    <div class="hasil-ba-box status-{{ strtolower($sidang->status) }}">

                        @if ($sidang->status == 'LULUS')
                            <i class="fa-solid fa-graduation-cap icon"></i>
                            <h3>Selamat, Anda Dinyatakan LULUS</h3>
                            <p>Berikut adalah rincian Berita Acara Anda:</p>
                        @else
                            <i class="fa-solid fa-circle-xmark icon"></i>
                            <h3>Anda Dinyatakan TIDAK LULUS</h3>
                            <p>Harap hubungi dosen pembimbing untuk sidang ulang. Berikut rincian BA Anda:</p>
                        @endif

                        @if ($sidang->beritaAcara)
                            <div class="hasil-ba-grid">
                                <div>
                                    <span class="hasil-ba-label">Nilai Rata-rata (NMA)</span>
                                    <span class="hasil-ba-value">{{ number_format($sidang->beritaAcara->rata_rata_nma, 2) }}</span>
                                </div>
                                <div>
                                    <span class="hasil-ba-label">Nilai Relatif (NR)</span>
                                    <span class="hasil-ba-value">{{ $sidang->beritaAcara->nilai_relatif_nr }}</span>
                                </div>
                            </div>
                        @else
                            <p style="color: red;">Error: Data Berita Acara belum digenerate oleh sistem.</p>
                        @endif

                        <div class="action-buttons">

                        {{-- [PERBAIKAN] TOMBOL ISI KETERANGAN REVISI (WEB) --}}
                        {{-- Mengarah ke halaman web interaktif Milestone 3 --}}
                        <a href="{{ route('mahasiswa.halaman_revisi', $sidang->id) }}" class="btn-custom btn-orange">
                            <i class="fa-solid fa-pen-to-square"></i>
                            Isi Keterangan Revisi
                        </a>

                        {{-- TOMBOL LIHAT BERITA ACARA --}}
                        <a href="{{ route('dokumen.hasil-sidang', ['sidang' => $sidang->id, 'jenis' => 'berita-acara', 'mode' => 'view']) }}"
                            target="_blank" class="btn-custom btn-blue">
                            <i class="fa-solid fa-certificate"></i>
                            Lihat Berita Acara
                        </a>        
                        </div>

                    </div>

                @endif

            @else
                <table class="table-wrapper">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Ruangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="3" class="empty">
                                Jadwal Sidang belum ditentukan oleh Staf PAJ.
                            </td>
                        </tr>
                    </tbody>
                </table>
            @endif
        </div>

    @elseif ($pengajuanTerbaru && $pengajuanTerbaru->status_validasi == 'PENDING')

        <div class="content-box">
            <div class="status-message-box status-pending">
                <i class="fa-solid fa-hourglass-half icon"></i>
                <h3>Berkas Sedang Diverifikasi</h3>
                <p>Jadwal sidang akan muncul di halaman ini setelah paket berkas Anda disetujui oleh Staf PAJ.</p>
            </div>
        </div>

    @else

        <div class="content-box">
            <div class="status-message-box status-reject">
                <i class="fa-solid fa-circle-xmark icon"></i>
                <h3>Berkas Belum Disetujui / Belum Upload</h3>
                <p>
                    Anda harus mengupload paket berkas sidang dan menunggu persetujuan
                    <br>
                    sebelum dapat melihat jadwal sidang di halaman ini.
                </p>
            </div>
        </div>

    @endif

</x-mahasiswa-layout>
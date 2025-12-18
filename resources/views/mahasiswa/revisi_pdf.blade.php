<!DOCTYPE html>
<html>

<head>
    <title>Lembar Revisi Sidang</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.3;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px double black;
            padding-bottom: 10px;
        }

        .header h3 {
            margin: 0;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .header h2 {
            margin: 5px 0 0 0;
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .header p {
            margin: 0;
            font-size: 11pt;
            font-style: italic;
        }

        .meta-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .meta-table td {
            vertical-align: top;
            padding: 4px 0;
        }

        .label {
            width: 160px;
            font-weight: bold;
        }

        .sep {
            width: 10px;
            text-align: center;
        }

        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .content-table th,
        .content-table td {
            border: 1px solid black;
            padding: 8px;
            vertical-align: top;
            text-align: left;
        }

        .content-table th {
            background-color: #f0f0f0;
            text-align: center;
            font-weight: bold;
        }

        .col-no {
            width: 5%;
            text-align: center;
        }

        .col-dosen {
            width: 30%;
        }

        .col-revisi {
            width: 65%;
        }

        .footer {
            margin-top: 60px;
            width: 100%;
        }

        .ttd-container {
            width: 100%;
            text-align: right;
        }

        .ttd-box {
            display: inline-block;
            text-align: center;
            width: 250px;
        }

        .ttd-line {
            margin-top: 80px;
            border-top: 1px solid black;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>

    <div class="header">
        <h3>Program Studi Informatika</h3>
        <h3>Fakultas Teknik - Universitas Surabaya</h3>
        <h2>Lembar Revisi Sidang Tugas Akhir</h2>
    </div>

    <table class="meta-table">
        <tr>
            <td class="label">Nama Mahasiswa</td>
            <td class="sep">:</td>
            <td>{{ $sidang->tugasAkhir->mahasiswa->nama_lengkap ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">NRP</td>
            <td class="sep">:</td>
            <td>{{ $sidang->tugasAkhir->mahasiswa->nrp ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Judul Tugas Akhir</td>
            <td class="sep">:</td>
            <td style="text-align: justify;">{{ $sidang->tugasAkhir->judul ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Sidang</td>
            <td class="sep">:</td>
            <td>{{ \Carbon\Carbon::parse($sidang->jadwal)->locale('id')->isoFormat('dddd, D MMMM Y') }}</td>
        </tr>
        <tr>
            <td class="label">Periode Sidang</td>
            <td class="sep">:</td>
            <td>{{ $sidang->eventSidang->nama_event ?? '-' }}</td>
        </tr>
    </table>

    <table class="content-table">
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-dosen">Dosen Penguji</th>
                <th class="col-revisi">Komentar / Poin Revisi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sidang->lembarPenilaians as $index => $penilaian)
                <tr>
                    {{-- KOLOM 1: NO --}}
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    
                    {{-- KOLOM 2: NAMA DOSEN (Ini yang tadi hilang di kode Anda) --}}
                    <td>
                        <strong>{{ $penilaian->dosen->nama_lengkap ?? 'Dosen Penguji' }}</strong>
                    </td>

                    {{-- KOLOM 3: REVISI --}}
                    <td>
                        @if($penilaian->komentar_revisi)
                            {!! nl2br(e($penilaian->komentar_revisi)) !!}
                        @else
                            <em style="color: #777;">- Tidak ada catatan revisi -</em>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center; padding: 20px;">
                        <em>Belum ada data penilaian atau revisi untuk sidang ini.</em>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div class="ttd-container">
            <div class="ttd-box">
                <p>Diketahui oleh,<br>Dosen Pembimbing</p>
                <div class="ttd-line"></div>
            </div>
        </div>
    </div>

</body>

</html>
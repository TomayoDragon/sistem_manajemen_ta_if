<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Berita Acara Sidang - {{ $mahasiswa->nrp }}</title>
    <style>
        /* CSS untuk layout dokumen cetak */
        @page {
            margin: 0.75in;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.3;
        }

        h3 {
            text-align: center;
            font-size: 14pt;
            text-decoration: underline;
            margin: 0 0 20px 0;
            padding: 0;
            font-weight: bold;
        }

        /* Tabel Info (Nama, NRP, dll) */
        .table-info {
            width: 100%;
            margin-bottom: 15px;
            margin-left: 20px;
            border-collapse: collapse;
        }

        .table-info td {
            padding: 2px 5px;
            vertical-align: top;
        }

        .table-info td:first-child {
            width: 130px;
        }

        .table-info td:nth-child(2) {
            width: 15px;
        }

        /* Tabel Utama (Judul, Nilai, Tim Penguji) */
        .table-main {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid black;
            margin-top: 10px;
        }

        .table-main th,
        .table-main td {
            border: 1px solid black;
            padding: 5px 8px;
            vertical-align: top;
        }

        /* Baris Judul & Pembimbing */
        .table-main .header-cell {
            width: 25%;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
        }

        .table-main .content-cell {
            width: 75%;
        }

        .table-main .pembimbing-table {
            width: 100%;
            border-collapse: collapse;
        }

        .table-main .pembimbing-table td {
            border: none;
            padding: 2px;
        }

        .table-main .pembimbing-table td:first-child {
            width: 20px;
        }

        /* Baris Nilai */
        .table-nilai {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            margin-top: 5px;
            margin-bottom: 5px;
        }

        .table-nilai th,
        .table-nilai td {
            border: 1px solid black;
            padding: 5px;
        }

        .table-nilai th {
            font-size: 10pt;
            background-color: #f0f0f0;
        }

        .table-nilai td {
            font-size: 11pt;
            font-weight: bold;
            height: 25px;
        }

        /* Tim Penguji */
        .tim-penguji-title {
            margin-top: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .table-penguji {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid black;
        }

        .table-penguji th {
            border: 1px solid black;
            padding: 5px;
            text-align: center;
            background-color: #f0f0f0;
        }

        .table-penguji td {
            border: 1px solid black;
            padding: 5px 8px;
            height: 90px; /* Ruang TTD */
            vertical-align: top;
        }

        .table-penguji .anggota-cell {
            height: auto;
            padding: 5px 8px;
            font-weight: bold;
            background-color: #f0f0f0;
        }

        /* Tanda Tangan Bawah */
        .table-signature {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }

        .table-signature td {
            padding: 5px;
            width: 50%; /* Bagi dua rata */
            vertical-align: top;
        }

        .table-signature td.right-align {
            text-align: right;
        }
    </style>
</head>

<body>

    <h3>BERITA ACARA UJIAN SKRIPSI/TUGAS AKHIR</h3>

    <table class="table-info">
        <tr>
            <td>Nama</td>
            <td>:</td>
            <td>{{ $mahasiswa->nama_lengkap }}</td>
        </tr>
        <tr>
            <td>NRP</td>
            <td>:</td>
            <td>{{ $mahasiswa->nrp }}</td>
        </tr>
        <tr>
            <td>Program Studi</td>
            <td>:</td>
            <td>Teknik Informatika</td>
        </tr>
        <tr>
            <td>Hari, Tanggal</td>
            <td>:</td>
            <td>{{ \Carbon\Carbon::parse($sidang->jadwal)->translatedFormat('l, d F Y') }}</td>
        </tr>
        <tr>
            <td>Waktu</td>
            <td>:</td>
            <td>{{ \Carbon\Carbon::parse($sidang->jadwal)->format('H:i') }} WIB</td>
        </tr>
        <tr>
            <td>Tempat</td>
            <td>:</td>
            <td>{{ $sidang->ruangan }}</td>
        </tr>
    </table>

    <table class="table-main">
        <tr>
            <td class="header-cell">JUDUL SKRIPSI/ TUGAS AKHIR</td>
            <td class="content-cell">{{ $ta->judul }}</td>
        </tr>
        <tr>
            <td class="header-cell">PEMBIMBING SKRIPSI</td>
            <td class="content-cell">
                <table class="pembimbing-table">
                    <tr>
                        <td>1.</td>
                        <td>{{ $ta->dosenPembimbing1->nama_lengkap ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>2.</td>
                        <td>{{ $ta->dosenPembimbing2?->nama_lengkap ?? '-' }}</td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- === PINDAHAN: CATATAN KEJADIAN (DI BAWAH PEMBIMBING) === --}}
        <tr>
            <td class="header-cell">CATATAN KEJADIAN SELAMA UJIAN</td>
            <td class="content-cell">
                <div style="font-style: italic; min-height: 40px;">
                    @if($sidang->catatan_kejadian)
                        {!! nl2br(e($sidang->catatan_kejadian)) !!}
                    @else
                        - Tidak ada catatan khusus -
                    @endif
                </div>
            </td>
        </tr>
        {{-- ========================================================= --}}
        
        <tr>
            <td class="header-cell">NILAI</td>
            <td class="content-cell">
                <table class="table-nilai">
                    <tr>
                        <th>Jumlah Nilai Mentah (NMA)</th>
                        <th>Rata-rata NMA</th>
                        <th>Nilai Relatif (NR)</th>
                    </tr>
                    <tr>
                        <td>{{ number_format($ba->jumlah_nilai_mentah_nma, 2) }}</td>
                        <td>{{ number_format($ba->rata_rata_nma, 2) }}</td>
                        <td>{{ $ba->nilai_relatif_nr }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="header-cell">HASIL UJIAN</td>
            <td class="content-cell" style="font-weight: bold; text-transform: uppercase;">
                {{ str_replace('_', ' ', $ba->hasil_ujian) }}
            </td>
        </tr>
    </table>

    <div class="tim-penguji-title">Tim Penguji:</div>
    <table class="table-penguji">
        <thead>
            <tr>
                <th style="width: 50%;">Ketua</th>
                <th style="width: 50%;">Sekretaris</th>
            </tr>
        </thead>
        <tbody>
            {{-- BARIS 1: Ketua & Sekretaris --}}
            <tr>
                <td>
                    <br><br><br><br>
                    ( {{ $sidang->dosenPengujiKetua->nama_lengkap ?? '.........................' }} )
                </td>
                <td>
                    <br><br><br><br>
                    ( {{ $sidang->dosenPengujiSekretaris->nama_lengkap ?? '.........................' }} )
                </td>
            </tr>
            
            {{-- HEADER ANGGOTA --}}
            <tr>
                <td colspan="2" class="anggota-cell">Anggota</td>
            </tr>

            {{-- BARIS 2: Pembimbing 1 & 2 --}}
            <tr>
                @if($ta->dosenPembimbing2)
                    {{-- KASUS A: 2 Pembimbing --}}
                    <td>
                        <br><br><br><br>
                        ( {{ $ta->dosenPembimbing1->nama_lengkap ?? '.........................' }} )
                    </td>
                    <td>
                        <br><br><br><br>
                        ( {{ $ta->dosenPembimbing2->nama_lengkap }} )
                    </td>
                @else
                    {{-- KASUS B: 1 Pembimbing (Gabung Cell) --}}
                    <td colspan="2" style="text-align: center;">
                        <br><br><br><br>
                        ( {{ $ta->dosenPembimbing1->nama_lengkap ?? '.........................' }} )
                    </td>
                @endif
            </tr>
        </tbody>
    </table>

    <table class="table-signature">
        <tr>
            <td>
                Mahasiswa yang Diuji,
                <br><br><br><br><br>
                <strong>{{ $mahasiswa->nama_lengkap }}</strong>
            </td>
            <td class="right-align">
                Surabaya, {{ \Carbon\Carbon::parse($sidang->jadwal)->translatedFormat('d F Y') }}
                <br>
                Ketua Jurusan Teknik Informatika
                <br><br><br><br>
                <strong>Dr. Joko Siswantoro, S.Si., M.Si.</strong>
            </td>
        </tr>
    </table>

</body>
</html>
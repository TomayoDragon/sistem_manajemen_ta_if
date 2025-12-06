<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara Sidang - {{ $mahasiswa->nrp }}</title>
    <style>
        /* CSS untuk meniru layout dokumen */
        @page {
            margin: 0.75in; /* Margin standar dokumen */
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.4;
        }
        h3 {
            text-align: center;
            font-size: 14pt;
            text-decoration: underline;
            margin: 0;
            padding: 0;
        }
        h4 {
            text-align: center;
            font-size: 12pt;
            margin: 0;
            padding: 0;
            font-weight: normal;
        }
        
        /* Tabel Info (Nama, NRP, dll) */
        .table-info {
            width: 100%;
            margin-top: 20px;
            margin-left: 20px; /* Indentasi seperti contoh */
            border-collapse: collapse;
        }
        .table-info td {
            padding: 1px 5px;
            vertical-align: top;
        }
        .table-info td:first-child { width: 120px; }
        .table-info td:nth-child(2) { width: 10px; }
        
        /* Tabel Utama (Judul, Nilai, Tim Penguji) */
        .table-main {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid black;
            margin-top: 15px;
        }
        .table-main th, .table-main td {
            border: 1px solid black;
            padding: 5px 8px;
            vertical-align: top;
        }
        
        /* Baris Judul & Pembimbing */
        .table-main .header-cell {
            width: 25%;
            font-weight: bold;
            text-align: center;
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
            width: 15px;
        }

        /* Baris Nilai */
        .table-nilai {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
        }
        .table-nilai th, .table-nilai td {
            border: 1px solid black;
            padding: 5px;
        }
        .table-nilai th { font-size: 10pt; }
        .table-nilai td {
            font-size: 11pt;
            font-weight: bold;
            height: 30px;
        }
        
        /* Tim Penguji (Sekarang terpisah & lebih besar) */
        .tim-penguji-title {
            margin-top: 15px; /* Jarak dari tabel utama */
            font-weight: bold;
        }
        .table-penguji {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid black;
            margin-top: 5px; /* Jarak dari teks "Tim Penguji:" */
        }
        .table-penguji th {
            border: 1px solid black;
            padding: 5px;
            text-align: center;
        }
        .table-penguji td {
            border: 1px solid black;
            padding: 5px 8px;
            height: 100px; /* Ruang untuk TTD */
            vertical-align: top; /* Teks nama rata atas */
        }
        .table-penguji .anggota-cell {
            height: auto; /* Biarkan tinggi menyesuaikan konten */
            padding: 5px 8px;
            font-weight: bold;
        }

        /* Tanda Tangan Bawah */
        .table-signature {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        .table-signature td {
            padding: 5px;
            width: 33.33%;
            vertical-align: top;
            height: 80px; /* <-- DIUBAH: Lebih kecil dari 120px */
        }
        .table-signature td.right-align {
            text-align: right;
        }
    </style>
</head>
<body>
    
    <h3>BERITA ACARA UJIAN SKRIPSI/TUGAS AKHIR</h3>

    <table class="table-info">
        <tr> <td>Nama</td> <td>:</td> <td>{{ $mahasiswa->nama_lengkap }}</td> </tr>
        <tr> <td>NRP</td> <td>:</td> <td>{{ $mahasiswa->nrp }}</td> </tr>
        <tr> <td>Program Studi</td> <td>:</td> <td>Teknik Informatika</td> </tr>
        <tr> <td>Hari, Tanggal</td> <td>:</td> <td>{{ \Carbon\Carbon::parse($sidang->jadwal)->translatedFormat('l, d F Y') }}</td> </tr>
        <tr> <td>Waktu</td> <td>:</td> <td>{{ \Carbon\Carbon::parse($sidang->jadwal)->format('H:i') }} WIB</td> </tr>
        <tr> <td>Tempat</td> <td>:</td> <td>{{ $sidang->ruangan }}</td> </tr>
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
                    <tr> <td>1.</td> <td>{{ $ta->dosenPembimbing1->nama_lengkap }}</td> </tr>
                    <tr> <td>2.</td> <td>{{ $ta->dosenPembimbing2->nama_lengkap }}</td> </tr>
                </table>
            </td>
        </tr>
        {{-- BARIS "KEJADIAN-KEJADIAN SELAMA UJIAN" DIHAPUS --}}
        <tr>
            <td class="header-cell">NILAI</td>
            <td>
                <table class="table-nilai">
                    <tr>
                        <th>Jumlah Nilai Mentah (NMA)</th>
                        <th>Re- Rata NMA</th>
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
            <td class="content-cell" style="font-weight: bold;">{{ $ba->hasil_ujian }}</td>
        </tr>
    </table>

    {{-- TABEL KATEGORI NILAI DIHAPUS --}}
    
    <div class="tim-penguji-title">Tim Penguji:</div>
    <table class="table-penguji">
        <tr>
            <th>Ketua,</th>
            <th>Sekretaris,</th>
        </tr>
        <tr>
            <td>{{ $sidang->dosenPengujiKetua->nama_lengkap ?? 'N/A' }}</td>
            <td>{{ $sidang->dosenPengujiSekretaris->nama_lengkap ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td colspan="2" class="anggota-cell">Anggota,</td>
        </tr>
        <tr>
            <td>{{ $ta->dosenPembimbing1->nama_lengkap ?? 'N/A' }}</td>
            <td>{{ $ta->dosenPembimbing2->nama_lengkap ?? 'N/A' }}</td>
        </tr>
    </table>

    <table class="table-signature">
        <tr>
            <td>Mahasiswa yang Diuji,</td>
            <td class="right-align" colspan="2">Surabaya, {{ \Carbon\Carbon::parse($sidang->jadwal)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td style="vertical-align: bottom;">{{ $mahasiswa->nama_lengkap }}</td>
            <td class="right-align" colspan="2" style="vertical-align: bottom;">
Dr. Joko Siswantoro, S.Si., M.Si.</td>
        </tr>
    </table>

</body>
</html>
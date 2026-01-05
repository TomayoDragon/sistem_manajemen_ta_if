<!DOCTYPE html>
<html>
<head>
    <title>Form Usulan Perbaikan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.3;
        }
        /* KUNCI: Class untuk memutus halaman */
        .page-break {
            page-break-after: always;
        }
        
        .container {
            width: 100%;
            padding: 10px;
        }

        /* --- PERUBAHAN 1: HAPUS GARIS BAWAH --- */
        h3 {
            text-align: center;
            /* text-decoration: underline;  <-- DIHAPUS */
            font-weight: bold;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        /* Table Header (Info Mhs) */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .header-table td {
            padding: 5px;
            vertical-align: top;
        }
        .label-col { width: 25%; }
        .sep-col { width: 2%; }

        /* Table Content (Isi Revisi) */
        .content-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid black;
            margin-bottom: 20px;
        }
        .content-table th, .content-table td {
            border: 1px solid black;
            padding: 8px;
        }
        .content-table th {
            background-color: #f0f0f0;
            text-align: center;
            font-weight: bold;
        }

        /* --- PERUBAHAN 2: LAYOUT TANDA TANGAN BERSEBELAHAN --- */
        .signature-section {
            width: 100%;
            margin-top: 30px;
            display: table; /* Gunakan table layout untuk PDF */
            border-collapse: collapse;
        }

        .signature-box {
            display: table-cell;
            width: 50%; /* Bagi dua kolom sama rata */
            vertical-align: top;
            padding: 10px;
        }

        /* Kotak Kiri (Dosen) */
        .left-box {
            text-align: center;
        }

        /* Kotak Kanan (Mahasiswa) */
        .right-box {
            border: 1px solid black; /* Tambahkan border sesuai contoh */
            text-align: left;
            padding: 15px;
            font-size: 10pt;
        }

        .ttd-space {
            height: 80px; /* Ruang untuk tanda tangan */
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
    </style>
</head>
<body>

    {{-- LOOPING UTAMA: Mencetak Halaman per Dosen --}}
    @foreach($daftarPenguji as $penguji)
    
    <div class="container">
        <h3>Form Usulan Perbaikan</h3>

        <table class="header-table">
            <tr>
                <td class="label-col">Nama Pemb/Penguji</td>
                <td class="sep-col">:</td>
                <td><strong>{{ $penguji['nama_dosen'] }}</strong></td>
            </tr>
            <tr>
                <td>Nama</td>
                <td>:</td>
                <td>{{ $mahasiswa->nama_lengkap }} ({{ $mahasiswa->nrp }})</td>
            </tr>
            <tr>
                <td>Program</td>
                <td>:</td>
                <td>Teknik Informatika</td>
            </tr>
            <tr>
                <td>Judul Skripsi</td>
                <td>:</td>
                <td>{{ $judul }}</td>
            </tr>
            <tr>
                <td>Tanggal Ujian</td>
                <td>:</td>
                <td>{{ \Carbon\Carbon::parse($tanggal_sidang)->translatedFormat('d F Y') }}</td>
            </tr>
        </table>

        <h4>Usulan Perbaikan</h4>
        <table class="content-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th>Uraian</th>
                </tr>
            </thead>
            <tbody>
                {{-- Loop Catatan Revisi --}}
                @forelse($penguji['revisi'] as $index => $poin)
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td>{{ $poin }}</td>
                    </tr>
                @empty
                    {{-- Jika Kosong, Tampilkan 5 Baris Kosong --}}
                    @for($i=1; $i<=5; $i++)
                    <tr>
                        <td style="text-align: center;">{{ $i }}</td>
                        <td style="height: 25px;"></td>
                    </tr>
                    @endfor
                @endforelse
            </tbody>
        </table>

        {{-- --- BAGIAN TANDA TANGAN BARU (BERSEBELAHAN) --- --}}
        <div class="signature-section">
            
            <div class="signature-box left-box">
                <p>Tanda tangan penguji/pembimbing</p>
                
                <div class="ttd-space">
                    @if(isset($qr_code)) 
                        <small style="color:gray;">[Digitally Signed]</small>
                    @else
                        <br><br><br>
                    @endif
                </div>

                <p>( <strong>{{ $penguji['nama_dosen'] }}</strong> )</p>
            </div>

            <div class="signature-box right-box">
                <p>
                    Tanda tangan di sini setelah mahasiswa menunjukkan hasil revisi (perbaikan yang dilakukan mahasiswa telah memenuhi usulan/harapan saya).
                </p>
                <br>
                <p>Tanda tangan:</p>

                <div class="ttd-space">
                    </div>

                <p>( ....................................................... )</p>
            </div>

        </div>
        {{-- --- AKHIR BAGIAN TANDA TANGAN BARU --- --}}

    </div>

    {{-- PAGE BREAK: Jangan break di halaman terakhir --}}
    @if(!$loop->last)
        <div class="page-break"></div>
    @endif

    @endforeach

</body>
</html>
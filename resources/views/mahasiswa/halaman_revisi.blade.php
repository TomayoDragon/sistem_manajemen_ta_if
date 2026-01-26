<x-mahasiswa-layout>
    <x-slot name="title">
        Form Perbaikan / Revisi Sidang
    </x-slot>

    <style>
        /* Container Konten Dashboard */
        .content-box {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid #e0e0e0;
        }

        /* Header Dokumen Formal */
        .doc-header {
            text-align: center;
            border-bottom: 2px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .doc-title {
            font-size: 1.3rem;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 5px;
            color: #0a2e6c;
            letter-spacing: 0.5px;
        }

        .doc-subtitle {
            font-size: 1rem;
            font-weight: 600;
            color: #555;
        }

        /* Tabel Informasi Mahasiswa */
        .info-table {
            width: 100%;
            margin-bottom: 30px;
            font-size: 0.95rem;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 8px 0;
            vertical-align: top;
            border-bottom: 1px dashed #eee;
        }

        .label-col { width: 180px; font-weight: 600; color: #555; }
        .sep-col { width: 20px; text-align: center; font-weight: 600; }
        .val-col { font-weight: 700; color: #333; }

        /* Section Per Dosen */
        .dosen-section {
            margin-top: 30px;
            margin-bottom: 25px;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden; /* Biar border radius ngaruh ke header */
        }

        .dosen-header-title {
            background-color: #f4f6f9;
            padding: 12px 15px;
            font-weight: 700;
            font-size: 1rem;
            color: #0a2e6c;
            border-bottom: 1px solid #ddd;
            display: flex;
            align-items: center;
        }

        /* Tabel Revisi Utama */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }

        .main-table th {
            padding: 12px;
            background-color: #0a2e6c;
            color: #fff;
            font-weight: 600;
            text-align: center;
            font-size: 0.9rem;
        }

        .main-table td {
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }

        /* Styling Input Area */
        .input-cell {
            padding: 0;
            margin: 0;
            background-color: #fff;
        }

        .input-keterangan {
            width: 100%;
            min-height: 100px;
            border: none;
            padding: 15px;
            resize: vertical;
            font-family: inherit;
            font-size: 0.95rem;
            background-color: #fff;
            display: block;
            transition: background-color 0.2s;
        }
        
        .input-keterangan:focus {
            background-color: #fffde7; /* Highlight kuning tipis saat fokus */
            outline: none;
            box-shadow: inset 0 0 5px rgba(0,0,0,0.05);
        }

        /* Tombol Aksi Melayang */
        .sticky-actions {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 999;
            display: flex;
            gap: 15px;
            background: rgba(255, 255, 255, 0.95);
            padding: 15px 20px;
            border-radius: 50px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            border: 1px solid #e0e0e0;
            backdrop-filter: blur(5px);
        }

        .btn-save {
            background-color: #0a2e6c; 
            color: white; 
            padding: 10px 20px; 
            border-radius: 25px; 
            font-weight: 600; 
            border: none; 
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-save:hover { background-color: #082456; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(10, 46, 108, 0.3); }

        .btn-pdf {
            background-color: #d32f2f; 
            color: white; 
            padding: 10px 20px; 
            border-radius: 25px; 
            font-weight: 600; 
            text-decoration: none; 
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
            transition: all 0.2s;
        }
        .btn-pdf:hover { background-color: #b71c1c; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(211, 47, 47, 0.3); }

        .readonly-cell {
            padding: 15px;
            background-color: #fcfcfc;
            color: #333;
            line-height: 1.6;
            border-right: 1px solid #eee;
        }

        .empty-revisi {
            text-align: center;
            padding: 30px;
            color: #777;
            font-style: italic;
            background-color: #fff;
        }

        .content-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
        }

    </style>

    <h1 class="content-title">Form Perbaikan / Revisi Sidang</h1>
    
    <form action="{{ route('mahasiswa.update', $sidang->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="content-box">
            
            {{-- === KOP / HEADER FORMULIR === --}}
            <div class="doc-header">
                <div class="doc-title">LEMBAR PERBAIKAN / REVISI SIDANG TUGAS AKHIR</div>
                <div class="doc-subtitle">Teknik Informatika - Universitas Surabaya</div>
            </div>

            {{-- === INFORMASI MAHASISWA === --}}
            <table class="info-table">
                <tr>
                    <td class="label-col">Nama Mahasiswa</td>
                    <td class="sep-col">:</td>
                    <td class="val-col">{{ Auth::user()->mahasiswa->nama_lengkap }}</td>
                </tr>
                <tr>
                    <td class="label-col">NRP</td>
                    <td class="sep-col">:</td>
                    <td class="val-col">{{ Auth::user()->mahasiswa->nrp }}</td>
                </tr>
                <tr>
                    <td class="label-col">Judul Tugas Akhir</td>
                    <td class="sep-col">:</td>
                    <td class="val-col">{{ $sidang->tugasAkhir->judul }}</td>
                </tr>
                <tr>
                    <td class="label-col">Tanggal Sidang</td>
                    <td class="sep-col">:</td>
                    <td class="val-col">{{ \Carbon\Carbon::parse($sidang->jadwal)->format('d F Y') }}</td>
                </tr>
            </table>

            @if(session('success'))
                <div style="background-color: #d1fae5; border-left: 5px solid #10b981; color: #065f46; padding: 15px; margin-bottom: 25px; border-radius: 4px;">
                    <strong>Berhasil!</strong> {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div style="background-color: #fee2e2; border-left: 5px solid #ef4444; color: #b91c1c; padding: 15px; margin-bottom: 25px; border-radius: 4px;">
                    <strong>Error!</strong> {{ session('error') }}
                </div>
            @endif

            <h3 style="font-weight: 700; font-size: 1.2rem; margin-top: 10px; margin-bottom: 15px; color: #333; border-left: 5px solid #0a2e6c; padding-left: 10px;">
                Daftar Usulan Perbaikan
            </h3>

            @php 
                $totalRevisi = 0;
                foreach($sidang->lembarPenilaians as $p) {
                    $totalRevisi += $p->detailRevisis->count();
                }
            @endphp

            {{-- === LOOP PER DOSEN (TABEL TERPISAH) === --}}
            @forelse($sidang->lembarPenilaians as $penilaian)
                <div class="dosen-section">
                    <div class="dosen-header-title">
                        <i class="fa-solid fa-user-tie" style="margin-right: 8px;"></i>
                        Penguji: {{ $penilaian->dosen->nama_lengkap }}
                    </div>

                    @if($penilaian->detailRevisis->count() > 0)
                        <table class="main-table">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">No</th>
                                    <th style="width: 45%;">Uraian Revisi (Dosen)</th>
                                    <th style="width: 50%;">Keterangan / Tindak Lanjut (Mahasiswa)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($penilaian->detailRevisis as $index => $detail)
                                    <tr>
                                        <td style="text-align: center; vertical-align: middle; background-color: #f9f9f9; color: #777;">{{ $index + 1 }}</td>
                                        
                                        {{-- Kolom Uraian (Read Only) --}}
                                        <td class="readonly-cell">
                                            {{ $detail->isi_revisi }}
                                        </td>

                                        {{-- Kolom Keterangan (Input Area) --}}
                                        <td class="input-cell">
                                            <textarea 
                                                name="keterangan[{{ $detail->id }}]" 
                                                class="input-keterangan" 
                                                placeholder="Tuliskan tindak lanjut atau perbaikan yang telah Anda lakukan..."
                                            >{{ old('keterangan.'.$detail->id, $detail->keterangan_mahasiswa) }}</textarea>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="empty-revisi">
                            <i class="fa-solid fa-check-circle" style="color: #2ecc71; margin-right: 5px;"></i> 
                            Tidak ada catatan revisi dari dosen ini.
                        </div>
                    @endif
                </div>
            @empty
                <div class="empty-revisi" style="border: 1px dashed #ccc; border-radius: 8px;">
                    Belum ada data penilaian/revisi yang masuk dari dosen penguji.
                </div>
            @endforelse

            <div style="margin-top: 30px; font-size: 0.9rem; color: #666; background-color: #f8f9fa; padding: 15px; border-radius: 6px; border-left: 4px solid #0a2e6c;">
                <strong>Panduan Pengisian:</strong>
                <ul style="list-style-type: disc; margin-left: 20px; margin-top: 5px;">
                    <li>Silakan isi kolom <strong>"Keterangan / Tindak Lanjut"</strong> sesuai dengan perbaikan yang telah Anda lakukan pada setiap poin revisi dosen.</li>
                    <li>Jangan lupa menekan tombol <strong>Simpan Keterangan</strong> di pojok kanan bawah setelah mengisi.</li>
                    <li>Setelah disetujui/selesai, Anda dapat mengunduh dokumen ini dalam format PDF melalui tombol <strong>Download PDF</strong>.</li>
                </ul>
            </div>

        </div>

        {{-- === TOMBOL AKSI MELAYANG (FLOATING) === --}}
        <div class="sticky-actions">
            {{-- Tombol Download PDF --}}
            <a href="{{ route('dokumen.hasil-sidang', ['sidang' => $sidang->id, 'jenis' => 'revisi', 'mode' => 'view', 't' => time()]) }}" class="btn-pdf" target="_blank">
                <i class="fa-solid fa-file-pdf"></i> Download PDF
            </a>

            {{-- Tombol Simpan (Hanya muncul jika ada revisi) --}}
            @if($totalRevisi > 0)
                <button type="submit" class="btn-save">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Keterangan
                </button>
            @endif
        </div>

    </form>
</x-mahasiswa-layout>
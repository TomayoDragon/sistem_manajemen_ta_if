<x-dosen-layout>
    <x-slot name="title">
        Form Penilaian {{ Str::upper($type) }}
    </x-slot>

    <style>
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
        }

        .detail-box {
            background-color: #fff;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            padding: 25px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .detail-box h3 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .info-item {
            margin-bottom: 15px;
        }

        .info-label {
            display: block;
            font-size: 0.9rem;
            color: #777;
            margin-bottom: 4px;
        }

        .info-value {
            font-weight: 700;
            color: #333;
            font-size: 1.1rem;
        }

        /* Style Tabel Penilaian */
        .grading-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .grading-table th,
        .grading-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
            vertical-align: middle;
        }

        .grading-table th {
            background-color: #f8f9fa;
            font-weight: 700;
            color: #333;
        }

        .grading-table td.text-left {
            text-align: left;
        }

        /* Input Nilai */
        .input-score {
            width: 80px;
            padding: 8px;
            text-align: center;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-weight: bold;
        }

        /* Hasil Kalkulasi */
        .calc-result {
            font-weight: bold;
            color: #0a2e6c;
        }

        .total-row {
            background-color: #eef5ff;
            font-weight: 800;
            font-size: 1.1rem;
        }

        .btn-submit {
            padding: 12px 25px;
            font-size: 1rem;
            font-weight: 700;
            color: #fff;
            background-color: #0a2e6c;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            float: right;
        }

        .file-list a {
            display: block;
            padding: 10px;
            background: #f4f7f6;
            margin-bottom: 5px;
            border-radius: 5px;
            text-decoration: none;
            color: #333;
            font-weight: 600;
        }

        .file-list a:hover {
            background: #e2e6ea;
        }

        /* Style Revisi Dinamis */
        #revisi-wrapper {
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .revisi-item {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
            align-items: flex-start;
        }

        .poin-nomor {
            padding-top: 8px;
            font-weight: bold;
            width: 25px;
            text-align: right;
        }

        .btn-remove-revisi {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            height: 38px;
        }

        .btn-remove-revisi:hover {
            background: #c82333;
        }

        #btn-add-revisi {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 5px;
            font-size: 0.9rem;
        }

        #btn-add-revisi:hover {
            background: #218838;
        }
    </style>

    <h1 class="content-title">Form Penilaian {{ Str::upper($type) }}</h1>

    <div class="detail-grid">
        <div class="detail-box">
            <h3>Informasi Mahasiswa</h3>
            <div class="info-item"> <span class="info-label">Mahasiswa:</span> <span
                    class="info-value">{{ $event->tugasAkhir->mahasiswa->nama_lengkap }}</span> </div>
            <div class="info-item"> <span class="info-label">NRP:</span> <span
                    class="info-value">{{ $event->tugasAkhir->mahasiswa->nrp }}</span> </div>
            <div class="info-item"> <span class="info-label">Judul TA:</span> <span
                    class="info-value">{{ $event->tugasAkhir->judul }}</span> </div>
            <div class="info-item"> <span class="info-label">Jadwal:</span> <span
                    class="info-value">{{ \Carbon\Carbon::parse($event->jadwal)->format('d M Y, H:i') }}</span> </div>
            <div class="info-item"> <span class="info-label">Ruangan:</span> <span
                    class="info-value">{{ $event->ruangan }}</span> </div>

            @if ($event->pengajuanSidang && $event->pengajuanSidang->dokumen->isNotEmpty())
                <h4 style="margin-top:20px; margin-bottom:10px;">Berkas Mahasiswa</h4>
                <div class="file-list">
                    @foreach ($event->pengajuanSidang->dokumen as $dokumen)
                        <a href="{{ route('dokumen.download', ['dokumen' => $dokumen->id, 'mode' => 'view']) }}"
                            target="_blank">
                            <i class="fa-solid fa-eye"></i> {{ $dokumen->tipe_dokumen }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="detail-box">
            <h3>Lembar Penilaian</h3>

            <form action="{{ route('dosen.penilaian.store', ['type' => $type, 'id' => $event->id]) }}" method="POST">
                @csrf

                @if ($errors->any())
                    <div style="color: red; margin-bottom: 15px; background: #ffebeb; padding: 10px; border-radius: 8px;">
                        <ul>@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
                    </div>
                @endif

                <table class="grading-table">
                    <thead>
                        <tr>
                            <th style="width: 40%;">Komponen Penilaian</th>
                            <th style="width: 20%;">Bobot (%)</th>
                            <th style="width: 20%;">Input Nilai (0-100)</th>
                            <th style="width: 20%;">Nilai x Bobot</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-left">Materi Tugas Akhir</td>
                            <td>15%</td>
                            <td>
                                <input type="number" name="nilai_materi" id="in_materi" class="input-score" min="0"
                                    max="100" value="{{ old('nilai_materi', $existingScore->nilai_materi ?? 0) }}"
                                    required>
                            </td>
                            <td><span id="out_materi" class="calc-result">0</span></td>
                        </tr>
                        <tr>
                            <td class="text-left">Sistematika & Bahasa</td>
                            <td>10%</td>
                            <td>
                                <input type="number" name="nilai_sistematika" id="in_sistematika" class="input-score"
                                    min="0" max="100"
                                    value="{{ old('nilai_sistematika', $existingScore->nilai_sistematika ?? 0) }}"
                                    required>
                            </td>
                            <td><span id="out_sistematika" class="calc-result">0</span></td>
                        </tr>
                        <tr>
                            <td class="text-left">Mempertahankan TA</td>
                            <td>50%</td>
                            <td>
                                <input type="number" name="nilai_mempertahankan" id="in_mempertahankan"
                                    class="input-score" min="0" max="100"
                                    value="{{ old('nilai_mempertahankan', $existingScore->nilai_mempertahankan ?? 0) }}"
                                    required>
                            </td>
                            <td><span id="out_mempertahankan" class="calc-result">0</span></td>
                        </tr>
                        <tr>
                            <td class="text-left">Pengetahuan Bidang Studi</td>
                            <td>15%</td>
                            <td>
                                <input type="number" name="nilai_pengetahuan_bidang" id="in_pengetahuan"
                                    class="input-score" min="0" max="100"
                                    value="{{ old('nilai_pengetahuan_bidang', $existingScore->nilai_pengetahuan_bidang ?? 0) }}"
                                    required>
                            </td>
                            <td><span id="out_pengetahuan" class="calc-result">0</span></td>
                        </tr>
                        <tr>
                            <td class="text-left">Karya Ilmiah</td>
                            <td>10%</td>
                            <td>
                                <input type="number" name="nilai_karya_ilmiah" id="in_karya" class="input-score" min="0"
                                    max="100"
                                    value="{{ old('nilai_karya_ilmiah', $existingScore->nilai_karya_ilmiah ?? 0) }}"
                                    required>
                            </td>
                            <td><span id="out_karya" class="calc-result">0</span></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="3" style="text-align: right; padding-right: 20px;">TOTAL NILAI AKHIR:</td>
                            <td><span id="grand_total">0</span></td>
                        </tr>
                    </tfoot>
                </table>

                <div class="form-group" style="margin-top: 25px; border-top: 1px solid #eee; padding-top: 15px;">
                    <label style="font-weight: bold; font-size: 1.1rem; display: block; margin-bottom: 10px;">
                        <i class="fa-solid fa-list-check"></i> Poin-poin Revisi (Uraian)
                    </label>
                    <div id="revisi-wrapper">
                        @if($existingScore && $existingScore->detailRevisis && $existingScore->detailRevisis->count() > 0)
                            {{-- JIKA EDIT: Tampilkan data yang sudah ada --}}
                            @foreach($existingScore->detailRevisis as $index => $detail)
                                <div class="revisi-item">
                                    <span class="poin-nomor">{{ $index + 1 }}.</span>
                                    <input type="text" name="revisi[]" class="form-control" value="{{ $detail->isi_revisi }}"
                                        placeholder="Tulis uraian revisi..."
                                        style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" required>
                                    <button type="button" class="btn-remove-revisi" title="Hapus Poin">&times;</button>
                                </div>
                            @endforeach
                        @else
                            {{-- JIKA BARU: Tampilkan satu input kosong default --}}
                            <div class="revisi-item">
                                <span class="poin-nomor">1.</span>
                                <input type="text" name="revisi[]" class="form-control" placeholder="Tulis uraian revisi..."
                                    style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                                <button type="button" class="btn-remove-revisi" title="Hapus Poin">&times;</button>
                            </div>
                        @endif
                    </div>

                    <button type="button" id="btn-add-revisi">
                        <i class="fa fa-plus"></i> Tambah Poin Revisi
                    </button>
                    <small style="display: block; color: #777; margin-top: 5px;">* Klik tombol tambah untuk menambah
                        baris revisi baru.</small>
                </div>

                <div style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                    @if($type === 'sidang' && $event->dosen_penguji_sekretaris_id == Auth::user()->dosen_id)
                        <div class="form-group"
                            style="margin-top: 30px; border-top: 2px dashed #ccc; padding-top: 20px; background-color: #fff8e1; padding: 20px; border-radius: 8px;">
                            <label
                                style="font-weight: bold; font-size: 1.1rem; display: block; margin-bottom: 10px; color: #d35400;">
                                <i class="fa-solid fa-triangle-exclamation"></i> Kejadian-kejadian Selama Ujian (Khusus
                                Sekretaris)
                            </label>
                            <p style="font-size: 0.9rem; color: #666; margin-bottom: 10px;">
                                Silakan catat jika ada kejadian khusus, kendala teknis, perubahan waktu, atau catatan
                                penting lainnya selama proses sidang berlangsung. Jika tidak ada, biarkan kosong.
                            </p>
                            {{-- Mengambil data dari $event (model Sidang), bukan $existingScore --}}
                            <textarea name="catatan_kejadian" class="form-textarea" rows="5"
                                placeholder="Contoh: Sidang sempat tertunda 15 menit karena kendala proyektor...">{{ old('catatan_kejadian', $event->catatan_kejadian) }}</textarea>
                        </div>
                    @endif
                    <button type="submit" class="btn-submit">
                        <i class="fa-solid fa-save"></i> Simpan Penilaian & Revisi
                    </button>
                    <div style="clear: both;"></div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // === BAGIAN 1: KALKULASI NILAI ===
            const ids = ['in_materi', 'in_sistematika', 'in_mempertahankan', 'in_pengetahuan', 'in_karya'];
            const bobots = [0.15, 0.10, 0.50, 0.15, 0.10];
            const outIds = ['out_materi', 'out_sistematika', 'out_mempertahankan', 'out_pengetahuan', 'out_karya'];

            function calculateAndValidate() {
                let grandTotal = 0;

                ids.forEach((id, index) => {
                    let input = document.getElementById(id);
                    let val = parseFloat(input.value);

                    if (val > 100) {
                        val = 100;
                        input.value = 100;
                    } else if (val < 0) {
                        val = 0;
                        input.value = 0;
                    } else if (isNaN(val)) {
                        val = 0;
                    }

                    let subTotal = val * bobots[index];
                    document.getElementById(outIds[index]).innerText = subTotal.toFixed(2);
                    grandTotal += subTotal;
                });

                document.getElementById('grand_total').innerText = grandTotal.toFixed(2);
            }

            ids.forEach(id => {
                document.getElementById(id).addEventListener('input', calculateAndValidate);
            });
            calculateAndValidate(); // Run once on load

            // === BAGIAN 2: DINAMIS REVISI ===
            const wrapper = document.getElementById('revisi-wrapper');
            const btnAdd = document.getElementById('btn-add-revisi');

            function updateNumbers() {
                const items = wrapper.querySelectorAll('.revisi-item');
                items.forEach((item, index) => {
                    item.querySelector('.poin-nomor').textContent = (index + 1) + '.';
                });
            }

            // Tambah Baris
            btnAdd.addEventListener('click', function () {
                const newItem = document.createElement('div');
                newItem.className = 'revisi-item';
                newItem.innerHTML = `
                    <span class="poin-nomor"></span>
                    <input type="text" name="revisi[]" class="form-control" placeholder="Tulis uraian revisi..." style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" required>
                    <button type="button" class="btn-remove-revisi" title="Hapus Poin">&times;</button>
                `;
                wrapper.appendChild(newItem);
                updateNumbers();

                // Focus ke input baru
                newItem.querySelector('input').focus();
            });

            // Hapus Baris (Delegasi Event)
            wrapper.addEventListener('click', function (e) {
                if (e.target.classList.contains('btn-remove-revisi')) {
                    const inputVal = e.target.previousElementSibling.value;

                    // Konfirmasi jika ada isinya biar gak kepencet
                    if (inputVal.trim() !== '' && !confirm('Hapus poin revisi ini?')) {
                        return;
                    }

                    // Jangan hapus jika sisa 1 baris, cukup kosongkan saja
                    if (wrapper.querySelectorAll('.revisi-item').length > 1) {
                        e.target.parentElement.remove();
                        updateNumbers();
                    } else {
                        e.target.previousElementSibling.value = ''; // Reset input pertama
                        e.target.previousElementSibling.focus();
                    }
                }
            });

            updateNumbers(); // Run once on load
        });
    </script>
</x-dosen-layout>
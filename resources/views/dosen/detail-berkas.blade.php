<x-dosen-layout>
    <x-slot name="title">
        Detail Bimbingan & Berkas
    </x-slot>

    {{-- HEADER & TOMBOL KEMBALI --}}
    <div class="mb-6 flex justify-between items-center">
        <a href="{{ route('dosen.bimbingan.index') }}" 
           class="inline-flex items-center text-gray-600 hover:text-gray-900 transition">
            <i class="fa-solid fa-arrow-left mr-2"></i> Kembali ke Daftar
        </a>
    </div>

    {{-- ALERT SUKSES --}}
    @if (session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-r shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fa-solid fa-circle-check text-green-500"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- 1. KARTU INFORMASI MAHASISWA --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8 relative overflow-hidden">
        {{-- Hiasan Background --}}
        <div class="absolute top-0 right-0 p-4 opacity-5">
            <i class="fa-solid fa-user-graduate text-8xl text-blue-900"></i>
        </div>
        
        <h2 class="text-xl md:text-2xl font-bold text-gray-800 mb-3 pr-10">{{ $tugasAkhir->judul_ta }}</h2>
        
        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
            {{-- Nama --}}
            <div>
                <p class="text-gray-500 mb-1">Nama Mahasiswa</p>
                <div class="font-bold text-lg text-gray-900 flex items-center">
                    <div class="h-8 w-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mr-2 text-xs">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    {{ $tugasAkhir->mahasiswa->nama }}
                </div>
            </div>
            {{-- NRP --}}
            <div>
                <p class="text-gray-500 mb-1">NRP</p>
                <p class="font-bold text-lg text-gray-900">{{ $tugasAkhir->mahasiswa->nrp }}</p>
            </div>
            {{-- Status Validasi --}}
            <div>
                <p class="text-gray-500 mb-1">Status Validasi Berkas (Staff)</p>
                @if($pengajuan)
                    @if($pengajuan->status_validasi == 'TERIMA')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">
                            <i class="fa-solid fa-check mr-1"></i> DITERIMA
                        </span>
                    @elseif($pengajuan->status_validasi == 'TOLAK')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">
                            <i class="fa-solid fa-xmark mr-1"></i> DITOLAK
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700">
                            <i class="fa-solid fa-hourglass-half mr-1"></i> MENUNGGU VERIFIKASI
                        </span>
                    @endif
                @else
                    <span class="text-gray-400 font-medium">- Belum Upload -</span>
                @endif
            </div>
        </div>
    </div>

    {{-- 2. LOGIKA TAMPILAN BERKAS --}}
    
    {{-- SKENARIO A: MAHASISWA BELUM UPLOAD --}}
    @if(!$pengajuan)
        <div class="bg-gray-50 rounded-xl border-2 border-dashed border-gray-300 p-10 text-center">
            <i class="fa-solid fa-cloud-arrow-up text-4xl text-gray-400 mb-3"></i>
            <p class="text-gray-500 font-medium">Mahasiswa belum mengupload paket berkas sidang.</p>
        </div>

    {{-- SKENARIO B: SEDANG DIVERIFIKASI STAFF (PENDING) --}}
    @elseif($pengajuan->status_validasi == 'PENDING')
        <div class="bg-yellow-50 rounded-xl border border-yellow-200 p-8 text-center">
            <i class="fa-solid fa-clipboard-check text-4xl text-yellow-500 mb-3"></i>
            <h3 class="text-lg font-bold text-yellow-800">Sedang Diverifikasi Staff</h3>
            <p class="text-yellow-700 mt-1 text-sm">
                Berkas mahasiswa sedang diperiksa kelengkapannya oleh Tata Usaha. <br>
                Anda dapat melihat dan menyetujui berkas setelah status berubah menjadi <strong>DITERIMA</strong>.
            </p>
        </div>

    {{-- SKENARIO C: DITOLAK STAFF --}}
    @elseif($pengajuan->status_validasi == 'TOLAK')
        <div class="bg-red-50 rounded-xl border border-red-200 p-8">
            <div class="flex items-start">
                <i class="fa-solid fa-triangle-exclamation text-3xl text-red-500 mr-4"></i>
                <div>
                    <h3 class="text-lg font-bold text-red-800">Berkas Ditolak Staff</h3>
                    <p class="text-red-700 mt-1 text-sm mb-3">
                        Terdapat kekurangan pada berkas mahasiswa. Mahasiswa harus memperbaiki dan upload ulang.
                    </p>
                    <div class="bg-white p-3 rounded border border-red-100 text-sm text-red-600 font-mono">
                        <strong>Catatan:</strong> {{ $pengajuan->catatan_validasi ?? 'Tidak ada catatan.' }}
                    </div>
                </div>
            </div>
        </div>

    {{-- SKENARIO D: DITERIMA (TAMPILKAN TABEL & LINK) --}}
    @elseif($pengajuan->status_validasi == 'TERIMA')
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <h3 class="font-bold text-gray-800">
                    <i class="fa-solid fa-folder-open text-blue-600 mr-2"></i> Daftar Berkas Tervalidasi
                </h3>
                <span class="text-xs text-gray-500">
                    Diupload: {{ $pengajuan->created_at->format('d M Y, H:i') }}
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                            <th class="px-6 py-4 font-semibold w-1/3">Dokumen</th>
                            <th class="px-6 py-4 font-semibold">Tanda Tangan Digital</th>
                            <th class="px-6 py-4 font-semibold">Link / Revisi</th>
                            <th class="px-6 py-4 font-semibold text-right">File Asli</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($pengajuan->dokumen as $dok)
                        <tr class="hover:bg-gray-50 transition-colors">
                            {{-- NAMA DOKUMEN --}}
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">
                                    {{ str_replace('_', ' ', $dok->tipe_dokumen) }}
                                </div>
                                @if($dok->tipe_dokumen == 'NASKAH_TA')
                                    <span class="text-[10px] bg-blue-50 text-blue-600 px-2 py-0.5 rounded border border-blue-100 font-semibold">
                                        Dokumen Utama
                                    </span>
                                @endif
                            </td>

                            {{-- STATUS TTD DIGITAL --}}
                            <td class="px-6 py-4">
                                @if($dok->is_signed)
                                    <div class="flex items-center text-green-600 text-xs font-semibold" title="Integritas Terjamin">
                                        <i class="fa-solid fa-file-signature mr-1.5 text-lg"></i>
                                        <div>
                                            Signed (EdDSA)<br>
                                            <span class="text-[10px] text-gray-400 font-normal">Hash Valid</span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>
                            
                            {{-- LINK EXTERNAL (GOOGLE DOCS) --}}
                            <td class="px-6 py-4">
                                @if($dok->tipe_dokumen == 'NASKAH_TA' && !empty($dok->external_link))
                                    <a href="{{ $dok->external_link }}" 
                                       target="_blank" 
                                       class="inline-flex items-center px-3 py-2 bg-white border border-blue-200 text-blue-700 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition shadow-sm text-sm group">
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/0/01/Google_Docs_logo_%282014-2020%29.svg" 
                                             alt="Docs" class="w-4 h-4 mr-2 opacity-80 group-hover:opacity-100">
                                        <span class="font-medium">Buka Google Docs</span>
                                        <i class="fa-solid fa-up-right-from-square ml-2 text-xs opacity-50"></i>
                                    </a>
                                    <p class="mt-1 text-[11px] text-gray-500 ml-1">
                                        *Klik untuk memberikan komentar/revisi
                                    </p>
                                @elseif($dok->tipe_dokumen == 'NASKAH_TA')
                                    <span class="text-xs text-red-400 italic">
                                        Link GDocs tidak disertakan
                                    </span>
                                @else
                                    <span class="text-gray-300 text-sm">-</span>
                                @endif
                            </td>

                            {{-- TOMBOL DOWNLOAD FILE --}}
                            <td class="px-6 py-4 text-right">
                                {{-- Menggunakan route dokumen.download global --}}
                                <a href="{{ route('dokumen.download', $dok->id) }}" 
                                   class="inline-flex items-center text-gray-600 hover:text-blue-600 font-semibold transition text-sm">
                                    <span>Download</span>
                                    <i class="fa-solid fa-download ml-2"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 3. CARD PERSETUJUAN (APPROVAL) --}}
        @php
            $dosenId = Auth::user()->dosen_id;
            $isDosbing1 = $tugasAkhir->dosen_pembimbing_1_id == $dosenId;
            $isDosbing2 = $tugasAkhir->dosen_pembimbing_2_id == $dosenId;
            
            $approvedAt = null;
            if ($isDosbing1) $approvedAt = $tugasAkhir->dosbing_1_approved_at;
            if ($isDosbing2) $approvedAt = $tugasAkhir->dosbing_2_approved_at;
        @endphp

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-bold text-gray-800 mb-4 pb-2 border-b">Persetujuan Maju Sidang</h3>
            
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                {{-- Bagian Teks Status --}}
                <div class="flex-1">
                    @if($approvedAt)
                        <div class="flex items-start text-green-700 bg-green-50 p-3 rounded-lg border border-green-100">
                            <i class="fa-solid fa-circle-check text-xl mr-3 mt-0.5"></i>
                            <div>
                                <p class="font-bold">Anda telah menyetujui mahasiswa ini.</p>
                                <p class="text-sm opacity-80">
                                    Disetujui pada: {{ \Carbon\Carbon::parse($approvedAt)->translatedFormat('l, d F Y H:i') }}
                                </p>
                            </div>
                        </div>
                    @else
                        <p class="text-gray-700 font-medium">Apakah berkas dan naskah mahasiswa sudah layak untuk disidangkan?</p>
                        <p class="text-sm text-gray-500 mt-1">
                            Pastikan Anda sudah memeriksa Link Google Docs (untuk Naskah TA) dan memberikan revisi jika diperlukan sebelum menyetujui.
                        </p>
                    @endif
                </div>

                {{-- Bagian Tombol Aksi --}}
                @if(!$approvedAt)
                    <form action="{{ route('dosen.bimbingan.approve', $tugasAkhir->id) }}" method="POST"
                          onsubmit="return confirm('Apakah Anda yakin menyetujui mahasiswa ini untuk maju sidang? Aksi ini tidak dapat dibatalkan.');">
                        @csrf
                        <button type="submit" 
                                class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition transform hover:-translate-y-0.5 flex items-center justify-center">
                            <i class="fa-solid fa-signature mr-2"></i>
                            Setujui Maju Sidang
                        </button>
                    </form>
                @endif
            </div>
        </div>
    @endif

</x-dosen-layout>
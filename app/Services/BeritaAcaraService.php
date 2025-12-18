<?php

namespace App\Services;

use App\Models\Sidang;
use App\Models\BeritaAcara;

class BeritaAcaraService
{
    /**
     * Mengkalkulasi dan men-generate Berita Acara untuk Sidang.
     */
    public function generate(Sidang $sidang)
    {
        // 1. --- LOGIKA PERHITUNGAN NILAI (TETAP ADA) ---
        $lembarPenilaians = $sidang->lembarPenilaians;
        $jumlahPenilai = $lembarPenilaians->count();

        if ($jumlahPenilai == 0) return; // Mencegah pembagian nol

        $totalNMA = 0;
        $bobot_materi = 0.15;
        $bobot_sistematika = 0.10;
        $bobot_mempertahankan = 0.50;
        $bobot_pengetahuan_bidang = 0.15;
        $bobot_karya_ilmiah = 0.10;
        
        // Cek apakah ada revisi dari penguji
        $adaRevisi = false;

        foreach ($lembarPenilaians as $lembar) {
            $nma_dosen = 
                ($lembar->nilai_materi * $bobot_materi) +
                ($lembar->nilai_sistematika * $bobot_sistematika) +
                ($lembar->nilai_mempertahankan * $bobot_mempertahankan) +
                ($lembar->nilai_pengetahuan_bidang * $bobot_pengetahuan_bidang) +
                ($lembar->nilai_karya_ilmiah * $bobot_karya_ilmiah);
            
            $totalNMA += $nma_dosen;

            // Cek jika dosen ini memberi catatan revisi
            if (!empty($lembar->komentar_revisi)) {
                $adaRevisi = true;
            }
        }

        $rataRataNMA = $totalNMA / $jumlahPenilai;
        $nilaiRelatif = $this->getNilaiRelatif($rataRataNMA);

        // --- PENENTUAN STATUS KELULUSAN ---
        if ($rataRataNMA < 55) {
            $hasilUjian = 'TIDAK_LULUS';
        } else {
            // Jika nilai cukup, cek apakah ada revisi
            if ($adaRevisi) {
                // Pastikan status 'LULUS' ini sesuai dengan ENUM di DB Anda
                $hasilUjian = 'LULUS'; 
            } else {
                $hasilUjian = 'LULUS';
            }
        }

        // 2. --- SIMPAN BERITA ACARA ---
        BeritaAcara::updateOrCreate(
            ['sidang_id' => $sidang->id],
            [
                'jumlah_nilai_mentah_nma' => $totalNMA,
                'rata_rata_nma' => $rataRataNMA,
                'nilai_relatif_nr' => $nilaiRelatif,
                'hasil_ujian' => $hasilUjian,
            ]
        );

        // 3. --- UPDATE STATUS SIDANG (Anak) ---
        // Menggunakan update() langsung agar tidak terhalang logic Model lain
        Sidang::where('id', $sidang->id)->update([
            'status' => $hasilUjian
        ]);
        
        // 4. --- UPDATE STATUS TUGAS AKHIR (Parent) [BAGIAN BARU] ---
        // Logika: Jika Sidang LULUS -> Tugas Akhir jadi SELESAI/LULUS
        // Jika Sidang TIDAK LULUS -> Tugas Akhir jadi REVISI/SIDANG_ULANG
        
        $statusTaBaru = 'SELESAI'; // Default jika lulus

        if ($hasilUjian == 'LULUS') {
            // Cek ENUM di tabel `tugas_akhirs` Anda.
            // Gunakan 'LULUS', 'SELESAI', atau 'REVISI' sesuai kebutuhan.
            $statusTaBaru = 'SELESAI'; 
        } else {
            $statusTaBaru = 'SIDANG_ULANG'; // Sesuaikan dengan ENUM DB (misal: 'GAGAL' atau 'AKTIF')
        }

        // Update tabel tugas_akhirs berdasarkan relasi
        if ($sidang->tugasAkhir) {
            $sidang->tugasAkhir()->update([
                'status' => $statusTaBaru
            ]);
        }

        // Refresh object agar data terbaru terbaca
        $sidang->refresh();
    }

    private function getNilaiRelatif($nma)
    {
        if ($nma >= 81) return 'A';
        if ($nma >= 73) return 'AB';
        if ($nma >= 66) return 'B';
        if ($nma >= 60) return 'BC';
        if ($nma >= 55) return 'C';
        return 'TIDAK LULUS';
    }
}
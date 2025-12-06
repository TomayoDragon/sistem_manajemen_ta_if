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
        $lembarPenilaians = $sidang->lembarPenilaians;
        $jumlahPenilai = $lembarPenilaians->count();

        if ($jumlahPenilai == 0) return; // Mencegah pembagian nol

        $totalNMA = 0;
        $bobot_materi = 0.15;
        $bobot_sistematika = 0.10;
        $bobot_mempertahankan = 0.50;
        $bobot_pengetahuan_bidang = 0.15;
        $bobot_karya_ilmiah = 0.10;

        foreach ($lembarPenilaians as $lembar) {
            $nma_dosen = 
                ($lembar->nilai_materi * $bobot_materi) +
                ($lembar->nilai_sistematika * $bobot_sistematika) +
                ($lembar->nilai_mempertahankan * $bobot_mempertahankan) +
                ($lembar->nilai_pengetahuan_bidang * $bobot_pengetahuan_bidang) +
                ($lembar->nilai_karya_ilmiah * $bobot_karya_ilmiah);
            
            $totalNMA += $nma_dosen;
        }

        $rataRataNMA = $totalNMA / $jumlahPenilai;
        $nilaiRelatif = $this->getNilaiRelatif($rataRataNMA);
        $hasilUjian = ($rataRataNMA < 55) ? 'TIDAK_LULUS' : 'LULUS';

        BeritaAcara::updateOrCreate(
            ['sidang_id' => $sidang->id],
            [
                'jumlah_nilai_mentah_nma' => $totalNMA,
                'rata_rata_nma' => $rataRataNMA,
                'nilai_relatif_nr' => $nilaiRelatif,
                'hasil_ujian' => $hasilUjian,
            ]
        );

        $sidang->status = $hasilUjian;
        $sidang->save();
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
<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DokumenPengajuan; 
use App\Models\DokumenHasilSidang;

class DigitalSignatureController extends Controller
{
    public function index(Request $request)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        $mahasiswaId = $mahasiswa->id;
        $ta = $mahasiswa->tugasAkhirs()->latest()->first();
        
        // Collection gabungan
        $mergedDocs = collect();

        // -----------------------------------------------------------
        // 1. DOKUMEN HASIL SIDANG (BA & REVISI) - DARI SYSTEM
        // -----------------------------------------------------------
        if ($ta) {
            $sidangIds = $ta->sidangs()->pluck('id');
            $systemDocs = DokumenHasilSidang::whereIn('sidang_id', $sidangIds)->latest()->get();

            foreach($systemDocs as $doc) {
                $item = new \stdClass();
                
                $item->id = $doc->id;
                $item->nama_file_asli = ($doc->jenis_dokumen == 'BERITA_ACARA') ? 'Berita Acara Sidang.pdf' : 'Lembar Revisi.pdf';
                $item->tipe_dokumen = ($doc->jenis_dokumen == 'BERITA_ACARA') ? 'BERITA ACARA' : 'LEMBAR REVISI';
                
                $item->hash_combined = $doc->hash_sha512_full ?? $doc->hash_combined ?? '-';
                $item->signature_base64 = $doc->signature_data;

                $item->download_url = route('dokumen.hasil-sidang', [
                    'sidang' => $doc->sidang_id, 
                    'jenis' => ($doc->jenis_dokumen == 'BERITA_ACARA' ? 'berita-acara' : 'revisi'), 
                    'mode' => 'view'
                ]);

                // [PERBAIKAN PENTING DI SINI]
                // Tambahkan parameter 'source' agar IntegritasController tahu ini dari tabel hasil sidang
                $item->verify_url = route('integritas.show', [
                    'dokumen' => $doc->id, 
                    'source'  => 'system' // <--- Penanda Sumber
                ]); 

                $item->is_system = true;
                $item->created_at = $doc->created_at;

                $mergedDocs->push($item);
            }
        }

        // -----------------------------------------------------------
        // 2. DOKUMEN UPLOAD MAHASISWA
        // -----------------------------------------------------------
        $queryUploads = DokumenPengajuan::query()
            ->where('is_signed', true)
            ->whereHas('pengajuanSidang.tugasAkhir', function ($q) use ($mahasiswaId) {
                $q->where('mahasiswa_id', $mahasiswaId);
            });

        $uploads = $queryUploads->latest()->get();

        foreach($uploads as $up) {
            $item = new \stdClass();

            $item->id = $up->id;
            $item->nama_file_asli = $up->nama_file_asli;
            $item->tipe_dokumen = $up->tipe_dokumen;

            // Logic Hash
            $rawHash = $up->hash_combined ?? $up->hash_file ?? $up->hash ?? $up->file_hash;
            if ($rawHash && !ctype_print($rawHash)) {
                $item->hash_combined = bin2hex($rawHash); 
            } else {
                $item->hash_combined = $rawHash ?? '-';
            }

            // Logic Signature
            $rawSig = $up->signature_base64 ?? $up->signature_data ?? $up->signature;
            if ($rawSig && !ctype_print($rawSig)) {
                $item->signature_base64 = base64_encode($rawSig);
            } else {
                $item->signature_base64 = $rawSig ?? '-';
            }

            $item->download_url = route('dokumen.download', ['dokumen' => $up->id, 'mode' => 'view']);
            
            // [PERBAIKAN PENTING DI SINI]
            // Tambahkan parameter 'source' untuk dokumen upload
            $item->verify_url = route('integritas.show', [
                'dokumen' => $up->id,
                'source'  => 'upload' // <--- Penanda Sumber
            ]);

            $item->is_system = false;
            $item->created_at = $up->created_at;

            $mergedDocs->push($item);
        }

        $dokumenTertanda = $mergedDocs->sortByDesc('created_at');

        return view('mahasiswa.digital-signature', [
            'dokumenTertanda' => $dokumenTertanda
        ]);
    }
}
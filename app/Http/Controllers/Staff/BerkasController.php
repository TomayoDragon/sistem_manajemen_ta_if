<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\BerkasTa; // <-- Import model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // <-- Import Storage

class BerkasController extends Controller
{
    /**
     * Menangani permintaan download berkas yang aman.
     */
    public function download($id)
    {
        // 1. Cari data berkas di database
        $berkas = BerkasTa::findOrFail($id);

        // 2. Ambil path penyimpanan dari database
        $path = $berkas->path_penyimpanan;

        // 3. Cek apakah file-nya ada di storage
        if (!Storage::exists($path)) {
            // Jika file hilang dari server, kembalikan error
            return redirect()->back()->with('error', 'File tidak ditemukan di server.');
        }

        // 4. Jika ada, stream file sebagai download
        // 'Storage::download' akan mengambil file dari storage/app
        // dan mengirimkannya ke browser dengan nama aslinya.
        return Storage::download($path, $berkas->nama_file_asli);
    }
}
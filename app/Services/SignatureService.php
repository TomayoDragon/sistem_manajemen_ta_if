<?php

namespace App\Services;

use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;

class SignatureService
{
    // =================================================================
    // 1. MANAJEMEN KUNCI (OTOMATIS)
    // =================================================================

    public function generateAndStoreKeys(Mahasiswa $mahasiswa)
    {
        $keyPair = sodium_crypto_sign_keypair();
        $publicKey = sodium_crypto_sign_publickey($keyPair);
        $privateKey = sodium_crypto_sign_secretkey($keyPair);

        $encryptedPrivateKey = Crypt::encryptString(base64_encode($privateKey));

        $mahasiswa->updateQuietly([
            'public_key' => base64_encode($publicKey),
            'private_key_encrypted' => $encryptedPrivateKey
        ]);
        
        sodium_memzero($privateKey);
        sodium_memzero($keyPair);
    }

    // =================================================================
    // 2. HASHING (NOVETLI: SHA-512 + BLAKE2b)
    // =================================================================

    /**
     * (Untuk Verifikasi) Mengambil konten mentah dari 3 file saat VERIFIKASI.
     */
    public function getConcatenatedFileContentsFromVerification(Request $request): string
    {
        return ''; // Helper (tidak dipakai di logika per-file ini)
    }

    public function performCustomHash(string $content): array
    {
        // 1. Hash SHA-512
        $sha512_full_raw = hash('sha512', $content, true);
        
        // 2. Hash BLAKE2b (Menggunakan Sodium)
        $blake2b_full_raw = sodium_crypto_generichash($content, '', 64); 

        // 3. Gabungkan 32 byte pertama
        $sha512_32bytes = substr($sha512_full_raw, 0, 32);
        $blake2b_32bytes = substr($blake2b_full_raw, 0, 32);
        $combined_raw = $sha512_32bytes . $blake2b_32bytes;

        return [
            'sha512_full_hex' => bin2hex($sha512_full_raw),
            'blake2b_full_hex' => bin2hex($blake2b_full_raw),
            'combined_hex' => bin2hex($combined_raw),
            'combined_raw_for_signing' => $combined_raw,
        ];
    }

    // =================================================================
    // 3. SIGNING (EDDSA)
    // =================================================================

    public function performRealEdDSASigning(string $combinedHashRaw, Mahasiswa $mahasiswa): string
    {
        $encryptedPrivateKey = $mahasiswa->private_key_encrypted;
        if (!$encryptedPrivateKey) {
            throw new \Exception("Mahasiswa ini tidak memiliki private key.");
        }

        $privateKey = base64_decode(Crypt::decryptString($encryptedPrivateKey));
        
        // Signing menggunakan Ed25519 (Sodium)
        $signature = sodium_crypto_sign_detached($combinedHashRaw, $privateKey);
        
        sodium_memzero($privateKey);

        return $signature;
    }

    // =================================================================
    // 4. VERIFIKASI (INI YANG HILANG TADI)
    // =================================================================

    /**
     * Memverifikasi Tanda Tangan Digital.
     * Menggunakan Public Key untuk memvalidasi Signature terhadap Hash.
     */
    public function verifySignature(string $signatureBiner, string $hashBiner, string $publicKeyBase64): bool
    {
        try {
            // 1. Dekode Public Key
            $publicKey = base64_decode($publicKeyBase64);

            // 2. Verifikasi Ed25519
            // Fungsi ini mengecek apakah Signature ini valid untuk Hash ini 
            // menggunakan Public Key ini.
            return sodium_crypto_sign_verify_detached($signatureBiner, $hashBiner, $publicKey);

        } catch (\Exception $e) {
            return false;
        }
    }
}
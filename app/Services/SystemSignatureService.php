<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Exception;

class SystemSignatureService
{
    /**
     * Menghitung Hash file (SHA512 + Blake2b)
     * Logika ini SAMA PERSIS dengan UploadController kamu agar standar.
     */
    public function calculateHash($fileContent)
    {
        // 1. SHA-512 Hash
        $sha512 = hash('sha512', $fileContent);

        // 2. Blake2b Hash (menggunakan library Sodium bawaan PHP)
        $blake2b = sodium_bin2hex(sodium_crypto_generichash($fileContent));

        // 3. Combined Hash (Gabungan keduanya untuk diperkuat)
        $combinedString = $sha512 . $blake2b;
        $combinedHash = hash('sha512', $combinedString);

        return [
            'sha512' => $sha512,
            'blake2b' => $blake2b,
            'combined' => $combinedHash,
            'raw_combined' => hex2bin($combinedHash) // Data biner untuk di-sign
        ];
    }

    /**
     * Melakukan Tanda Tangan Digital menggunakan Kunci Privat Sistem
     */
    public function signWithSystemKey($rawDataToSign)
    {
        // Cek apakah kunci ada
        if (!Storage::exists('secure_keys/system_private.key')) {
            throw new Exception('System Private Key not found! Run php artisan system:generate-keys first.');
        }

        // Ambil kunci private sistem
        $privateKeyB64 = Storage::get('secure_keys/system_private.key');
        $privateKey = base64_decode($privateKeyB64);

        // Lakukan Signing (Ed25519)
        $signature = sodium_crypto_sign_detached($rawDataToSign, $privateKey);

        // Kembalikan Signature dalam bentuk Base64
        return base64_encode($signature);
    }
}
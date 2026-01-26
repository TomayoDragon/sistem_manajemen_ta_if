<?php

namespace App\Services;

use Exception;

class SystemSignatureService
{
    protected $secretKey;
    protected $publicKey;
    protected $publicKeyBase64;

    public function __construct()
    {
        $this->loadKeys();
    }

    protected function loadKeys()
    {
        $b64Secret = config('services.signature.system_secret_key');
        $b64Public = config('services.signature.system_public_key');

        if (!$b64Secret || !$b64Public) {
            throw new Exception('Key belum diset di .env');
        }

        $this->publicKeyBase64 = $b64Public;
        $this->secretKey = base64_decode($b64Secret);
        $this->publicKey = base64_decode($b64Public);
    }

    public function getPublicKey()
    {
        return $this->publicKeyBase64; 
    }

    /**
     * Tetap gunakan Base64 untuk output signature agar tidak crash (UTF-8 Error)
     */
    public function signWithSystemKey($message)
    {
        $binarySignature = sodium_crypto_sign_detached($message, $this->secretKey);
        return base64_encode($binarySignature);
    }

    /**
     * PERBAIKAN: Mengembalikan array lengkap termasuk 'sha512' dan 'blake2b'
     * agar DokumenSystemHelper tidak error "Undefined array key".
     */
    public function calculateHash($fileContent)
    {
        // 1. SHA-512 (Full & Split)
        $sha512_full_raw = hash('sha512', $fileContent, true);
        $sha512_32bytes  = substr($sha512_full_raw, 0, 32);

        // 2. BLAKE2b (Full & Split) - Length WAJIB 64
        $blake2b_full_raw = sodium_crypto_generichash($fileContent, '', 64);
        $blake2b_32bytes  = substr($blake2b_full_raw, 0, 32);

        // 3. Gabungkan (Logic 32+32 agar cocok dengan Mahasiswa)
        $combined_raw = $sha512_32bytes . $blake2b_32bytes;

        return [
            // [INI YANG TADI HILANG] Diperlukan untuk log database
            'sha512' => bin2hex($sha512_full_raw),
            'blake2b' => bin2hex($blake2b_full_raw),
            
            // Diperlukan untuk verifikasi visual
            'combined' => bin2hex($combined_raw),
            
            // Diperlukan untuk proses signing
            'binary_for_signing' => $combined_raw, 
        ];
    }
}
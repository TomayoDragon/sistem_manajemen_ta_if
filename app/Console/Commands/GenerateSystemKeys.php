<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateSystemKeys extends Command
{
    // Ini nama perintah yang nanti diketik di terminal
    protected $signature = 'system:generate-keys';
    protected $description = 'Generate EdDSA Keypair for System Signing';

    public function handle()
    {
        if (!extension_loaded('sodium')) {
            $this->error('PHP Sodium extension is required!');
            return;
        }

        $this->info('Generating System Keypair...');

        // 1. Generate Pasangan Kunci Ed25519
        $keypair = sodium_crypto_sign_keypair();
        $secretKey = sodium_crypto_sign_secretkey($keypair);
        $publicKey = sodium_crypto_sign_publickey($keypair);

        // 2. Encode ke Base64 agar bisa disimpan sebagai teks
        $secretKeyB64 = base64_encode($secretKey);
        $publicKeyB64 = base64_encode($publicKey);

        // 3. Simpan ke folder storage/app/secure_keys/
        // Pastikan folder ini aman dan tidak bisa diakses publik
        Storage::put('secure_keys/system_private.key', $secretKeyB64);
        Storage::put('secure_keys/system_public.key', $publicKeyB64);

        $this->info('Success! Keys stored in storage/app/secure_keys/');
    }
}
<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class FileEncryptionService
{
    private string $secretWord;
    private string $encryptionKey;

    public function __construct()
    {
        $this->secretWord = config('app.encryption_secret', 'default-secret');
        $this->encryptionKey = $this->generateEncryptionKey();
    }

    private function generateEncryptionKey(): string
    {
        return hash('sha256', $this->secretWord);
    }

    private function encryptContent(string $content): string
    {
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt(
            $content,
            'AES-256-CBC',
            $this->encryptionKey,
            OPENSSL_RAW_DATA,
            $iv
        );
        
        // Combine IV and encrypted content
        return base64_encode($iv . $encrypted);
    }

    private function decryptContent(string $encryptedContent): string
    {
        $data = base64_decode($encryptedContent);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        
        return openssl_decrypt(
            $encrypted,
            'AES-256-CBC',
            $this->encryptionKey,
            OPENSSL_RAW_DATA,
            $iv
        );
    }

    public function encryptAndStore(UploadedFile $file, string|int $userId): array
    {
        $originalName = $file->getClientOriginalName();
        $encryptedName = Str::random(40);
        $mimeType = $file->getMimeType();
        $size = $file->getSize();

        // Read file content
        $content = file_get_contents($file->getRealPath());

        // Encrypt the content with custom encryption
        $encryptedContent = $this->encryptContent($content);

        // Store the encrypted file
        $path = "files/{$userId}/{$encryptedName}";
        Storage::put($path, $encryptedContent);

        return [
            'original_name' => $originalName,
            'encrypted_name' => $encryptedName,
            'mime_type' => $mimeType,
            'size' => $size,
            'path' => $path,
        ];
    }

    public function decryptAndDownload(string $path): array
    {
        if (!Storage::exists($path)) {
            throw new \Exception('File not found');
        }

        // Get encrypted content
        $encryptedContent = Storage::get($path);

        // Decrypt the content with custom decryption
        $decryptedContent = $this->decryptContent($encryptedContent);

        return [
            'content' => $decryptedContent,
            'mime_type' => Storage::mimeType($path),
        ];
    }
} 
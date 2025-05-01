<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;

class FileEncryptionService
{
    public function encryptAndStore(UploadedFile $file, string|int $userId): array
    {
        $originalName = $file->getClientOriginalName();
        $encryptedName = Str::random(40);
        $mimeType = $file->getMimeType();
        $size = $file->getSize();

        // Read file content
        $content = file_get_contents($file->getRealPath());

        // Encrypt the content
        $encryptedContent = Crypt::encrypt($content);

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

        // Decrypt the content
        $decryptedContent = Crypt::decrypt($encryptedContent);

        return [
            'content' => $decryptedContent,
            'mime_type' => Storage::mimeType($path),
        ];
    }
} 
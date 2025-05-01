<?php

namespace App\Services;

use App\Models\Bucket;
use App\Models\File;
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
        $this->secretWord = config('app.encryption_secret');
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

    private function getFileExtension(string $mimeType): string
    {
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'text/plain' => 'txt',
            'text/csv' => 'csv',
            'application/json' => 'json',
            'application/xml' => 'xml',
            'application/zip' => 'zip',
            'application/x-rar-compressed' => 'rar',
            'application/x-7z-compressed' => '7z',
        ];

        return $extensions[$mimeType] ?? 'bin';
    }

    public function encryptAndStore(UploadedFile $file, int $bucketId): File
    {
        $bucket = Bucket::findOrFail($bucketId);
        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getMimeType();
        $extension = $this->getFileExtension($mimeType);
        $encryptedName = Str::random(40) . '.' . $extension;
        $size = $file->getSize();
        
        // Read file content
        $content = $file->getContent();
        
        // Encrypt content
        $encryptedContent = $this->encryptContent($content);
        
        // Store encrypted file using bucket name in path
        $path = "{$bucket->name}/{$encryptedName}";
        Storage::put($path, $encryptedContent);
        
        // Create file record
        return File::create([
            'bucket_id' => $bucketId,
            'original_name' => $originalName,
            'encrypted_name' => $encryptedName,
            'mime_type' => $mimeType,
            'size' => $size,
            'path' => $path,
        ]);
    }

    public function decryptAndDownload(File $file): array
    {
        // Get encrypted content
        $encryptedContent = Storage::get($file->path);
        
        // Decrypt content
        $decryptedContent = $this->decryptContent($encryptedContent);
        
        return [
            'content' => $decryptedContent,
            'name' => $file->original_name,
            'mime_type' => $file->mime_type,
        ];
    }
} 
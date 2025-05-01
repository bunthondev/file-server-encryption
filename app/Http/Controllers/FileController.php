<?php

namespace App\Http\Controllers;

use App\Models\Bucket;
use App\Models\File;
use App\Services\FileEncryptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    private FileEncryptionService $encryptionService;

    public function __construct(FileEncryptionService $encryptionService)
    {
        $this->encryptionService = $encryptionService;
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'bucket' => 'required|string|max:255',
        ]);

        if (!$request->hasFile('file')) {
            return response()->json(['message' => 'No file uploaded'], 400);
        }

        // Sanitize bucket name to be URL and filesystem friendly
        $bucketName = Str::slug($request->input('bucket'));

        // Find or create bucket
        $bucket = Bucket::firstOrCreate(
            ['name' => $bucketName, 'user_id' => Auth::guard('sanctum')->user()->id],
            ['description' => 'Created automatically']
        );

        $file = $this->encryptionService->encryptAndStore(
            $request->file('file'),
            $bucket->id
        );

        return response()->json([
            'message' => 'File uploaded successfully',
            'file' => $file,
            'bucket' => $bucket->name,
            'download_url' => "{$bucket->name}/{$file->encrypted_name}/download",
            'view_url' => "{$bucket->name}/{$file->encrypted_name}/view"
        ], 201);
    }

    public function downloadByPath(string $bucketName, string $fileName)
    {
        // Sanitize bucket name
        $bucketName = Str::slug($bucketName);

        // Find the file
        $file = File::whereHas('bucket', function ($query) use ($bucketName) {
            $query->where('name', $bucketName)
                  ->where('user_id', Auth::guard('sanctum')->user()->id);
        })->where('encrypted_name', $fileName)->firstOrFail();

        $fileData = $this->encryptionService->decryptAndDownload($file);

        return response($fileData['content'])
            ->header('Content-Type', $fileData['mime_type'])
            ->header('Content-Disposition', 'attachment; filename="' . $fileData['name'] . '"');
    }

    public function viewByPath(string $bucketName, string $fileName)
    {
        // Sanitize bucket name
        $bucketName = Str::slug($bucketName);

        // Find the file
        $file = File::whereHas('bucket', function ($query) use ($bucketName) {
            $query->where('name', $bucketName)
                  ->where('user_id', Auth::guard('sanctum')->user()->id);
        })->where('encrypted_name', $fileName)->firstOrFail();

        $fileData = $this->encryptionService->decryptAndDownload($file);

        return response($fileData['content'])
            ->header('Content-Type', $fileData['mime_type'])
            ->header('Content-Disposition', 'inline; filename="' . $fileData['name'] . '"');
    }

    public function index(Request $request)
    {
        $request->validate([
            'bucket' => 'required|string|max:255',
        ]);

        // Sanitize bucket name
        $bucketName = Str::slug($request->input('bucket'));

        $bucket = Bucket::where('name', $bucketName)
            ->where('user_id', Auth::guard('sanctum')->user()->id)
            ->firstOrFail();

        // Add download and view URLs to each file
        $files = $bucket->files->map(function ($file) use ($bucket) {
            $file->download_url = "/files/{$bucket->name}/{$file->encrypted_name}/download";
            $file->view_url = "/files/{$bucket->name}/{$file->encrypted_name}/view";
            return $file;
        });

        return response()->json([
            'bucket' => $bucket->name,
            'files' => $files
        ]);
    }

    public function destroy(Request $request, string $bucketName, string $fileName)
    {
        // Sanitize bucket name
        $bucketName = Str::slug($bucketName);

        // Find the file
        $file = File::whereHas('bucket', function ($query) use ($bucketName) {
            $query->where('name', $bucketName)
                  ->where('user_id', Auth::guard('sanctum')->user()->id);
        })->where('encrypted_name', $fileName)->firstOrFail();

        // Delete file from storage
        Storage::delete($file->path);

        // Delete file record
        $file->delete();

        return response()->json(['message' => 'File deleted successfully']);
    }
} 
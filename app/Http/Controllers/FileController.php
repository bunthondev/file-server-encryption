<?php

namespace App\Http\Controllers;

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

        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Sanitize bucket name to be URL and filesystem friendly
        $bucketName = Str::slug($request->input('bucket'));

        $file = $this->encryptionService->encryptAndStore(
            $request->file('file'),
            $bucketName,
            $user->id
        );

        return response()->json([
            'message' => 'File uploaded successfully',
            'file' => $file,
            'bucket' => $bucketName,
            'download_url' => "/files/{$bucketName}/{$file->encrypted_name}/download",
            'view_url' => "/files/{$bucketName}/{$file->encrypted_name}/view"
        ], 201);
    }

    public function downloadByPath(string $bucketName, string $fileName)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Sanitize bucket name
        $bucketName = Str::slug($bucketName);

        // Find the file
        $file = File::where('bucket', $bucketName)
            ->where('user_id', $user->id)
            ->where('encrypted_name', $fileName)
            ->firstOrFail();

        $fileData = $this->encryptionService->decryptAndDownload($file);

        return response($fileData['content'])
            ->header('Content-Type', $fileData['mime_type'])
            ->header('Content-Disposition', 'attachment; filename="' . $fileData['name'] . '"');
    }

    public function viewByPath(string $bucketName, string $fileName)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Sanitize bucket name
        $bucketName = Str::slug($bucketName);

        // Find the file
        $file = File::where('bucket', $bucketName)
            ->where('user_id', $user->id)
            ->where('encrypted_name', $fileName)
            ->firstOrFail();

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

        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Sanitize bucket name
        $bucketName = Str::slug($request->input('bucket'));

        $files = File::where('bucket', $bucketName)
            ->where('user_id', $user->id)
            ->get();

        // Add download and view URLs to each file
        $files = $files->map(function ($file) use ($bucketName) {
            $file->download_url = "/files/{$bucketName}/{$file->encrypted_name}/download";
            $file->view_url = "/files/{$bucketName}/{$file->encrypted_name}/view";
            return $file;
        });

        return response()->json([
            'bucket' => $bucketName,
            'files' => $files
        ]);
    }

    public function destroy(Request $request, string $bucketName, string $fileName)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Sanitize bucket name
        $bucketName = Str::slug($bucketName);

        // Find the file
        $file = File::where('bucket', $bucketName)
            ->where('user_id', $user->id)
            ->where('encrypted_name', $fileName)
            ->firstOrFail();

        // Delete file from storage
        Storage::delete($file->path);

        // Delete file record
        $file->delete();

        return response()->json(['message' => 'File deleted successfully']);
    }
} 
<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Services\FileEncryptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    protected $encryptionService;

    public function __construct(FileEncryptionService $encryptionService)
    {
        $this->encryptionService = $encryptionService;
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // Max 10MB
        ]);

        $file = $request->file('file');
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $fileData = $this->encryptionService->encryptAndStore($file, (string) $user->id);

        $savedFile = File::create([
            'user_id' => $user->id,
            'original_name' => $fileData['original_name'],
            'encrypted_name' => $fileData['encrypted_name'],
            'mime_type' => $fileData['mime_type'],
            'size' => $fileData['size'],
            'path' => $fileData['path'],
        ]);

        return response()->json([
            'message' => 'File uploaded successfully',
            'file' => $savedFile,
        ], 201);
    }

    public function download(File $file)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if ($file->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $fileData = $this->encryptionService->decryptAndDownload($file->path);

            return response($fileData['content'])
                ->header('Content-Type', $fileData['mime_type'])
                ->header('Content-Disposition', 'attachment; filename="' . $file->original_name . '"');
        } catch (\Exception $e) {
            return response()->json(['message' => 'File not found'], 404);
        }
    }

    public function view(File $file)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if ($file->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $fileData = $this->encryptionService->decryptAndDownload($file->path);

            return response($fileData['content'])
                ->header('Content-Type', $fileData['mime_type'])
                ->header('Content-Disposition', 'inline; filename="' . $file->original_name . '"');
        } catch (\Exception $e) {
            return response()->json(['message' => 'File not found'], 404);
        }
    }

    public function index()
    {   
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $files = File::where('user_id', $user->id)->get();
        return response()->json($files);
    }

    public function destroy(File $file)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if ($file->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        Storage::delete($file->path);
        $file->delete();

        return response()->json(['message' => 'File deleted successfully']);
    }
} 
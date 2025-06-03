<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\FilesystemAdapter;

class FileUploadController extends Controller
{

    public function showForm()
    {
        $path = 'uploads/profile.jpeg';

        try {
            $url = Storage::disk('s3')->url($path);
        } catch (\Exception $e) {
            $url = null;
            logger()->error('Failed to generate temporary URL: ' . $e->getMessage());
        }

        return view('admin.upload', ['imageUrl' => $url]);
    }

    public function uploadFile(Request $request)
    {
        Log::info('Upload started');

        $request->validate([
            'file' => 'required|file|max:10240', // max 10MB
        ]);

        $file = $request->file('file');
        Log::info('Got file', ['original_name' => $file->getClientOriginalName(), 'size' => $file->getSize()]);

        try {
            $path = Storage::disk('s3')->putFile('uploads/posts/images', $request->file('file'), 'public');

            Log::info('Stored path: ' . $path);

            if (!$path) {
                Log::error('Failed to store file on S3');
                return back()->withErrors('Failed to upload file');
            }

            // Отримуємо URL до файлу
            $url = Storage::disk('s3')->url($path);
            Log::info('File URL generated', ['url' => $url]);

            // Логуємо класи для дебагу
            $disk = Storage::disk('s3');
            Log::info('Disk class: ' . get_class($disk));
            Log::info('Driver class: ' . get_class($disk->getDriver()));

            return back()->with([
                'success' => 'File uploaded successfully!',
                'file_url' => $url,
            ]);
        } catch (\Exception $e) {
            Log::error('Upload failed', ['message' => $e->getMessage()]);
            return back()->withErrors('Failed to upload file: ' . $e->getMessage());
        }
    }


}

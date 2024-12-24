<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{

    public function showForm()
    {
        $imageUrl = Storage::disk('s3')->url('uploads/profile.jpeg');
        return view('admin.upload', ['imageUrl' => $imageUrl]);
    }

    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:2048',
        ]);

        $path = $request->file('file')->store('uploads', 's3');

        Storage::disk('s3')->setVisibility($path, 'public');

        $url = Storage::disk('s3')->url($path);

        return back()->with('success', 'File uploaded successfully!')->with('file_url', $url);
    }
}

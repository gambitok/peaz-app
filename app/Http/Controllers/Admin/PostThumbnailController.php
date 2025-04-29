<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostThumbnailResource;
use App\PostThumbnail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostThumbnailController extends Controller
{

    public function store(Request $request)
    {
        $validated = $request->validate([
            'post_id' => 'required|integer|exists:posts,id',
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Upload thumbnail to S3
        $file = $request->file('thumbnail');
        $filename = time() . '.' . $file->getClientOriginalExtension();
        $filePath = 'thumbnails/' . $filename;
        Storage::disk('s3')->put($filePath, file_get_contents($file), 'public');

        // Create thumbnail record
        $thumbnail = PostThumbnail::create([
            'post_id' => $validated['post_id'],
            'thumbnail' => $filePath,
            'title' => $validated['title'] ?? null,
            'description' => $validated['description'] ?? null,
            'type' => 'image'
        ]);

        return response()->json(new PostThumbnailResource($thumbnail), 201);
    }

    public function update(Request $request, $id)
    {
        $thumbnail = PostThumbnail::findOrFail($id);

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // If a new file is uploaded, replace the old one
        if ($request->hasFile('thumbnail')) {
            // Delete the old thumbnail from S3
            Storage::disk('s3')->delete($thumbnail->thumbnail);

            // Upload the new thumbnail
            $file = $request->file('thumbnail');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $filePath = 'thumbnails/' . $filename;
            Storage::disk('s3')->put($filePath, file_get_contents($file), 'public');

            $thumbnail->thumbnail = $filePath;
        }

        // Update other fields
        $thumbnail->title = $validated['title'] ?? $thumbnail->title;
        $thumbnail->description = $validated['description'] ?? $thumbnail->description;
        $thumbnail->save();

        return response()->json(new PostThumbnailResource($thumbnail), 200);
    }

    public function destroy($id)
    {
        $thumbnail = PostThumbnail::findOrFail($id);
        Storage::disk('s3')->delete($thumbnail->thumbnail);
        $thumbnail->delete();

        return back()->with('success', 'Thumbnail deleted.');
    }
}

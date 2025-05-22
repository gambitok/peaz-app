<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostThumbnailResource;
use Illuminate\Http\Request;
use App\PostThumbnail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PostThumbnailController extends Controller
{
    public function index(Request $request)
    {
        $query = PostThumbnail::query();

        if ($request->has('post_id')) {
            $query->where('post_id', $request->input('post_id'));
        }

        $thumbnails = $query->get();

        return response()->json(PostThumbnailResource::collection($thumbnails), 200);
    }

    public function show($id)
    {
        $thumbnail = PostThumbnail::findOrFail($id);

        return response()->json(new PostThumbnailResource($thumbnail), 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'post_id' => 'required|integer|exists:posts,id',
            'file' => 'required|string|max:2048',
            'thumbnail' => 'required|string|max:2048',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $filePath = $validated['file'];
        $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'svg'])) {
            $fileType = 'image';
        } elseif (in_array($fileExtension, ['mp4', 'avi', 'mov', 'mkv', 'wmv', 'flv'])) {
            $fileType = 'video';
        } else {
            return response()->json(['error' => 'Invalid file type for "file".'], 400);
        }

        $thumbnailPath = $validated['thumbnail'];
        $thumbnailExtension = strtolower(pathinfo($thumbnailPath, PATHINFO_EXTENSION));

        if (in_array($thumbnailExtension, ['jpg', 'jpeg', 'png', 'gif', 'svg'])) {
            $thumbnailType = 'image';
        } elseif (in_array($thumbnailExtension, ['mp4', 'avi', 'mov', 'mkv', 'wmv', 'flv'])) {
            $thumbnailType = 'video';
        } else {
            return response()->json(['error' => 'Invalid file type for "thumbnail".'], 400);
        }

        $thumbnail = PostThumbnail::create([
            'post_id' => $validated['post_id'],
            'file' => $filePath,
            'file_type' => $fileType,
            'thumbnail' => $thumbnailPath,
            'type' => $thumbnailType,
            'title' => $validated['title'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json(new PostThumbnailResource($thumbnail), 201);
    }

    public function update(Request $request, $id)
    {
        $thumbnail = PostThumbnail::findOrFail($id);

        $validated = $request->validate([
            'post_id' => 'nullable|integer|exists:posts,id',
            'file' => 'nullable|string|max:2048',
            'thumbnail' => 'nullable|string|max:2048',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        if (!empty($validated['file'])) {
            $filePath = $validated['file'];
            $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

            if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'svg'])) {
                $thumbnail->file_type = 'image';
            } elseif (in_array($fileExtension, ['mp4', 'avi', 'mov', 'mkv', 'wmv', 'flv'])) {
                $thumbnail->file_type = 'video';
            } else {
                return response()->json(['error' => 'Invalid file type for "file".'], 400);
            }

            $thumbnail->file = $filePath;
        }

        if (!empty($validated['thumbnail'])) {
            $thumbPath = $validated['thumbnail'];
            $thumbExtension = strtolower(pathinfo($thumbPath, PATHINFO_EXTENSION));

            if (in_array($thumbExtension, ['jpg', 'jpeg', 'png', 'gif', 'svg'])) {
                $thumbnail->type = 'image';
            } elseif (in_array($thumbExtension, ['mp4', 'avi', 'mov', 'mkv', 'wmv', 'flv'])) {
                $thumbnail->type = 'video';
            } else {
                return response()->json(['error' => 'Invalid file type for "thumbnail".'], 400);
            }

            $thumbnail->thumbnail = $thumbPath;
        }

        if (isset($validated['title'])) {
            $thumbnail->title = $validated['title'];
        }

        if (isset($validated['description'])) {
            $thumbnail->description = $validated['description'];
        }

        if (isset($validated['post_id'])) {
            $thumbnail->post_id = $validated['post_id'];
        }

        $thumbnail->save();

        return response()->json(new PostThumbnailResource($thumbnail));
    }

    public function destroy($id)
    {
        $thumbnail = PostThumbnail::findOrFail($id);

        Storage::disk('s3')->delete($thumbnail->getRawOriginal('thumbnail'));
        $thumbnail->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Thumbnail deleted successfully.'
        ], 200);
    }

}

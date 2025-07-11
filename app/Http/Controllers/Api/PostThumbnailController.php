<?php

namespace App\Http\Controllers\Api;

use App\PostThumbnail;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostThumbnailResource;
use Illuminate\Http\Request;

class PostThumbnailController extends Controller
{
    public function index()
    {
        $thumbnails = PostThumbnail::all();

        return PostThumbnailResource::collection($thumbnails);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $thumbnail = PostThumbnail::create($validatedData);
        return response(new PostThumbnailResource($thumbnail), 201);
    }

    public function show(PostThumbnail $thumbnail)
    {
        return new PostThumbnailResource($thumbnail);
    }

    public function update(Request $request, PostThumbnail $thumbnail)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $thumbnail->update($validatedData);
        return new PostThumbnailResource($thumbnail);
    }

    public function destroy(PostThumbnail $thumbnail)
    {
        $thumbnail->delete();
        return response()->noContent();
    }
}

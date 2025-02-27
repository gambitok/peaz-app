<?php

namespace App\Http\Controllers\Api;

use App\Tag;
use App\Http\Controllers\Controller;
use App\Http\Resources\TagResource;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::all();

        return TagResource::collection($tags);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $Tag = Tag::create($validatedData);
        return response(new TagResource($Tag), 201);
    }

    public function show(Tag $Tag)
    {
        return new TagResource($Tag);
    }

    public function update(Request $request, Tag $Tag)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $Tag->update($validatedData);
        return new TagResource($Tag);
    }

    public function destroy(Tag $Tag)
    {
        $Tag->delete();
        return response()->noContent();
    }
}

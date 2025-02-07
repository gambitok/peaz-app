<?php

namespace App\Http\Controllers\Api;

use App\Billboard;
use App\Http\Controllers\Controller;
use App\Http\Resources\BillboardResource;
use Illuminate\Http\Request;

class BillboardController extends Controller
{
    public function index()
    {
        return BillboardResource::collection(Billboard::all());
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'caption' => 'nullable|string|max:255',
            'file' => 'nullable|string|max:255',
            'link' => 'nullable|string|max:255',
            'tag_id' => 'nullable|exists:tags,id',
            'verified' => 'boolean',
            'status' => 'boolean',
        ]);

        $billboard = Billboard::create($validatedData);
        return new BillboardResource($billboard);
    }

    public function show(Billboard $billboard)
    {
        return new BillboardResource($billboard);
    }

    public function update(Request $request, Billboard $billboard)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'caption' => 'nullable|string|max:255',
            'file' => 'nullable|string|max:255',
            'link' => 'nullable|string|max:255',
            'tag_id' => 'nullable|exists:tags,id',
            'verified' => 'boolean',
            'status' => 'boolean',
        ]);

        $billboard->update($validatedData);
        return new BillboardResource($billboard);
    }

    public function destroy(Billboard $billboard)
    {
        $billboard->delete();
        return response()->noContent();
    }
}

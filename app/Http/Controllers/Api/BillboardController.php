<?php

namespace App\Http\Controllers\Api;

use App\Billboard;
use App\Http\Controllers\Controller;
use App\Http\Resources\BillboardResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillboardController extends Controller
{
    public function index()
    {
        $billboards = Billboard::with('user', 'tag')->get();

        return BillboardResource::collection($billboards);
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

        $validatedData['user_id'] = $request->user()->id;

        $billboard = Billboard::create($validatedData);
        return response(new BillboardResource($billboard), 201);
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

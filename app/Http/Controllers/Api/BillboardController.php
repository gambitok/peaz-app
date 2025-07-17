<?php

namespace App\Http\Controllers\Api;

use App\Billboard;
use App\Http\Controllers\Controller;
use App\Http\Requests\BillboardRequest;
use App\Http\Resources\BillboardResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BillboardController extends Controller
{
    public function index()
    {
        return BillboardResource::collection(Billboard::with('user', 'tag')->paginate(10));
    }

    public function store(BillboardRequest $request)
    {
        $data = $request->validated();

        $data['user_id'] = $request->user()->id;
        $billboard = Billboard::create($data);
        $data['file'] = $this->uploadFile($request, $billboard->id);

        return response(new BillboardResource($billboard), 201);
    }

    public function show(Billboard $billboard)
    {
        return new BillboardResource($billboard);
    }

    public function update(BillboardRequest $request, Billboard $billboard)
    {
        $data = $request->validated();

        if ($request->hasFile('file')) {
            if ($billboard->file) {
                Storage::disk('s3')->delete($billboard->file);
            }
            $data['file'] = $this->uploadFile($request, $billboard->id);
        }
        $billboard->update($data);

        return new BillboardResource($billboard);
    }

    public function destroy(Billboard $billboard)
    {
        if ($billboard->file) {
            Storage::disk('s3')->delete($billboard->file);
        }
        $billboard->delete();

        return response()->noContent();
    }

    public function uploadFile(Request $request, $billboardId)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = Storage::disk('s3')->putFile("uploads/billboards/{$billboardId}", $file, 'public');
            return $path;
        }

        return null;
    }
}

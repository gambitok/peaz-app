<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Billboard;
use App\Tag;
use App\Http\Requests\BillboardRequest;
use App\Services\BillboardService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BillboardViewController extends Controller
{
    protected $billboardService;

    public function __construct(BillboardService $billboardService)
    {
        $this->billboardService = $billboardService;
    }

    public function index()
    {
        $billboards = Billboard::with('user', 'tag')->get();
        return view('admin.billboards.index', [
            'billboards' => $billboards,
            'title' => 'Billboards',
            'breadcrumb' => breadcrumb([
                'Billboards' => route('admin.billboards.index'),
            ]),
        ]);
    }

    public function show($id)
    {
        $billboard = Billboard::with('user', 'tag')->findOrFail($id);
        return view('admin.billboards.show', compact('billboard'));
    }

    public function create()
    {
        $tags = Tag::all();
        return view('admin.billboards.create', compact('tags'));
    }

    public function edit($id)
    {
        $billboard = Billboard::findOrFail($id);
        $tags = Tag::all();

        return view('admin.billboards.edit', compact('billboard', 'tags'));
    }

    public function store(BillboardRequest $request)
    {
        $validatedData = $request->validated();
        $userId = $request->user()->id;

        $validatedData['status'] = isset($validatedData['status']) ? (int) filter_var($validatedData['status'], FILTER_VALIDATE_BOOLEAN) : 0;

        $billboard = Billboard::create(['user_id' => $userId] + $validatedData);
        $billboardId = $billboard->id;

        $fileFields = ['file', 'logo_file', 'horizontal_file', 'video_file'];

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $path = Storage::disk('s3')->putFile("uploads/billboards/$billboardId", $request->file($field), 'public');
                $validatedData[$field] = $path;
            }
        }

        $billboard->update($validatedData);

        return redirect()->route('admin.billboards.index')->with('success', 'Billboard created successfully.');
    }

    public function update(BillboardRequest $request, $id)
    {
        $billboard = Billboard::findOrFail($id);
        $validatedData = $request->validated();
        $billboardId = $billboard->id;

        // Convert status to boolean and then to an integer (0 or 1)
        $validatedData['status'] = isset($validatedData['status']) ? (int) filter_var($validatedData['status'], FILTER_VALIDATE_BOOLEAN) : 0;

        // Array to update file fields
        $fileFields = ['file', 'logo_file', 'horizontal_file', 'video_file'];

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $path = Storage::disk('s3')->putFile("uploads/billboards/$billboardId", $request->file($field), 'public');
                $validatedData[$field] = $path;
            } else {
                $validatedData[$field] = $billboard->getRawOriginal($field);
            }
        }

        // Convert status to boolean
        $validatedData['status'] = filter_var($validatedData['status'] ?? false, FILTER_VALIDATE_BOOLEAN);

        try {
            $billboard->update($validatedData);
            return redirect()->route('admin.billboards.index')->with('success', 'Billboard updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to update billboard.']);
        }
    }

    public function destroy($id)
    {
        $billboard = Billboard::findOrFail($id);
        $this->billboardService->delete($billboard);

        return redirect()->route('admin.billboards.index')->with('success', 'Billboard deleted successfully.');
    }
}

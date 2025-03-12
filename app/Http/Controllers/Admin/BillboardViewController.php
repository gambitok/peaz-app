<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Billboard;
use App\Tag;
use App\Http\Requests\BillboardRequest;
use App\Services\BillboardService;
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
        return view('admin.billboards.index', compact('billboards'));
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

    public function store(BillboardRequest $request)
    {
        $validatedData = $request->validated(); // This ensures only validated data is used

        // Handle file upload if present
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $path = $file->storeAs('uploads/billboards', time() . '.' . $extension, 's3');
            Storage::disk('s3')->setVisibility($path, 'public');
            $validatedData['file'] = $path;
        }

        $validatedData['user_id'] = $request->user()->id; // Assign the authenticated user

        Billboard::create($validatedData);

        // Redirect back to the list of billboards with a success message
        return redirect()->route('admin.billboards.index')
            ->with('success', 'Billboard created successfully.');
    }

    public function edit($id)
    {
        $billboard = Billboard::findOrFail($id);
        $tags = Tag::all();

        return view('admin.billboards.edit', compact('billboard', 'tags'));
    }

    public function update(BillboardRequest $request, $id)
    {
        // Fetch the existing billboard from the database
        $billboard = Billboard::findOrFail($id);

        // Get the validated data from the request
        $validatedData = $request->validated();

        // Handle the file
        $file = $billboard->getRawOriginal('file'); // Get the current file

        if ($request->hasFile('file')) {
            $extension = $request->file('file')->getClientOriginalExtension();
            $path = $request->file('file')->storeAs('uploads/billboards', time() . '.' . $extension, 's3');
            if ($path) {
                Storage::disk('s3')->setVisibility($path, 'public');
                $file = $path;
            }
        }

        // Update the status
        $status = filter_var($validatedData['status'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($status === null) {
            $status = false; // Default to false if invalid
        }

        // Prepare the update data
        $updateData = $request->only(['link']);
        $updateData['status'] = $status;
        $updateData['file'] = $file; // Only update the file if it changed

        // Attempt to update the billboard
        try {
            // Perform the update
            $updated = $billboard->update($updateData);

            // Check if the update was successful
            if ($updated) {
                return redirect()->route('admin.billboards.index')->with('success', 'Billboard updated successfully.');
            } else {
                return redirect()->back()->withInput()->withErrors(['error' => 'Failed to update billboard.']);
            }
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

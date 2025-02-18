<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\WebController;
use App\Http\Controllers\Api\BillboardController as ApiBillboardController;
use App\Billboard;
use App\Tag;
use App\Http\Resources\BillboardResource;
use Illuminate\Http\Request;

class BillboardViewController extends WebController
{
    protected $apiBillboardController;

    public function __construct(ApiBillboardController $apiBillboardController)
    {
        $this->apiBillboardController = $apiBillboardController;
    }

    public function index()
    {
        $billboards = $this->apiBillboardController->index()->toArray(request());

        return view('admin.billboards.index', compact('billboards'));
    }

    public function show($id)
    {
        $billboard = $this->apiBillboardController->show(Billboard::findOrFail($id))->toArray(request());

        return view('admin.billboards.show', compact('billboard'));
    }

    public function create()
    {
        $tags = Tag::all();
        return view('admin.billboards.create', compact('tags'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'verified' => $request->has('verified'),
            'status' => $request->has('status'),
        ]);

        $response = $this->apiBillboardController->store($request);

        if ($response->getStatusCode() === 201) {
            return redirect()->route('admin.billboards.index')->with('success', 'Billboard created successfully.');
        } else {
            $error = $response->json();
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to create billboard.', 'api_error' => $error]);
        }
    }

    public function edit($id)
    {
        $billboard = $this->apiBillboardController->show(Billboard::findOrFail($id))->toArray(request());
        $tags = Tag::all();

        return view('admin.billboards.edit', compact('billboard', 'tags'));
    }

    public function update(Request $request, $id)
    {
        $billboard = Billboard::findOrFail($id);

        $request->merge([
            'verified' => $request->has('verified'),
            'status' => $request->has('status'),
        ]);

        $response = $this->apiBillboardController->update($request, $billboard);

        if ($response instanceof BillboardResource) {
            return redirect()->route('admin.billboards.index')->with('success', 'Billboard updated successfully.');
        } else {
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to update billboard.']);
        }
    }

    public function destroy($id)
    {
        $billboard = Billboard::findOrFail($id);
        $response = $this->apiBillboardController->destroy($billboard);

        if ($response->getStatusCode() === 204) {
            return redirect()->route('admin.billboards.index')->with('success', 'Billboard deleted successfully.');
        } else {
            return redirect()->back()->withErrors(['error' => 'Failed to delete billboard.']);
        }
    }
}

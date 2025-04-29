<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\WebController;
use App\Http\Controllers\Api\FilterController as ApiFilterController;
use App\Filter;
use App\Http\Resources\FilterResource;
use App\Http\Requests\FilterRequest;
use App\Tag;

class FilterViewController extends WebController
{
    protected $apiFilterController;

    public function __construct(ApiFilterController $apiFilterController)
    {
        $this->apiFilterController = $apiFilterController;
    }

    public function index()
    {
        $filters = $this->apiFilterController->index()->toArray(request());
        $filters = array_map(function ($filter) {
            $filter['tags'] = Filter::find($filter['id'])->tags()->get();
            return $filter;
        }, $filters);
        return view('admin.filter.index', [
            'filters' => $filters,
            'title' => 'Inspiration sections',
            'breadcrumb' => breadcrumb([
                'Inspiration sections' => route('admin.filter.index'),
            ]),
        ]);
    }

    public function show($id)
    {
        $filter = Filter::with('tags')->findOrFail($id);
        return view('admin.filter.show', compact('filter'));
    }

    public function create()
    {
        $tags = Tag::all();
        return view('admin.filter.create', compact('tags'));
    }

    public function store(FilterRequest $request)
    {
        $request->merge(['tag_ids' => $request->input('tag_ids', [])]);
        $response = $this->apiFilterController->store($request);

        if ($response->getStatusCode() === 201) {
            return redirect()->route('admin.filter.index')->with('success', 'Filter created successfully.');
        } else {
            $error = $response->json();
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to create filter.', 'api_error' => $error]);
        }
    }

    public function edit($id)
    {
        $filter = Filter::with('tags')->findOrFail($id);
        $tags = Tag::all();

        return view('admin.filter.edit', compact('filter', 'tags'));
    }

    public function update(FilterRequest $request, $id)
    {
        $request->merge(['tag_ids' => $request->input('tag_ids', [])]);
        $filter = Filter::findOrFail($id);

        $response = $this->apiFilterController->update($request, $filter);

        if ($response instanceof FilterResource) {
            return redirect()->route('admin.filter.index')->with('success', 'Filter updated successfully.');
        } else {
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to update filter.']);
        }
    }

    public function destroy($id)
    {
        $filter = Filter::findOrFail($id);
        $response = $this->apiFilterController->destroy($filter);

        if ($response->getStatusCode() === 204) {
            return redirect()->route('admin.filter.index')->with('success', 'filter deleted successfully.');
        } else {
            return redirect()->back()->withErrors(['error' => 'Failed to delete filter.']);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Cuisine;
use App\Dietary;
use App\Http\Controllers\WebController;
use App\Http\Controllers\Api\FilterController as ApiFilterController;
use App\Filter;
use App\Http\Resources\FilterResource;
use App\Http\Requests\FilterRequest;
use App\Post;
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
            $filter['dietaries'] = Filter::find($filter['id'])->dietaries()->get();
            $filter['cuisines'] = Filter::find($filter['id'])->cuisines()->get();
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
        $filter = Filter::with(['tags', 'dietaries', 'cuisines'])->findOrFail($id);

        $tagIds = $filter->tags->pluck('id')->toArray();
        $dietaryIds = $filter->dietaries->pluck('id')->toArray();
        $cuisineIds = $filter->cuisines->pluck('id')->toArray();

        $posts = Post::query()
            ->when($tagIds, function ($query) use ($tagIds) {
                foreach ($tagIds as $tagId) {
                    $query->whereHas('tags', function ($q) use ($tagId) {
                        $q->where('tags.id', $tagId);
                    });
                }
            })
            ->when($dietaryIds, function ($query) use ($dietaryIds) {
                foreach ($dietaryIds as $dietaryId) {
                    $query->whereHas('dietaries', function ($q) use ($dietaryId) {
                        $q->where('dietaries.id', $dietaryId);
                    });
                }
            })
            ->when($cuisineIds, function ($query) use ($cuisineIds) {
                foreach ($cuisineIds as $cuisineId) {
                    $query->whereHas('cuisines', function ($q) use ($cuisineId) {
                        $q->where('cuisines.id', $cuisineId);
                    });
                }
            })
            ->with(['tags:id,name', 'dietaries:id,name', 'cuisines:id,name'])
            ->get();

        return view('admin.filter.show', compact('filter', 'posts'));
    }

    public function create()
    {
        $tags = Tag::all();
        $dietaries = Dietary::all();
        $cuisines = Cuisine::all();
        return view('admin.filter.create', compact('tags', 'dietaries', 'cuisines'));
    }

    public function store(FilterRequest $request)
    {
        $request->merge([
            'tag_ids' => $request->input('tag_ids', []),
            'dietary_ids' => $request->input('dietary_ids', []),
            'cuisine_ids' => $request->input('cuisine_ids', []),
        ]);

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
        $filter = Filter::with(['tags', 'dietaries', 'cuisines'])->findOrFail($id);
        $tags = Tag::all();
        $dietaries = Dietary::all();
        $cuisines = Cuisine::all();

        return view('admin.filter.edit', compact('filter', 'tags', 'dietaries', 'cuisines'));
    }

    public function update(FilterRequest $request, $id)
    {
        $request->merge([
            'tag_ids' => $request->input('tag_ids', []),
            'dietary_ids' => $request->input('dietary_ids', []),
            'cuisine_ids' => $request->input('cuisine_ids', []),
        ]);

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
            return redirect()->route('admin.filter.index')->with('success', 'Filter deleted successfully.');
        } else {
            return redirect()->back()->withErrors(['error' => 'Failed to delete filter.']);
        }
    }
}

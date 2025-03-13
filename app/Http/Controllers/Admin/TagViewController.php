<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\WebController;
use App\Http\Controllers\Api\TagController as ApiTagController;
use App\Tag;
use App\Http\Resources\TagResource;
use Illuminate\Http\Request;

class TagViewController extends WebController
{
    protected $apiTagController;

    public function __construct(ApiTagController $apiTagController)
    {
        $this->apiTagController = $apiTagController;
    }

    public function index()
    {
        $tags = $this->apiTagController->index()->toArray(request());
        $tags = array_map(function ($tag) {
            $tag['posts_count'] = Tag::find($tag['id'])->posts()->count();
            return $tag;
        }, $tags);
        return view('admin.tag.index', [
            'tags' => $tags,
            'title' => 'Tags',
            'breadcrumb' => breadcrumb([
                'Tags' => route('admin.tag.index'),
            ]),
        ]);
    }

    public function show($id)
    {
        $tag = $this->apiTagController->show(Tag::findOrFail($id))->toArray(request());

        return view('admin.tag.show', compact('tag'));
    }

    public function create()
    {
        return view('admin.tag.create');
    }

    public function store(Request $request)
    {
        $response = $this->apiTagController->store($request);

        if ($response->getStatusCode() === 201) {
            return redirect()->route('admin.tag.index')->with('success', 'Tag created successfully.');
        } else {
            $error = $response->json();
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to create tag.', 'api_error' => $error]);
        }
    }

    public function edit($id)
    {
        $tag = $this->apiTagController->show(Tag::findOrFail($id))->toArray(request());

        return view('admin.tag.edit', compact('tag'));
    }

    public function update(Request $request, $id)
    {
        $tag = Tag::findOrFail($id);

        $response = $this->apiTagController->update($request, $tag);

        if ($response instanceof TagResource) {
            return redirect()->route('admin.tag.index')->with('success', 'Tag updated successfully.');
        } else {
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to update tag.']);
        }
    }

    public function destroy($id)
    {
        $tag = Tag::findOrFail($id);
        $response = $this->apiTagController->destroy($tag);

        if ($response->getStatusCode() === 204) {
            return redirect()->route('admin.tag.index')->with('success', 'Tag deleted successfully.');
        } else {
            return redirect()->back()->withErrors(['error' => 'Failed to delete tag.']);
        }
    }
}

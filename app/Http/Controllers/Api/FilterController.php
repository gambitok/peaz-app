<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilterRequest;
use App\Filter;

class FilterController extends Controller
{
    public function index()
    {
        return response()->json(Filter::with('tags')->get());
    }

    public function store(FilterRequest $request)
    {
        $data = $request->only('name');
        $filter = Filter::create($data);
        $filter->tags()->sync($request->tag_ids);

        return response()->json(['message' => 'Filter created', 'filter' => $filter->load('tags')], 201);
    }

    public function show($id)
    {
        $filter = Filter::with('tags')->findOrFail($id);
        return response()->json($filter);
    }

    public function update(FilterRequest $request, $id)
    {
        $filter = Filter::findOrFail($id);

        if ($request->has('name')) {
            $filter->name = $request->name;
        }

        $filter->save();

        if ($request->has('tag_ids')) {
            $filter->tags()->sync($request->tag_ids);
        }

        return response()->json(['message' => 'Filter updated', 'filter' => $filter->load('tags')]);
    }

    public function destroy($id)
    {
        $filter = Filter::findOrFail($id);
        $filter->tags()->detach();
        $filter->delete();

        return response()->json(['message' => 'Filter deleted']);
    }
}

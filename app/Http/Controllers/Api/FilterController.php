<?php

namespace App\Http\Controllers\Api;

use App\Filter;
use App\Http\Controllers\Controller;
use App\Http\Requests\FilterRequest;
use App\Http\Resources\FilterResource;

class FilterController extends Controller
{
    public function index()
    {
        $filters = Filter::with('tags')->get();
        return FilterResource::collection($filters);
    }

    public function store(FilterRequest $request)
    {
        $data = $request->only('name');
        $filter = Filter::create($data);
        $filter->tags()->sync($request->tag_ids);

        return response(new FilterResource($filter->load('tags')), 201);
    }

    public function show(Filter $filter)
    {
        $filter->load('tags');
        return new FilterResource($filter);
    }

    public function update(FilterRequest $request, Filter $filter)
    {
        if ($request->has('name')) {
            $filter->name = $request->name;
        }

        $filter->save();

        if ($request->has('tag_ids')) {
            $filter->tags()->sync($request->tag_ids);
        }

        return new FilterResource($filter->load('tags'));
    }

    public function destroy(Filter $filter)
    {
        $filter->tags()->detach();
        $filter->delete();

        return response()->noContent();
    }
}

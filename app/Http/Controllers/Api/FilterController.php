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
        $filters = Filter::with(['tags', 'dietaries', 'cuisines'])->get();
        return FilterResource::collection($filters);
    }

    public function store(FilterRequest $request)
    {
        $data = $request->only('name');
        $filter = Filter::create($data);

        $filter->tags()->sync($request->tag_ids);
        $filter->dietaries()->sync($request->dietary_ids);
        $filter->cuisines()->sync($request->cuisine_ids);

        return response(new FilterResource($filter->load(['tags', 'dietaries', 'cuisines'])), 201);
    }

    public function show(Filter $filter)
    {
        $filter->load(['tags', 'dietaries', 'cuisines']);
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

        if ($request->has('dietary_ids')) {
            $filter->dietaries()->sync($request->dietary_ids);
        }

        if ($request->has('cuisine_ids')) {
            $filter->cuisines()->sync($request->cuisine_ids);
        }

        return new FilterResource($filter->load(['tags', 'dietaries', 'cuisines']));
    }

    public function destroy(Filter $filter)
    {
        $filter->tags()->detach();
        $filter->dietaries()->detach();
        $filter->cuisines()->detach();

        $filter->delete();

        return response()->noContent();
    }
}

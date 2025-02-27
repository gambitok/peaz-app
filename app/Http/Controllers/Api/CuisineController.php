<?php

namespace App\Http\Controllers\Api;

use App\Cuisine;
use App\Http\Controllers\Controller;
use App\Http\Resources\CuisineResource;
use Illuminate\Http\Request;

class CuisineController extends Controller
{
    public function index()
    {
        $cuisines = Cuisine::all();

        return CuisineResource::collection($cuisines);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $Cuisine = Cuisine::create($validatedData);
        return response(new CuisineResource($Cuisine), 201);
    }

    public function show(Cuisine $Cuisine)
    {
        return new CuisineResource($Cuisine);
    }

    public function update(Request $request, Cuisine $Cuisine)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $Cuisine->update($validatedData);
        return new CuisineResource($Cuisine);
    }

    public function destroy(Cuisine $Cuisine)
    {
        $Cuisine->delete();
        return response()->noContent();
    }
}

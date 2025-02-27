<?php

namespace App\Http\Controllers\Api;

use App\Dietary;
use App\Http\Controllers\Controller;
use App\Http\Resources\DietaryResource;
use Illuminate\Http\Request;

class DietaryController extends Controller
{
    public function index()
    {
        $dietaries = Dietary::all();

        return DietaryResource::collection($dietaries);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $Dietary = Dietary::create($validatedData);
        return response(new DietaryResource($Dietary), 201);
    }

    public function show(Dietary $Dietary)
    {
        return new DietaryResource($Dietary);
    }

    public function update(Request $request, Dietary $Dietary)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $Dietary->update($validatedData);
        return new DietaryResource($Dietary);
    }

    public function destroy(Dietary $Dietary)
    {
        $Dietary->delete();
        return response()->noContent();
    }
}

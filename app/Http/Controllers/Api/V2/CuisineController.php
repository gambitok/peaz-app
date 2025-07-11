<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Cuisine;

class CuisineController extends Controller
{
    public function index()
    {
        $cuisines = Cuisine::all();
        return response()->json($cuisines);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
        ]);

        $cuisine = Cuisine::create($validatedData);

        return response()->json($cuisine, 201);
    }

    public function show($id)
    {
        $cuisine = Cuisine::find($id);
        if (!$cuisine) {
            return response()->json(['message' => 'Cuisine not found'], 404);
        }
        return response()->json($cuisine);
    }

    public function update(Request $request, $id)
    {
        $cuisine = Cuisine::find($id);
        if (!$cuisine) {
            return response()->json(['message' => 'Cuisine not found'], 404);
        }

        $validatedData = $request->validate([
            'name' => 'required|string',
        ]);

        $cuisine->update($validatedData);

        return response()->json($cuisine);
    }

    public function destroy($id)
    {
        $cuisine = Cuisine::find($id);
        if (!$cuisine) {
            return response()->json(['message' => 'Cuisine not found'], 404);
        }

        $cuisine->delete();

        return response()->json(['message' => 'Cuisine deleted']);
    }
}

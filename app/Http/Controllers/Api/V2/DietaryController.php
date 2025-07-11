<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Dietary;

class DietaryController extends Controller
{
    public function index()
    {
        $dietaries = Dietary::all();
        return response()->json($dietaries);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
        ]);

        $dietary = Dietary::create($validatedData);

        return response()->json($dietary, 201);
    }

    public function show($id)
    {
        $dietary = Dietary::find($id);
        if (!$dietary) {
            return response()->json(['message' => 'Dietary not found'], 404);
        }
        return response()->json($dietary);
    }

    public function update(Request $request, $id)
    {
        $dietary = Dietary::find($id);
        if (!$dietary) {
            return response()->json(['message' => 'Dietary not found'], 404);
        }

        $validatedData = $request->validate([
            'name' => 'required|string',
        ]);

        $dietary->update($validatedData);

        return response()->json($dietary);
    }

    public function destroy($id)
    {
        $dietary = Dietary::find($id);
        if (!$dietary) {
            return response()->json(['message' => 'Dietary not found'], 404);
        }

        $dietary->delete();

        return response()->json(['message' => 'Dietary deleted']);
    }
}

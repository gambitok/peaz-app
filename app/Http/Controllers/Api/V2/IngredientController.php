<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\IngredientResource;
use Illuminate\Http\Request;
use App\Ingredient;
use Illuminate\Support\Facades\Log;

class IngredientController extends Controller
{

    public function index(Request $request)
    {
        $query = Ingredient::query();

        if ($request->has('post_id')) {
            $query->where('post_id', $request->input('post_id'));
        }

        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        if ($request->has('limit')) {
            $limit = intval($request->input('limit'));
            $ingredients = $query->limit($limit)->get();
            return response()->json(IngredientResource::collection($ingredients), 200);
        }

        $perPage = $request->input('per_page', 15);
        $ingredients = $query->paginate($perPage);

        return IngredientResource::collection($ingredients)->response();
    }

    public function show($id)
    {
        $ingredient = Ingredient::find($id);

        if (!$ingredient) {
            return response()->json(['status' => 'error', 'message' => 'Ingredient not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'ingredient' => $ingredient
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'post_id' => 'required|integer|exists:posts,id',
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'measurement' => 'nullable|string|max:255',
            'user_id' => 'nullable|integer|exists:users,id',
        ]);

        $userId = $validated['user_id'] ?? auth()->id();

        if (!$userId) {
            return response()->json(['status' => 'error', 'message' => 'User not authenticated'], 401);
        }

        $ingredient = Ingredient::create([
            'user_id' => $userId,
            'post_id' => $validated['post_id'],
            'name' => $validated['name'],
            'type' => $validated['type'] ?? '',
            'measurement' => $validated['measurement'] ?? '',
        ]);

        return response()->json([
            'status' => 'success',
            'ingredient' => $ingredient
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'post_id' => 'required|integer|exists:posts,id',
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'measurement' => 'nullable|string|max:255',
            'user_id' => 'nullable|integer|exists:users,id',
        ]);

        $ingredient = Ingredient::find($id);

        if (!$ingredient) {
            return response()->json(['status' => 'error', 'message' => 'Ingredient not found'], 404);
        }

        $userId = $validated['user_id'] ?? auth()->id();

        if (!$userId) {
            return response()->json(['status' => 'error', 'message' => 'User not authenticated'], 401);
        }

        $ingredient->fill([
            'user_id' => $userId,
            'post_id' => $validated['post_id'],
            'name' => $validated['name'],
            'type' => $validated['type'] ?? '',
            'measurement' => $validated['measurement'] ?? '',
        ])->save();

        return response()->json([
            'status' => 'success',
            'ingredient' => $ingredient
        ]);
    }

    public function destroy($id)
    {
        $ingredient = Ingredient::find($id);

        if (!$ingredient) {
            return response()->json(['status' => 'error', 'message' => 'Ingredient not found'], 404);
        }

        $ingredient->delete();

        return response()->json(['status' => 'success', 'message' => 'Ingredient deleted']);
    }
}

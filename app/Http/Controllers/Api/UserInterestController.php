<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\UserInterest;
use Illuminate\Http\Request;

class UserInterestController extends Controller
{
    public function index()
    {
        return UserInterest::with(['tags', 'dietaries', 'cuisines'])->get();
    }

    public function show($id)
    {
        return UserInterest::with(['tags', 'dietaries', 'cuisines'])->findOrFail($id);
    }

    public function byUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $interest = \App\UserInterest::with(['tags', 'dietaries', 'cuisines'])
            ->where('user_id', $request->query('user_id'))
            ->first();

        if (!$interest) {
            return response()->json(['message' => 'No interests found for this user.'], 404);
        }

        return response()->json($interest);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'dietaries' => 'nullable|array',
            'dietaries.*' => 'exists:dietaries,id',
            'cuisines' => 'nullable|array',
            'cuisines.*' => 'exists:cuisines,id',
        ]);

        $userId = $request->user()->id;

        $existingInterest = UserInterest::where('user_id', $userId)->first();
        if ($existingInterest) {
            return response()->json([
                'error' => 'User interests are already recorded.'
            ], 409);
        }

        $userInterest = UserInterest::create([
            'user_id' => $userId,
        ]);

        $userInterest->tags()->attach($validated['tags'] ?? []);
        $userInterest->dietaries()->attach($validated['dietaries'] ?? []);
        $userInterest->cuisines()->attach($validated['cuisines'] ?? []);

        return response()->json($userInterest->load(['tags', 'dietaries', 'cuisines']), 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'dietaries' => 'nullable|array',
            'dietaries.*' => 'exists:dietaries,id',
            'cuisines' => 'nullable|array',
            'cuisines.*' => 'exists:cuisines,id',
        ]);

        $userInterest = UserInterest::findOrFail($id);

        if ($userInterest->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $userInterest->tags()->sync($validated['tags'] ?? []);
        $userInterest->dietaries()->sync($validated['dietaries'] ?? []);
        $userInterest->cuisines()->sync($validated['cuisines'] ?? []);

        return $userInterest->load(['tags', 'dietaries', 'cuisines']);
    }

    public function destroy($id)
    {
        $userInterest = UserInterest::findOrFail($id);
        $userInterest->tags()->detach();
        $userInterest->dietaries()->detach();
        $userInterest->cuisines()->detach();
        $userInterest->delete();

        return response()->noContent();
    }
}

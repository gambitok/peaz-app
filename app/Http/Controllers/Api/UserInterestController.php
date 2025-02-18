<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\UserInterest;
use Illuminate\Http\Request;

class UserInterestController extends Controller
{
    public function index()
    {
        return UserInterest::all();
    }

    public function show($id)
    {
        return UserInterest::findOrFail($id);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tags' => 'nullable|array',
            'dietaries' => 'nullable|array',
            'cuisines' => 'nullable|array',
        ]);

        $userId = $request->user()->id;

        // Check if the user's interests already exist
        $existingInterest = UserInterest::where('user_id', $userId)->first();

        if ($existingInterest) {
            return response()->json([
                'error' => 'User interests are already recorded.'
            ], 409);
        }

        $validated['user_id'] = $userId;

        return UserInterest::create($validated);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'tags' => 'nullable|array',
            'dietaries' => 'nullable|array',
            'cuisines' => 'nullable|array',
        ]);

        $validated['user_id'] = $request->user()->id;

        $userInterest = UserInterest::findOrFail($id);
        $userInterest->update($validated);

        return $userInterest;
    }

    public function destroy($id)
    {
        $userInterest = UserInterest::findOrFail($id);
        $userInterest->delete();

        return response()->noContent();
    }
}

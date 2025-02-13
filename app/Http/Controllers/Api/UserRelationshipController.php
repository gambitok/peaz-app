<?php

namespace App\Http\Controllers\Api;

use App\UserRelationship;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserRelationshipResource;
use Illuminate\Http\Request;


class UserRelationshipController extends Controller
{
    public function index()
    {
        return UserRelationshipResource::collection(UserRelationship::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'follower_id' => 'required|exists:users,id',
            'following_id' => 'required|exists:users,id',
        ]);

        $relationship = UserRelationship::create($validated);

        return new UserRelationshipResource($relationship);
    }

    public function show($id)
    {
        $relationship = UserRelationship::findOrFail($id);
        return new UserRelationshipResource($relationship);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'follower_id' => 'required|exists:users,id',
            'following_id' => 'required|exists:users,id',
        ]);

        $relationship = UserRelationship::findOrFail($id);
        $relationship->update($validated);

        return new UserRelationshipResource($relationship);
    }

    public function destroy($id)
    {
        $relationship = UserRelationship::findOrFail($id);
        $relationship->delete();

        return response()->json(null, 204);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\User;
use App\UserRelationship;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserRelationshipResource;
use Illuminate\Http\Request;
//“Following” is the term for the users who you follow. "Followers" are the users who follow you

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


    public function follow(Request $request)
    {
        $validated = $request->validate([
            'following_id' => 'required|exists:users,id',
        ]);

        if ($request->user()->id == $validated['following_id']) {
            return response()->json([
                'error' => 'You cannot subscribe to yourself.'
            ], 400);
        }

        // Check if the relationship already exists
        $existingRelationship = UserRelationship::where('follower_id', $request->user()->id)
            ->where('following_id', $validated['following_id'])
            ->first();

        if ($existingRelationship) {
            return response()->json([
                'error' => 'You cannot subscribe twice.'
            ], 409);
        }

        $relationship = UserRelationship::create([
            'follower_id' => $request->user()->id,
            'following_id' => $validated['following_id'],
        ]);

        return new UserRelationshipResource($relationship);
    }

    public function unfollow(Request $request, $following_id)
    {
        $relationship = UserRelationship::where('follower_id', $request->user()->id)
            ->where('following_id', $following_id)
            ->first();

        if (!$relationship) {
            return response()->json([
                'error' => 'You are not following this user.'
            ], 400);
        }

        $relationship->delete();

        return response()->json(null, 204);
    }

    public function getFollowers(Request $request)
    {
        $userId = $request->user()->id;
        $searchTerm = $request->query('search_text');

        $followers = UserRelationship::where('following_id', $userId)
            ->pluck('follower_id')
            ->toArray();

        $usersQuery = User::whereIn('id', $followers)
            ->select('id', 'name', 'username', 'profile_image', 'bio', 'website', 'verified');

        if ($searchTerm) {
            $usersQuery->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('username', 'like', '%' . $searchTerm . '%');
            });
        }

        $users = $usersQuery->get()
            ->map(function ($user) use ($userId) {
                $isFollowing = UserRelationship::where('follower_id', $userId)
                    ->where('following_id', $user->id)
                    ->exists();

                $user->is_following = $isFollowing ? 1 : 0;
                return $user;
            });

        return response()->json([
            'status' => 'success',
            'data' => $users
        ]);
    }

    public function getFollowing(Request $request)
    {
        $userId = $request->user()->id;
        $searchTerm = $request->query('search_text');

        $following = UserRelationship::where('follower_id', $userId)
            ->pluck('following_id')
            ->toArray();

        $usersQuery = User::whereIn('id', $following)
            ->select('id', 'name', 'username', 'profile_image', 'bio', 'website', 'verified');

        if ($searchTerm) {
            $usersQuery->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('username', 'like', '%' . $searchTerm . '%');
            });
        }

        $users = $usersQuery->get();

        return response()->json([
            'status' => 'success',
            'data' => $users
        ]);
    }

}

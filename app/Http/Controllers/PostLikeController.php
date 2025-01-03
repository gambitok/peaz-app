<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostLikeController
{

    public function getLikesGroupedByCuisines(Request $request)
    {
        $userId = $request->input('user_id');

        if (!$userId) {
            return response()->json([
                'status' => 'error',
                'message' => 'User ID is required'
            ], 400);
        }

        $cuisines = $request->input('cuisines');

        $likesGroupedQuery = DB::table('postlikes')
            ->join('posts', 'postlikes.post_id', '=', 'posts.id')
            ->select(
                'posts.cuisines',
                DB::raw('GROUP_CONCAT(postlikes.post_id) AS post_ids'),
                DB::raw('COUNT(posts.id) AS totalCount')
            )
            ->where('postlikes.user_id', $userId)
            ->groupBy('posts.cuisines');

        if ($cuisines) {
            $likesGroupedQuery->where('posts.cuisines', $cuisines);
        }

        $likesGrouped = $likesGroupedQuery->get();

        if ($likesGrouped->isNotEmpty()) {
            foreach ($likesGrouped as $group) {
                $posts = DB::table('posts')
                    ->whereIn('id', explode(',', $group->post_ids))
                    ->limit(3)
                    ->get();

                $group->posts = $posts;
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Liked posts grouped by cuisines with first 3 posts fetched successfully',
                'data' => $likesGrouped
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'No liked posts found'
            ], 404);
        }
    }

}

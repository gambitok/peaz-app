<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostLikeController extends Controller
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
        $sortField = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $perPage = $request->input('per_page', 10);

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

        $sortField = !empty($sortField) ? 'postlikes.' . $sortField : $sortField;

        $likesGroupedQuery->orderBy($sortField, $sortOrder);

        $likesGrouped = $likesGroupedQuery->paginate($perPage)->appends($request->except('page'));

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
                'data' => $likesGrouped->items(),
                'pagination' => [
                    'total' => $likesGrouped->total(),
                    'per_page' => $likesGrouped->perPage(),
                    'current_page' => $likesGrouped->currentPage(),
                    'last_page' => $likesGrouped->lastPage(),
                    'from' => $likesGrouped->firstItem(),
                    'to' => $likesGrouped->lastItem(),
                    'path' => $likesGrouped->path(),
                    'last_page_url' => $likesGrouped->url($likesGrouped->lastPage()),
                    'next_page_url' => $likesGrouped->nextPageUrl()
                ]
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'No liked posts found'
            ], 404);
        }
    }

    public function getLikes(Request $request)
    {
        $userId = $request->input('user_id');

        if (!$userId) {
            return response()->json([
                'status' => 'error',
                'message' => 'User ID is required'
            ], 400);
        }

        $cuisines = $request->input('cuisines');
        $sortField = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $perPage = $request->input('per_page', 10);

        $likesQuery = DB::table('postlikes')
            ->join('posts', 'postlikes.post_id', '=', 'posts.id')
            ->select('posts.*')
            ->where('postlikes.user_id', $userId);

        if ($cuisines) {
            $likesQuery->where('posts.cuisines', $cuisines);
        }

        $posts = $likesQuery->orderBy($sortField, $sortOrder)
            ->paginate($perPage)
            ->appends($request->except('page'));

        if ($posts->isNotEmpty()) {
            $likesWithPosts = [];
            foreach ($posts as $post) {
                $post->likes = DB::table('postlikes')
                    ->where('post_id', $post->id)
                    ->get();
                $likesWithPosts[] = $post;
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Liked posts fetched successfully',
                'data' => $likesWithPosts,
                'pagination' => [
                    'total' => $posts->total(),
                    'per_page' => $posts->perPage(),
                    'current_page' => $posts->currentPage(),
                    'last_page' => $posts->lastPage(),
                    'from' => $posts->firstItem(),
                    'to' => $posts->lastItem(),
                    'path' => $posts->path(),
                    'last_page_url' => $posts->url($posts->lastPage()),
                    'next_page_url' => $posts->nextPageUrl()
                ]
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'No liked posts found'
            ], 404);
        }
    }

}

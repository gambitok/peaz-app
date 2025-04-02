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
        $status = $request->input('status');
        $sortField = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $perPage = $request->input('per_page', 10);
        $awsUrl = env('AWS_URL', 'https://peazapi.s3.amazonaws.com');

        $likesGroupedQuery = DB::table('postlikes')
            ->join('posts', 'postlikes.post_id', '=', 'posts.id')
            ->join('post_cuisine', 'posts.id', '=', 'post_cuisine.post_id')
            ->join('cuisines', 'post_cuisine.cuisine_id', '=', 'cuisines.id')
            ->select(
                'cuisines.id as cuisine_id',
                'cuisines.name as cuisine_name',
                DB::raw('GROUP_CONCAT(postlikes.post_id) AS post_ids'),
                DB::raw('COUNT(posts.id) AS totalCount')
            )
            ->where('postlikes.user_id', $userId)
            ->groupBy('cuisines.id', 'cuisines.name');

        if ($cuisines) {
            $likesGroupedQuery->where('cuisines.name', 'LIKE', '%' . $cuisines . '%');
        }

        if ($status) {
            $likesGroupedQuery->where('posts.status', $status);
        }

        $sortField = !empty($sortField) ? 'postlikes.' . $sortField : $sortField;

        $likesGroupedQuery->orderBy($sortField, $sortOrder);

        $likesGrouped = $likesGroupedQuery->paginate($perPage)->appends($request->except('page'));

        if ($likesGrouped->isNotEmpty()) {
            foreach ($likesGrouped as $group) {
                $posts = DB::table('posts')
                    ->whereIn('id', explode(',', $group->post_ids))
                    ->select('id', 'title', 'type', 'hours', 'minutes', 'file', 'status', 'verified', 'created_at')
                    ->limit(3)
                    ->get();

                foreach ($posts as $post) {
                    $post->file = strpos($post->file, $awsUrl) === 0 ? $post->file : rtrim($awsUrl, '/') . '/' . ltrim($post->file, '/');

                    $thumbnails = DB::table('post_thumbnails')
                        ->where('post_id', $post->id)
                        ->get(['thumbnail', 'type']);

                    foreach ($thumbnails as $thumbnail) {
                        $thumbnail->thumbnail = strpos($thumbnail->thumbnail, $awsUrl) === 0 ? $thumbnail->thumbnail : rtrim($awsUrl, '/') . '/' . ltrim($thumbnail->thumbnail, '/');
                    }

                    $post->thumbnails = $thumbnails;
                }

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


    public function getLikes(Request $request)
    {
        $userId = $request->input('user_id');

        if (!$userId) {
            return response()->json([
                'status' => 'error',
                'message' => 'User ID is required'
            ], 400);
        }

        $cuisineName = $request->input('cuisines');
        $dietaryName = $request->input('dietaries');
        $tagName = $request->input('tags');
        $sortField = 'posts.' . $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $perPage = $request->input('per_page', 10);

        $likesQuery = DB::table('postlikes')
            ->join('posts', 'postlikes.post_id', '=', 'posts.id')
            ->select(
                'posts.id',
                'posts.title',
                'posts.caption',
                'posts.serving_size',
                'posts.minutes',
                'posts.hours',
                'posts.type',
                'posts.file',
                'posts.thumbnail',
                'posts.user_id',
                DB::raw('(SELECT GROUP_CONCAT(DISTINCT tags.name SEPARATOR ", ") FROM post_tag LEFT JOIN tags ON post_tag.tag_id = tags.id WHERE post_tag.post_id = posts.id) as tags'),
                DB::raw('(SELECT GROUP_CONCAT(DISTINCT dietaries.name SEPARATOR ", ") FROM post_dietary LEFT JOIN dietaries ON post_dietary.dietary_id = dietaries.id WHERE post_dietary.post_id = posts.id) as dietaries'),
                DB::raw('(SELECT GROUP_CONCAT(DISTINCT cuisines.name SEPARATOR ", ") FROM post_cuisine LEFT JOIN cuisines ON post_cuisine.cuisine_id = cuisines.id WHERE post_cuisine.post_id = posts.id) as cuisines')
            )
            ->where('postlikes.user_id', $userId);

        if ($cuisineName) {
            $likesQuery->join('post_cuisine', 'posts.id', '=', 'post_cuisine.post_id')
                ->join('cuisines', 'post_cuisine.cuisine_id', '=', 'cuisines.id')
                ->where('cuisines.name', $cuisineName);
        }

        if ($dietaryName) {
            $likesQuery->join('post_dietary', 'posts.id', '=', 'post_dietary.post_id')
                ->join('dietaries', 'post_dietary.dietary_id', '=', 'dietaries.id')
                ->where('dietaries.name', $dietaryName);
        }

        if ($tagName) {
            $likesQuery->join('post_tag', 'posts.id', '=', 'post_tag.post_id')
                ->join('tags', 'post_tag.tag_id', '=', 'tags.id')
                ->where('tags.name', $tagName);
        }

        $posts = $likesQuery->orderBy($sortField, $sortOrder)
            ->paginate($perPage)
            ->appends($request->except('page'));

        if ($posts->isNotEmpty()) {
            $likesWithPosts = [];
            $awsUrl = env('AWS_URL', 'https://peazapi.s3.amazonaws.com');
            foreach ($posts as $post) {
                $post->likes = DB::table('postlikes')
                    ->where('post_id', $post->id)
                    ->get();

                $post->file = strpos($post->file, $awsUrl) === 0 ? $post->file : rtrim($awsUrl, '/') . '/' . ltrim($post->file, '/');

                $post->thumbnail = strpos($post->thumbnail, $awsUrl) === 0 ? $post->thumbnail : $awsUrl . $post->thumbnail;

                $thumbnails = DB::table('post_thumbnails')
                    ->where('post_id', $post->id)
                    ->get(['thumbnail', 'type']);

                foreach ($thumbnails as $thumbnail) {
                    $thumbnail->thumbnail = strpos($thumbnail->thumbnail, $awsUrl) === 0 ? $thumbnail->thumbnail : rtrim($awsUrl, '/') . '/' . ltrim($thumbnail->thumbnail, '/');
                }

                $post->thumbnails = $thumbnails;

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

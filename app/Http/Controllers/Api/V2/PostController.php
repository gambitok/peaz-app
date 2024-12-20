<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Post;

class PostController extends Controller
{
    /**
     * Display a listing of the posts.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $posts = Post::paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $posts
        ]);
    }

    /**
     * Store a newly created post in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'type' => 'required|string',
            'file' => 'nullable|string',
            'caption' => 'nullable|string',
            'tags' => 'nullable|string',
            'serving_size' => 'nullable|integer',
            'hours' => 'nullable|integer',
            'minutes' => 'nullable|integer',
            'dietary' => 'nullable|string',
            'cuisines' => 'nullable|string',
        ]);

        $post = Post::create($validatedData);

        return response()->json([
            'status' => 'success',
            'data' => $post
        ], 201);
    }

    /**
     * Display the specified post.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                'status' => 'error',
                'message' => 'Post not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $post
        ]);
    }

    /**
     * Update the specified post in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                'status' => 'error',
                'message' => 'Post not found'
            ], 404);
        }

        $validatedData = $request->validate([
            'user_id' => 'sometimes|integer',
            'title' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|max:50',
            'file' => 'sometimes|string|max:255|nullable',
            'thumbnail' => 'sometimes|string|max:255|nullable',
            'caption' => 'sometimes|string',
            'serving_size' => 'sometimes|integer',
            'hours' => 'sometimes|integer',
            'minutes' => 'sometimes|integer',
            'dietary' => 'sometimes|string|max:255|nullable',
            'tags' => 'sometimes|string|max:255',
            'not_interested' => 'sometimes|boolean',
            'cuisines' => 'sometimes|string|max:255|nullable',
        ]);

        if ($validatedData['dietary'] === 'undefined') {
            $validatedData['dietary'] = null;
        }

        $post->update($validatedData);

        return response()->json([
            'status' => 'success',
            'data' => $post
        ]);
    }

    /**
     * Remove the specified post from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                'status' => 'error',
                'message' => 'Post not found'
            ], 404);
        }

        $post->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Post deleted successfully'
        ]);
    }

    public function search(Request $request)
    {
        $query = Post::query();

        if ($request->filled('title')) {
            $query->where('title', 'LIKE', '%' . $request->input('title') . '%');
        }

        if ($request->filled('type')) {
            $query->where('type', 'LIKE', '%' . $request->input('type') . '%');
        }

        if ($request->filled('caption')) {
            $query->where('caption', 'LIKE', '%' . $request->input('caption') . '%');
        }

        $sortField = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $perPage = $request->input('per_page', 10);
        $posts = $query->orderBy($sortField, $sortOrder)->paginate($perPage);

        if ($posts->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No posts found',
                'data' => []
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $posts
        ]);
    }

}

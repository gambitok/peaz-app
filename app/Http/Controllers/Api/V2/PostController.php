<?php

namespace App\Http\Controllers\Api\V2;

use App\Comment;
use App\Http\Controllers\Controller;
use App\PostLike;
use Illuminate\Http\Request;
use App\Post;
use App\Instruction;
use App\Ingredient;

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
        $post = Post::with(['user', 'comment', 'postlike', 'report_statuses'])->find($id);

        if (!$post) {
            return response()->json([
                'status' => 'error',
                'message' => 'Post not found'
            ], 404);
        }

        // Додаємо додаткові поля
        $post = $this->addExtraFields($post);

        return response()->json([
            'status' => 'success',
            'data' => $post,
        ]);
    }

    protected function addExtraFields($post, $user = null)
    {
        $post->comment_count = $post->comment->count();
        $post->postlike_count = $post->postlike->count();
        $post->avg_rating = $post->comment->avg('rating') ?? '0';
        $post->is_rating = false;
        $post->is_like = false;
        $post->is_reported = false;

        if ($user) {
            $post->is_like = $post->postlike->where('user_id', $user->id)->exists();
            $post->is_reported = $post->report_statuses->where('user_id', $user->id)->exists();
        }

        return $post;
    }

    public function details($id)
    {
        $post = Post::with(['instructions', 'ingredients'])->find($id);

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
        // Initialize the query with eager loading of the user relationship
        $query = Post::with('user');

        // Apply filters based on request input
        if ($request->filled('title')) {
            $query->where('title', 'LIKE', '%' . $request->input('title') . '%');
        }

        if ($request->filled('type')) {
            $query->where('type', 'LIKE', '%' . $request->input('type') . '%');
        }

        if ($request->filled('caption')) {
            $query->where('caption', 'LIKE', '%' . $request->input('caption') . '%');
        }

        if ($request->filled('dietary')) {
            $query->where('dietary', 'LIKE', '%' . $request->input('dietary') . '%');
        }

        if ($request->filled('time')) {
            $inputTime = (int) $request->input('time');
            $query->whereRaw('(hours * 3600000000 + minutes * 60000000) <= ?', [$inputTime]);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // Sorting and pagination
        $sortField = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $perPage = $request->input('per_page', 10);

        // Execute the query and paginate the results
        $posts = $query->orderBy($sortField, $sortOrder)
            ->paginate($perPage)
            ->appends($request->except('page'));

        // Check if the result is empty
        if ($posts->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No posts found',
                'data' => []
            ]);
        }

        // Return the response with user information included
        return response()->json([
            'status' => 'success',
            'data' => $posts
        ]);
    }

    public function getUserPosts(Request $request)
    {
        $user = $request->user();

        $posts = $user->posts;

        return response()->json([
            'posts' => $posts
        ], 200);
    }

    public function getUserLikedPosts(Request $request)
    {
        $user = $request->user();

        $likedPosts = Post::with([
            'user' => function ($q) {
                $q->select("id", "username", "profile_image");
            },
            'ingredient' => function ($q) {
                $q->select("id", "post_id", "name", "type", "measurement");
            },
            'instruction',
            'comment' => function ($q) {
                $q->withCount(["commentlike"]);
                $q->with(["user" => function ($q) {
                    $q->select("id", "username", "profile_image");
                }]);
            },
            'postlike'
        ])
            ->whereHas('postlike', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json([
            'liked_posts' => $likedPosts
        ], 200);
    }

    public function getUserRecipesAndComments(Request $request)
    {
        $user = $request->user();

        $recipes = Post::with([
            'user' => function ($q) {
                $q->select("id", "username", "profile_image");
            },
            'ingredient' => function ($q) {
                $q->select("id", "post_id", "name", "type", "measurement");
            },
            'instruction',
            'comment' => function ($q) {
                $q->withCount(["commentlike"]);
                $q->with(["user" => function ($q) {
                    $q->select("id", "username", "profile_image");
                }]);
            },
            'postlike'
        ])
            ->AvgRating()
            ->IsRating($user->id ?? 0)
            ->where('user_id', $user->id)
            ->orderBy('id', 'DESC')
            ->get();

        $comments = Comment::with([
            'reply' => function ($query) use ($request) {
                $query->withCount(["replylike"]);
                $query->with(["user" => function ($q) {
                    $q->select("id", "username", "profile_image");
                }]);
            },
            'user' => function ($q) {
                $q->select("id", "username", "profile_image");
            }
        ])
            ->whereHas('post', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->whereNull("comment_id")
            ->get();

        return response()->json([
            'recipes' => $recipes,
            'comments' => $comments
        ], 200);
    }

    public function postLike(Request $request)
    {
        $user = $request->user();

        $rules = [
            'post_id' => ['required', 'exists:posts,id'],
        ];

        $request->validate($rules);

        $postLike = PostLike::where('user_id', $user->id)->where('post_id', $request->post_id)->first();

        if ($postLike) {
            $postLike->delete();
            return response()->json(['message' => __('api.suc_dislike')], 200);
        } else {
            $like = PostLike::create([
                'user_id' => $user->id,
                'post_id' => $request->post_id,
            ]);

            if ($like) {
                return response()->json(['message' => __('api.suc_like'), 'data' => $like], 200);
            } else {
                return response()->json(['message' => __('api.err_like')], 500);
            }
        }
    }

    public function commentLike(Request $request)
    {
        $user = $request->user();

        $rules = [
            'post_id' => ['required', 'exists:posts,id'],
            'comment_id' => ['required', 'exists:comments,id'],
        ];

        $request->validate($rules);

        $commentLike = CommentLike::where('user_id', $user->id)
            ->where('post_id', $request->post_id)
            ->where('comment_id', $request->comment_id)
            ->first();

        if ($commentLike) {
            $commentLike->delete();
            return response()->json(['message' => __('api.suc_comment_dislike')], 200);
        } else {
            $like = CommentLike::create([
                'user_id' => $user->id,
                'post_id' => $request->post_id,
                'comment_id' => $request->comment_id,
            ]);

            if ($like) {
                return response()->json(['message' => __('api.suc_comment_like'), 'data' => $like], 200);
            } else {
                return response()->json(['message' => __('api.err_comment_like')], 500);
            }
        }
    }

}

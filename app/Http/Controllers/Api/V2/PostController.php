<?php

namespace App\Http\Controllers\Api\V2;

use App\Comment;
use App\CommentLike;
use App\Http\Controllers\Controller;
use App\Ingredient;
use App\Instruction;
use App\PostLike;
use Illuminate\Http\Request;
use App\Post;
use App\Http\Controllers\Api\ResponseController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function  __construct()
    {
        $this->post_obj = new Post();
        $this->ingredient_obj = new Ingredient();
        $this->instruction_obj = new Instruction();
    }

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
            'thumbnail' => 'nullable|string',
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

        $post = $this->addExtraFields($post);

        return response()->json([
            'status' => 'success',
            'data' => $post,
        ]);
    }

    protected function addExtraFields($post, $user_id = null)
    {
        $post->comment_count = $post->comment->count();
        $post->postlike_count = $post->postlike->count();
        $post->avg_rating = $post->comment->avg('rating') ?? '0';
        $post->is_rating = false;
        $post->is_like = false;
        $post->is_reported = false;

        if ($user_id) {
            $post->is_like = $post->postlike()->where('user_id', $user_id)->exists();
            $post->is_reported = $post->report_statuses()->where('user_id', $user_id)->exists();
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
        $user = $request->user();

        $query = Post::with(['user', 'comment', 'postlike', 'report_statuses']);

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

        if ($request->filled('tags')) {
            $query->where('tags', 'LIKE', '%' . $request->input('tags') . '%');
        }

        if ($request->filled('time')) {
            $inputTime = (int) $request->input('time');
            $query->whereRaw('(hours * 3600000000 + minutes * 60000000) <= ?', [$inputTime]);
        }

        $user_id = null;
        if ($user) {
            $user_id = $user->id;
        }

        $sortField = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $perPage = $request->input('per_page', 10);

        $posts = $query->orderBy($sortField, $sortOrder)
            ->paginate($perPage)
            ->appends($request->except('page'));

        if ($posts->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No posts found',
                'data' => []
            ]);
        }

        $posts->getCollection()->transform(function ($post) use ($user_id) {
            return $this->addExtraFields($post, $user_id);
        });

        return response()->json([
            'status' => 'success',
            'data' => $posts
        ]);
    }

    public function userSearch(Request $request)
    {
        $query = Post::with(['user', 'comment', 'postlike', 'report_statuses']);

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

        if ($request->filled('tags')) {
            $query->where('tags', 'LIKE', '%' . $request->input('tags') . '%');
        }

        if ($request->filled('time')) {
            $inputTime = (int) $request->input('time');
            $query->whereRaw('(hours * 3600000000 + minutes * 60000000) <= ?', [$inputTime]);
        }

        $user_id = null;
        if ($request->filled('user_id')) {
            $user_id = $request->input('user_id');
            $query->where('user_id', '=', $user_id);
        }

        $sortField = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $perPage = $request->input('per_page', 10);

        $posts = $query->orderBy($sortField, $sortOrder)
            ->paginate($perPage)
            ->appends($request->except('page'));

        if ($posts->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No posts found',
                'data' => []
            ]);
        }

        $posts->getCollection()->transform(function ($post) use ($user_id) {
            return $this->addExtraFields($post, $user_id);
        });

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

    public function addIngredient(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => __('api.err_user_not_found')], 404);
        }

        $rules = [
            'name' => ['required'],
            'measurement' => ['required'],
            'post_id' => ['required', 'exists:posts,id'],
            'method' => ['required'],
            'ingredient_id' => ['required_if:method,edit'],
        ];

        (new \App\Http\Controllers\Api\ResponseController)->directValidation($rules);

        $data = null;
        $messages = __('api.suc_ingredient_create', ['order' => $request->order]);

        if ($request->ingredient_id > 0) {
            $data = $this->ingredient_obj->find($request->ingredient_id);
            if (!$data) {
                return response()->json(['error' => __('api.err_ingredient_not_found')], 404);
            }
            $messages = __('api.suc_ingredient_update');
        }

        $request_data = $request->all();
        $request_data['user_id'] = $user->id;
        $request_data['type'] = $request->type ?? '';
        $request_data['measurement'] = $request->measurement ?? '';
        $request_data['order'] = (int) ($request->order ?? 0);
        unset($request_data['method']);

        $post = $this->ingredient_obj->saveIngredient($request_data, 0, $data);
        if (!$post) {
            return response()->json(['error' => __('api.err_something_went_wrong')], 500);
        }

        return response()->json(['message' => $messages, 'data' => $post], 200);
    }

    public function addInstruction(Request $request)
    {
        $user = $request->user();

        $s3BaseUrl = Storage::disk('s3')->url('/');

        $file = $request->input('file');
        $thumbnail = $request->input('thumbnail');

        $fileUrl = $file && !filter_var($file, FILTER_VALIDATE_URL) ? $s3BaseUrl . ltrim($file, '/') : $file;
        $thumbnailUrl = $thumbnail && !filter_var($thumbnail, FILTER_VALIDATE_URL) ? $s3BaseUrl . ltrim($thumbnail, '/') : $thumbnail;

        $isFileUrl = filter_var($fileUrl, FILTER_VALIDATE_URL);
        $isThumbnailUrl = filter_var($thumbnailUrl, FILTER_VALIDATE_URL);

        $rules = [
            'title' => 'required',
            'file' => ['required_if:method,add', 'nullable', $isFileUrl ? 'string' : 'file', $isFileUrl ? '' : 'mimes:jpg,jpeg,png,gif,bmp,svg,webp,mp4,avi,wmv,mov,flv'],
            'description' => 'required',
            'post_id' => ['required', 'exists:posts,id'],
            'thumbnail' => ['required_if:method,add', 'nullable', $isThumbnailUrl ? 'string' : 'file', $isThumbnailUrl ? '' : 'mimes:jpg,jpeg,png,gif,bmp,svg,webp,mp4,avi,wmv,mov,flv'],
            'type' => ['required', 'in:video,image'],
            'method' => 'required',
            'instruction_id' => ['required_if:method,edit'],
        ];

        $messages = [
            'file.required_if' => 'The image field is required when adding a new instruction',
        ];

        $response = new ResponseController();
        $response->directValidation($rules, $messages);

        $data = null;
        $order = $request->order ?? '1';

        $messages = __('api.suc_instruction_create', ['order' => $order]);
        if ($request->instruction_id > 0) {
            $data = $this->instruction_obj->find($request->instruction_id);
            if (!empty($data)) {
                $data->getRawOriginal('thumbnail');
                $data->getRawOriginal('file');
            }
            $messages = __('api.suc_instruction_update');
        }

        if ($request->hasFile('file')) {
            $up = upload_file('file', 'user_instruction_image');
        } elseif ($isFileUrl) {
            $up = $file;
        } elseif ($request->input('file') && strpos($request->input('file'), '/uploads/posts/images/') === 0) {
            $up = $request->input('file');
        } else {
            return response()->json([
                'status' => 412,
                'message' => 'The file must be a valid file or URL.',
                'data' => []
            ]);
        }

        if ($request->hasFile('thumbnail')) {
            $upThumbnail = upload_file('thumbnail', 'user_instruction_thumbnail');
        } elseif ($isThumbnailUrl) {
            $upThumbnail = $thumbnail;
        } elseif ($request->input('thumbnail') && strpos($request->input('thumbnail'), '/uploads/posts/thumbnails/images/') === 0) {
            $upThumbnail = $request->input('thumbnail');
        } else {
            return response()->json([
                'status' => 412,
                'message' => __('api.err_invalid_thumbnail'),
                'data' => []
            ]);
        }

        $request_data = $request->all();
        $request_data['file'] = $up ?? '';
        $request_data['thumbnail'] = $upThumbnail ?? '';
        $request_data['user_id'] = $user->id;
        $request_data['order'] = (int) $order;

        if ($isFileUrl) {
            $fileExtension = pathinfo($fileUrl, PATHINFO_EXTENSION);
            $request_data['type'] = in_array($fileExtension, ['mp4', 'avi', 'wmv', 'mov', 'flv']) ? 'video' : 'image';
        } else {
            $request_data['type'] = (strpos($request->file('file')->getMimeType(), 'video') !== false) ? 'video' : 'image';
        }

        unset($request_data['method']);

        $post = $this->instruction_obj->saveInstruction($request_data, 0, $data);
        if ($post) {
            return response()->json([
                'status' => 200,
                'message' => $messages,
                'data' => $post
            ]);
        }
        return response()->json([
            'status' => 500,
            'message' => __('api.err_something_went_wrong'),
            'data' => false
        ]);
    }

    public function postCommentReview(Request $request)
    {
        try {
            $user = $request->user();

            $rules = [
                'post_id' => ['required', 'exists:posts,id'],
                'comment_id' => ['nullable', 'numeric', 'exists:comments,id'],
                'comment_text' => ['required_if:type,0'],
                'type' => ['required'],
                'rating' => ['required_if:type,1'],
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 412,
                    'message' => $validator->errors()->first(),
                    'data' => []
                ]);
            }

            $comment_details = Comment::create([
                "user_id" => $user->id,
                "post_id" => $request->post_id,
                "comment_id" => $request->comment_id,
                "comment_text" => $request->comment_text,
                "type" => $request->type,
                "rating" => $request->rating,
            ]);

            $comments = Comment::with([
                'reply' => function ($query) use ($request) {
                    $query->withCount(["replylike"]);
                    $query->with(["user" => function ($q) {
                        $q->select("id", "username", "profile_image");
                    }])
                        ->when(!is_null($request->type) && $request->type != "", function ($q) use ($request) {
                            $q->where('type', $request->type);
                        });
                },
                'user' => function ($q) {
                    $q->select("id", "username", "profile_image");
                }
            ])->where(function ($q) use ($request, $comment_details) {
                $q->where('id', $comment_details->id)->orwhere('id', $request->comment_id);
            })->whereNull("comment_id")
                ->get();

            $commentlikes = CommentLike::where('user_id', $user->id)
                ->where('post_id', $request->post_id)
                ->pluck('comment_id')->toArray();

            foreach ($comments as $comment) {
                $comment->is_commentlike = false;
                $comment->is_replylike = false;
                $comment->commentlike_count = count($commentlikes);
                if (in_array($comment->id, $commentlikes)) {
                    $comment->is_commentlike = true;
                }

                $rep_comments = $comment->reply;
                foreach ($rep_comments as $replay) {
                    $replay->is_replaylike = false;
                    if (in_array($replay->id, $commentlikes)) {
                        $replay->is_replaylike = true;
                    }
                }
            }

            if (!empty($comments)) {
                return response()->json([
                    'status' => 200,
                    'message' => __('api.suc_comment_create'),
                    'data' => $comments
                ]);
            } else {
                return response()->json([
                    'status' => 500,
                    'message' => __('api.err_comment'),
                    'data' => []
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 500,
                'message' => __('api.err_comment'),
                'data' => $th->getMessage()
            ]);
        }
    }

}

<?php

namespace App\Http\Controllers\Api\V2;

use App\Comment;
use App\CommentLike;
use App\Http\Controllers\Controller;
use App\PostIngredient;
use App\Instruction;
use App\PostLike;
use App\PostThumbnail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Post;
use App\Http\Controllers\Api\ResponseController;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PostController extends Controller
{
    public function  __construct()
    {
        $this->post_obj = new Post();
        $this->ingredient_obj = new PostIngredient();
        $this->instruction_obj = new Instruction();
    }

    /**
     * Display a listing of the posts.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $user = Auth::user();

        $sortOrder = request()->get('sort', 'desc');
        $validSortOrders = ['asc', 'desc'];

        if (!in_array(strtolower($sortOrder), $validSortOrders)) {
            $sortOrder = 'desc';
        }

        $statusFilter = request()->get('status');
        $verifiedFilter = request()->get('verified');

        $postsQuery = Post::with([
            'user' => function ($query) {
                $query->select('id', 'name', 'username', 'profile_image', 'bio', 'website', 'verified');
            },
            'tags',
            'dietaries',
            'cuisines',
            'comment',
            'postlike',
            'report_statuses',
            'thumbnails'
        ]);

        if ($statusFilter !== null) {
            $postsQuery->where('status', $statusFilter);
        }

        if ($verifiedFilter !== null) {
            $postsQuery->where('verified', (bool) $verifiedFilter);
        }

        $posts = $postsQuery->orderBy('created_at', $sortOrder)->get();

        $posts = $posts->map(function ($post) use ($user) {
            $postWithExtras = method_exists($this, 'addExtraFields')
                ? $this->addExtraFields($post, $user?->id)
                : $post;

            $postData = $postWithExtras->toArray();

            $postData['tags'] = $post->tags instanceof \Illuminate\Support\Collection
                ? $post->tags->map(function ($tag) {
                    return [
                        'id' => $tag->id,
                        'name' => $tag->name,
                    ];
                })->toArray()
                : [];

            $postData['dietaries'] = $post->dietaries instanceof \Illuminate\Support\Collection
                ? $post->dietaries->map(function ($dietary) {
                    return [
                        'id' => $dietary->id,
                        'name' => $dietary->name,
                    ];
                })->toArray()
                : [];

            $postData['cuisines'] = $post->cuisines instanceof \Illuminate\Support\Collection
                ? $post->cuisines->map(function ($cuisine) {
                    return [
                        'id' => $cuisine->id,
                        'name' => $cuisine->name,
                    ];
                })->toArray()
                : [];

            $postData['thumbnails'] = $post->thumbnails instanceof \Illuminate\Support\Collection
                ? $post->thumbnails->map(function ($thumbnail) {
                    return [
                        'thumbnail' => $thumbnail->thumbnail,
                        'type' => $thumbnail->type
                    ];
                })->toArray()
                : [];

            return $postData;
        });

        return response()->json([
            'status' => 'success',
            'data' => $posts
        ]);
    }

    /**
     * Store a newly created post in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        if (!$user || $user->verified != 1) {
            return response()->json(['error' => 'Your account must be verified to create a post.'], 403);
        }

        $rules = [
            'title' => 'required|string|max:100',
            'caption' => 'required|string|max:255',
            'serving_size' => 'required|integer',
            'minutes' => 'required|integer',
            'hours' => 'required|integer',
            'method' => 'nullable|string',
            'type' => 'required|string',
            'user_id' => 'required|integer',
            'file' => 'required|string',
            'thumbnail' => 'required|string',
            'tags' => 'nullable|array',
            'tags.*' => 'integer|exists:tags,id',
            'dietaries' => 'nullable|array',
            'dietaries.*' => 'integer|exists:dietaries,id',
            'cuisines' => 'nullable|array',
            'cuisines.*' => 'integer|exists:cuisines,id',
            'thumbnails' => 'nullable|array',
            'thumbnails.*.file' => 'required|string',
            'thumbnails.*.thumbnail' => 'required|string',
            'thumbnails.*.title' => 'nullable|string|max:255',
            'thumbnails.*.description' => 'nullable|string|max:1000',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $validatedData = $validator->validated();

        $post = Post::create($validatedData);

        if (!empty($validatedData['tags'])) {
            $post->tags()->attach($validatedData['tags']);
        }

        if (!empty($validatedData['dietaries'])) {
            $post->dietaries()->attach($validatedData['dietaries']);
        }

        if (!empty($validatedData['cuisines'])) {
            $post->cuisines()->attach($validatedData['cuisines']);
        }

        if (!empty($validatedData['thumbnails'])) {
            $imageExtensions = ['jpeg', 'png', 'jpg', 'gif'];
            $videoExtensions = ['mp4', 'avi', 'mov', 'mkv', 'wmv', 'flv'];

            foreach ($validatedData['thumbnails'] as $thumb) {
                $fileExt = strtolower(pathinfo($thumb['file'], PATHINFO_EXTENSION));
                $thumbExt = strtolower(pathinfo($thumb['thumbnail'], PATHINFO_EXTENSION));

                $fileType = in_array($fileExt, $imageExtensions) ? 'image' : (in_array($fileExt, $videoExtensions) ? 'video' : null);
                $thumbType = in_array($thumbExt, $imageExtensions) ? 'image' : (in_array($thumbExt, $videoExtensions) ? 'video' : null);

                if (!$fileType || !$thumbType) {
                    return response()->json(['error' => 'Unsupported file format in thumbnails'], 400);
                }

                PostThumbnail::create([
                    'post_id' => $post->id,
                    'file' => $thumb['file'],
                    'thumbnail' => $thumb['thumbnail'],
                    'title' => $thumb['title'] ?? null,
                    'description' => $thumb['description'] ?? null,
                    'file_type' => $fileType,
                    'type' => $thumbType,
                ]);
            }
        }

        $post->load('tags', 'dietaries', 'cuisines', 'thumbnails');

        return response()->json([
            'post' => $post
        ], 201);
    }

    public function show($id)
    {
        $post = Post::with(['tags', 'dietaries', 'cuisines', 'thumbnails'])->find($id);

        if (!$post) {
            return response()->json([
                'status' => 'error',
                'message' => 'Post not found',
            ], 404);
        }

        $post = $this->addExtraFields($post, Auth::id());
        $postData = $post->toArray();

        $postData['tags'] = $post->tags instanceof Collection
            ? $post->tags->map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                ];
            })->toArray()
            : [];

        $postData['dietaries'] = $post->dietaries instanceof Collection
            ? $post->dietaries->map(function ($dietary) {
                return [
                    'id' => $dietary->id,
                    'name' => $dietary->name,
                ];
            })->toArray()
            : [];

        $postData['cuisines'] = $post->cuisines instanceof Collection
            ? $post->cuisines->map(function ($cuisine) {
                return [
                    'id' => $cuisine->id,
                    'name' => $cuisine->name,
                ];
            })->toArray()
            : [];

        $postData['thumbnails'] = $post->thumbnails instanceof Collection
            ? $post->thumbnails->map(function ($thumbnail) {
                return [
                    'thumbnail' => $thumbnail->thumbnail,
                    'type' => $thumbnail->type
                ];
            })->toArray()
            : [];

        return response()->json([
            'status' => 'success',
            'data' => $postData
        ]);
    }

    protected function addExtraFields($post, $user_id = null)
    {
        $post->comment_count = $post->comment->count();
        $post->postlike_count = $post->postlike->count();
        $post->avg_rating = number_format(
            $post->comment
                ->whereNotNull('rating')
                ->where('rating', '>', 0)
                ->avg('rating') ?? 0,
            1,
            '.',
            ''
        );
        $post->is_rating = $post->comment()
            ->where('user_id', $user_id)
            ->whereNotNull('rating')
            ->where('rating', '>', 0)
            ->exists();
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
        $post = Post::with([
            'instructions',
            'ingredients',
            'tags',
            'dietaries',
            'cuisines',
            'thumbnails'
        ])->find($id);

        if (!$post) {
            return response()->json([
                'status' => 'error',
                'message' => 'Post not found'
            ], 404);
        }

        if ($post->tags && $post->tags instanceof Collection) {
            $post->tags = $post->tags->pluck('name');
        }

        if ($post->dietaries && $post->dietaries instanceof Collection) {
            $post->dietaries = $post->dietaries->pluck('name');
        }

        if ($post->cuisines && $post->cuisines instanceof Collection) {
            $post->cuisines = $post->cuisines->pluck('name');
        }

        if ($post->thumbnails && $post->thumbnails instanceof Collection) {
            $post->thumbnails = $post->thumbnails->map(function ($thumbnail) {
                return [
                    'thumbnail' => $thumbnail->thumbnail,
                    'type' => $thumbnail->type
                ];
            });
        }

        return response()->json([
            'status' => 'success',
            'data' => $post
        ]);
    }

    /**
     * Update the specified post in storage.
     *
     * @param int $id
     * @return JsonResponse
     * @throws ValidationException
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

        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        $videoExtensions = ['mp4', 'avi', 'mov', 'mkv', 'wmv', 'flv'];

        $rules = [
            'title' => 'required|string|max:100',
            'caption' => 'nullable|string|max:255',
            'serving_size' => 'nullable|integer',
            'minutes' => 'nullable|integer',
            'hours' => 'nullable|integer',
            'method' => 'nullable|string',
            'type' => 'nullable|string',
            'user_id' => 'nullable|integer',
            'file' => 'nullable|string',
            'thumbnail' => 'nullable|string',
            'thumbnails' => 'nullable|array|max:4',
            'thumbnails.*.file' => 'nullable|string',
            'thumbnails.*.thumbnail' => 'nullable|string',
            'thumbnails.*.title' => 'nullable|string|max:255',
            'thumbnails.*.description' => 'nullable|string|max:1000',
            'tags' => 'nullable|array',
            'tags.*' => 'integer|exists:tags,id',
            'dietaries' => 'nullable|array',
            'dietaries.*' => 'integer|exists:dietaries,id',
            'cuisines' => 'nullable|array',
            'cuisines.*' => 'integer|exists:cuisines,id',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $validated = $validator->validated();

        $post->update([
            'title' => $validated['title'],
            'caption' => $validated['caption'] ?? $post->caption,
            'serving_size' => $validated['serving_size'] ?? $post->serving_size,
            'minutes' => $validated['minutes'] ?? $post->minutes,
            'hours' => $validated['hours'] ?? $post->hours,
            'method' => $validated['method'] ?? $post->method,
            'type' => $validated['type'] ?? $post->type,
            'user_id' => $validated['user_id'] ?? $post->user_id,
            'file' => isset($validated['file']) ? $validated['file'] : $post->file,
        ]);

        $post->tags()->sync($validated['tags'] ?? []);
        $post->dietaries()->sync($validated['dietaries'] ?? []);
        $post->cuisines()->sync($validated['cuisines'] ?? []);

        if (isset($validated['thumbnails'])) {
            $post->thumbnails()->delete();

            foreach ($validated['thumbnails'] as $thumb) {
                $fileExtension = strtolower(pathinfo($thumb['file'], PATHINFO_EXTENSION));
                $thumbnailExtension = strtolower(pathinfo($thumb['thumbnail'], PATHINFO_EXTENSION));

                $fileType = in_array($fileExtension, $videoExtensions) ? 'video' : (in_array($fileExtension, $imageExtensions) ? 'image' : null);
                $thumbType = in_array($thumbnailExtension, $videoExtensions) ? 'video' : (in_array($thumbnailExtension, $imageExtensions) ? 'image' : null);

                if (!$fileType || !$thumbType) {
                    return response()->json(['error' => 'Unsupported file or thumbnail type'], 400);
                }

                PostThumbnail::create([
                    'post_id' => $post->id,
                    'file' => $thumb['file'],
                    'thumbnail' => $thumb['thumbnail'],
                    'type' => $thumbType,
                    'file_type' => $fileType,
                    'title' => $thumb['title'] ?? null,
                    'description' => $thumb['description'] ?? null,
                ]);
            }
        }

        $post->load('tags', 'dietaries', 'cuisines', 'thumbnails');

        return response()->json($post, 200);
    }

    /**
     * Remove the specified post from storage.
     *
     * @param  int  $id
     * @return JsonResponse
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

        $post->tags()->detach();
        $post->dietaries()->detach();
        $post->cuisines()->detach();

        $post->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Post deleted successfully'
        ]);
    }

    public function search(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User must be authenticated.'
            ], 401);
        }

        $user_id = $user->id;

        $query = Post::with([
            'user' => function ($query) {
                $query->select('id', 'name', 'username', 'profile_image', 'bio', 'website', 'verified');
            },
            'comment',
            'postlike',
            'report_statuses',
            'thumbnails'
        ])
            ->leftJoin('post_tag', 'posts.id', '=', 'post_tag.post_id')
            ->leftJoin('tags', 'post_tag.tag_id', '=', 'tags.id')
            ->leftJoin('post_dietary', 'posts.id', '=', 'post_dietary.post_id')
            ->leftJoin('dietaries', 'post_dietary.dietary_id', '=', 'dietaries.id')
            ->leftJoin('post_cuisine', 'posts.id', '=', 'post_cuisine.post_id')
            ->leftJoin('cuisines', 'post_cuisine.cuisine_id', '=', 'cuisines.id')
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
                'posts.status',
                'posts.verified',
                DB::raw('(SELECT GROUP_CONCAT(DISTINCT tags.name SEPARATOR ", ") FROM post_tag LEFT JOIN tags ON post_tag.tag_id = tags.id WHERE post_tag.post_id = posts.id) as tags'),
                DB::raw('(SELECT GROUP_CONCAT(DISTINCT dietaries.name SEPARATOR ", ") FROM post_dietary LEFT JOIN dietaries ON post_dietary.dietary_id = dietaries.id WHERE post_dietary.post_id = posts.id) as dietaries'),
                DB::raw('(SELECT GROUP_CONCAT(DISTINCT cuisines.name SEPARATOR ", ") FROM post_cuisine LEFT JOIN cuisines ON post_cuisine.cuisine_id = cuisines.id WHERE post_cuisine.post_id = posts.id) as cuisines')
            )
            ->groupBy(
                'posts.id',
                'posts.title',
                'posts.caption',
                'posts.serving_size',
                'posts.minutes',
                'posts.hours',
                'posts.type',
                'posts.file',
                'posts.user_id',
                'posts.status',
                'posts.verified'
            );

        if ($request->filled('title')) {
            $query->where('posts.title', 'LIKE', '%' . $request->input('title') . '%');
        }
        if ($request->filled('type')) {
            $query->where('posts.type', 'LIKE', '%' . $request->input('type') . '%');
        }
        if ($request->filled('caption')) {
            $query->where('posts.caption', 'LIKE', '%' . $request->input('caption') . '%');
        }
        if ($request->filled('dietaries')) {
            $query->where('dietaries.name', 'LIKE', '%' . $request->input('dietaries') . '%');
        }
        if ($request->filled('tags')) {
            $query->where('tags.name', 'LIKE', '%' . $request->input('tags') . '%');
        }
        if ($request->filled('cuisines')) {
            $query->where('cuisines.name', 'LIKE', '%' . $request->input('cuisines') . '%');
        }
        if ($request->filled('time')) {
            $inputTime = (int) $request->input('time');
            $query->whereRaw('(posts.hours * 3600000000 + posts.minutes * 60000000) <= ?', [$inputTime]);
        }
        if ($request->filled('user_id')) {
            $query->where('posts.user_id', '=', $user_id);
        }
        if ($request->filled('status')) {
            $query->where('posts.status', '=', $request->input('status'));
        }

        $sortField = 'posts.' . $request->input('sort_by', 'created_at');
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
            $postData = $this->addExtraFields($post, $user_id);
            $postData['thumbnails'] = $post->thumbnails->map(function ($thumbnail) {
                return [
                    'thumbnail' => $thumbnail->thumbnail,
                    'type' => $thumbnail->type
                ];
            });
            return $postData;
        });

        return response()->json([
            'status' => 'success',
            'data' => $posts
        ]);
    }

    public function userSearch(Request $request)
    {
        if (!$request->filled('user_id')) {
            return response()->json([
                'status' => 'error',
                'message' => 'The user_id field is required.'
            ], 400);
        }

        $user_id = $request->input('user_id');

        $query = Post::with([
            'user' => function ($query) {
                $query->select('id', 'name', 'username', 'profile_image', 'bio', 'website');
            },
            'comment',
            'postlike',
            'report_statuses',
            'thumbnails'
        ])
            ->leftJoin('post_tag', 'posts.id', '=', 'post_tag.post_id')
            ->leftJoin('tags', 'post_tag.tag_id', '=', 'tags.id')
            ->leftJoin('post_dietary', 'posts.id', '=', 'post_dietary.post_id')
            ->leftJoin('dietaries', 'post_dietary.dietary_id', '=', 'dietaries.id')
            ->leftJoin('post_cuisine', 'posts.id', '=', 'post_cuisine.post_id')
            ->leftJoin('cuisines', 'post_cuisine.cuisine_id', '=', 'cuisines.id')
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
                'posts.status',
                DB::raw('(SELECT GROUP_CONCAT(DISTINCT tags.name SEPARATOR ", ") FROM post_tag LEFT JOIN tags ON post_tag.tag_id = tags.id WHERE post_tag.post_id = posts.id) as tags'),
                DB::raw('(SELECT GROUP_CONCAT(DISTINCT dietaries.name SEPARATOR ", ") FROM post_dietary LEFT JOIN dietaries ON post_dietary.dietary_id = dietaries.id WHERE post_dietary.post_id = posts.id) as dietaries'),
                DB::raw('(SELECT GROUP_CONCAT(DISTINCT cuisines.name SEPARATOR ", ") FROM post_cuisine LEFT JOIN cuisines ON post_cuisine.cuisine_id = cuisines.id WHERE post_cuisine.post_id = posts.id) as cuisines')
            )
            ->groupBy('posts.id', 'posts.title', 'posts.caption', 'posts.serving_size', 'posts.minutes', 'posts.hours', 'posts.type', 'posts.file', 'posts.user_id', 'posts.status');

        if ($request->filled('title')) {
            $query->where('posts.title', 'LIKE', '%' . $request->input('title') . '%');
        }
        if ($request->filled('type')) {
            $query->where('posts.type', 'LIKE', '%' . $request->input('type') . '%');
        }
        if ($request->filled('caption')) {
            $query->where('posts.caption', 'LIKE', '%' . $request->input('caption') . '%');
        }
        if ($request->filled('dietaries')) {
            $query->where('dietaries.name', 'LIKE', '%' . $request->input('dietaries') . '%');
        }
        if ($request->filled('tags')) {
            $query->where('tags.name', 'LIKE', '%' . $request->input('tags') . '%');
        }
        if ($request->filled('cuisines')) {
            $query->where('cuisines.name', 'LIKE', '%' . $request->input('cuisines') . '%');
        }
        if ($request->filled('time')) {
            $inputTime = (int) $request->input('time');
            $query->whereRaw('(posts.hours * 3600000000 + posts.minutes * 60000000) <= ?', [$inputTime]);
        }
        if ($request->filled('user_id')) {
            $query->where('posts.user_id', '=', $user_id);
        }
        if ($request->filled('status')) {
            $query->where('posts.status', '=', $request->input('status'));
        }

        $sortField = $request->input('sort_by', 'posts.created_at');
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
            $postData = $this->addExtraFields($post, $user_id);
            $postData['thumbnails'] = $post->thumbnails->map(function ($thumbnail) {
                return [
                    'thumbnail' => $thumbnail->thumbnail,
                    'type' => $thumbnail->type
                ];
            });
            return $postData;
        });

        return response()->json([
            'status' => 'success',
            'data' => $posts
        ]);
    }

    public function interestsSearch(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User must be authenticated.'
            ], 401);
        }

        $user_id = $user->id;

        $query = Post::with(['user' => function($query) {
            $query->select('id', 'name', 'username', 'profile_image', 'bio', 'website');
        }, 'comment', 'postlike', 'report_statuses'])
            ->leftJoin('post_tag', 'posts.id', '=', 'post_tag.post_id')
            ->leftJoin('tags', 'post_tag.tag_id', '=', 'tags.id')
            ->leftJoin('post_dietary', 'posts.id', '=', 'post_dietary.post_id')
            ->leftJoin('dietaries', 'post_dietary.dietary_id', '=', 'dietaries.id')
            ->leftJoin('post_cuisine', 'posts.id', '=', 'post_cuisine.post_id')
            ->leftJoin('cuisines', 'post_cuisine.cuisine_id', '=', 'cuisines.id')
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
            ->groupBy('posts.id', 'posts.title', 'posts.caption', 'posts.serving_size', 'posts.minutes', 'posts.hours', 'posts.type', 'posts.file', 'posts.thumbnail', 'posts.user_id')
            ->where('posts.user_id', '=', $user_id);

        if ($request->filled('title')) {
            $query->where('posts.title', 'LIKE', '%' . $request->input('title') . '%');
        }

        if ($request->filled('type')) {
            $query->where('posts.type', 'LIKE', '%' . $request->input('type') . '%');
        }

        if ($request->filled('caption')) {
            $query->where('posts.caption', 'LIKE', '%' . $request->input('caption') . '%');
        }

        if ($request->filled('dietaries')) {
            $query->where('dietaries.name', 'LIKE', '%' . $request->input('dietaries') . '%');
        }

        if ($request->filled('tags')) {
            $query->where('tags.name', 'LIKE', '%' . $request->input('tags') . '%');
        }

        if ($request->filled('cuisines')) {
            $query->where('cuisines.name', 'LIKE', '%' . $request->input('cuisines') . '%');
        }

        if ($request->filled('time')) {
            $inputTime = (int) $request->input('time');
            $query->whereRaw('(posts.hours * 3600000000 + posts.minutes * 60000000) <= ?', [$inputTime]);
        }

        if ($request->filled('user_id')) {
            $query->where('posts.user_id', '=', $user_id);
        }

        $sortField = 'posts.' . $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $perPage = $request->input('per_page', 10);

        if ($sortField === 'posts.users_interests') {

            $userInterests = DB::table('users_interests')->where('user_id', $user_id)->first();
            if ($userInterests) {
                $userTags = json_decode($userInterests->tags, true) ?? [];
                $userDietaries = json_decode($userInterests->dietaries, true) ?? [];
                $userCuisines = json_decode($userInterests->cuisines, true) ?? [];

                $posts = $query->get()->map(function ($post) use ($userTags, $userDietaries, $userCuisines) {
                    $postTags = explode(', ', $post->tags);
                    $postDietaries = explode(', ', $post->dietaries);
                    $postCuisines = explode(', ', $post->cuisines);

                    $tagMatchCount = count(array_intersect($postTags, $userTags));
                    $dietaryMatchCount = count(array_intersect($postDietaries, $userDietaries));
                    $cuisineMatchCount = count(array_intersect($postCuisines, $userCuisines));

                    $post->match_score = $tagMatchCount + $dietaryMatchCount + $cuisineMatchCount;

                    return $post;
                });

                $posts = $posts->sortByDesc('match_score')->values();

                $currentPage = LengthAwarePaginator::resolveCurrentPage();
                $paginatedPosts = new LengthAwarePaginator(
                    $posts->forPage($currentPage, $perPage),
                    $posts->count(),
                    $perPage,
                    $currentPage,
                    ['path' => LengthAwarePaginator::resolveCurrentPath()]
                );

                return response()->json([
                    'status' => 'success',
                    'data' => $paginatedPosts
                ]);
            }
        } else {
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
        ]);

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
                'comment_id' => ['required_if:type,0', 'numeric', 'exists:comments,id'],
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
                $q->where('id', $comment_details->id)->orWhere('id', $request->comment_id);
            })->whereNull("comment_id")
                ->get();

            $commentlikes = CommentLike::where('user_id', $user->id)
                ->where('post_id', $request->post_id)
                ->pluck('comment_id')->toArray();

            foreach ($comments as $comment) {
                $comment->is_commentlike = in_array($comment->id, $commentlikes);
                $comment->is_replylike = false;
                $comment->commentlike_count = count($commentlikes);

                foreach ($comment->reply as $reply) {
                    $reply->is_replaylike = in_array($reply->id, $commentlikes);
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
                'data' => [
                    'message' => $th->getMessage(),
                    'file' => $th->getFile(),
                    'line' => $th->getLine(),
                    'trace' => collect($th->getTrace())->take(5),
                ]
            ]);
        }
    }

    public function byFilter($filter_id)
    {
        $tagIds = DB::table('filter_tag')
            ->where('filter_id', $filter_id)
            ->pluck('tag_id')
            ->toArray();

        $dietaryIds = DB::table('filter_dietary')
            ->where('filter_id', $filter_id)
            ->pluck('dietary_id')
            ->toArray();

        $cuisineIds = DB::table('filter_cuisine')
            ->where('filter_id', $filter_id)
            ->pluck('cuisine_id')
            ->toArray();

        if (empty($tagIds) && empty($dietaryIds) && empty($cuisineIds)) {
            return response()->json([]);
        }

        $posts = Post::with(['tags:id,name', 'dietaries:id,name', 'cuisines:id,name', 'thumbnails'])
            ->when(!empty($tagIds), function ($query) use ($tagIds) {
                foreach ($tagIds as $tagId) {
                    $query->whereHas('tags', function ($q) use ($tagId) {
                        $q->where('tags.id', $tagId);
                    });
                }
            })
            ->when(!empty($dietaryIds), function ($query) use ($dietaryIds) {
                foreach ($dietaryIds as $dietaryId) {
                    $query->whereHas('dietaries', function ($q) use ($dietaryId) {
                        $q->where('dietaries.id', $dietaryId);
                    });
                }
            })
            ->when(!empty($cuisineIds), function ($query) use ($cuisineIds) {
                foreach ($cuisineIds as $cuisineId) {
                    $query->whereHas('cuisines', function ($q) use ($cuisineId) {
                        $q->where('cuisines.id', $cuisineId);
                    });
                }
            })
            ->get();

        return response()->json($posts);
    }

}

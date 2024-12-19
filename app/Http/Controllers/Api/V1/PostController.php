<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ResponseController;
use App\Post;
use App\Tag;
use App\Comment;
use App\CommentLike;
use App\DeviceToken;
use App\Ingredient;
use App\Instruction;
use App\Http\Controllers\Controller;
use App\PostLike;
use App\ReportStatus;
use App\User;
use App\UserTag;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PostController extends ResponseController
{
    public $post_obj;
    public $ingredient_obj;
    public $instruction_obj;

    public function  __construct()
    {
        $this->post_obj = new Post();
        $this->ingredient_obj = new Ingredient();
        $this->instruction_obj = new Instruction();
    }
    public function createPost(Request $request)
    {
        $user = $request->user();
        $rules = [
            'title' => ['required'],
            'file' => ['required_if:method,add', 'file', 'mimes:jpeg,png,gif,bmp,svg,webp,mp4,avi,wmv,mov,flv'],
            'caption' => ['required'],
            'serving_size' => ['required'],
            'hours' => ['required'],
            'minutes' => ['required'],
            'dietary' => ['required'],
            'tags' => ['required'],
            'method'=>['required'],
            'thumbnail'=>['required_if:method,add'],
            'type'=>['required','in:video,image'],
            'post_id'=>['required_if:method,edit'],

        ];
        $messages = [
            'file.required' => "The image field is required",
        ];
        $this->directValidation($rules, $messages);
        $data = null;
        $messages = __('api.suc_post_create');
        if($request->post_id > 0){
            $data = $this->post_obj->find($request->post_id);
            if(!empty($data)){
                $thumbnail = $data->getRawOriginal('thumbnail');
                $up = $data->getRawOriginal('file');
            }
            $messages = __('api.suc_post_update');

        }
        if ($request->hasfile('file')) {
            $up = upload_file('file', 'user_post_image');
        }
        if ($request->hasfile('thumbnail')) {
            $thumbnail = upload_file('thumbnail', 'user_post_thumbnail');
        }
        $tagItems = json_decode($request->tags, true);
        $tag = Tag::whereIn('name', $tagItems)->exists();
        if ($tag == false) {
            foreach ($tagItems as $value) {
                Tag::create([
                    'name' => $value,
                ]);
            }
        }
        $request_data = $request->all();
        $request_data['thumbnail'] = $thumbnail ?? "";
        $request_data['file'] = $up ?? "";
        $request_data['user_id'] = $user->id;
        $request_data['tags'] = implode(",", $tagItems);
        $request_data['hours'] = $request->hours ?? 0;
        unset($request_data['method']);
        $post = $this->post_obj->savePost($request_data,0,$data);
        if($post){
            $this->sendResponse(200, $messages , $post);
        }
        $this->sendError(__('api.err_something_went_wrong'), false);

    }

    public function addIngredient(Request $request)
    {
        $user = $request->user();
        $rules = [
            'name' => ['required'],
            'measurement' => ['required'],
            "post_id" => ['required', 'exists:posts,id'],
            "method"=>['required'],
            "ingredient_id"=>['required_if:method,edit'],
        ];
        $this->directValidation($rules);
        $data = null;
        $messages = __('api.suc_ingredient_create', ['order' => $request->order]);
        if($request->ingredient_id > 0){
            $data = $this->ingredient_obj->find($request->ingredient_id);
            $messages = __('api.suc_ingredient_update');
        }
        $request_data = $request->all();
        $request_data['user_id'] = $user->id;
        $request_data['type'] = $request->type ?? '';
        $request_data['measurement'] = $request->measurement ?? '';
        $request_data['order'] = (int) ($request->order ?? 0);
        unset($request_data['method']);
        $post = $this->ingredient_obj->saveIngredient($request_data,0,$data);
        if($post){
            $this->sendResponse(200, $messages , $post);
        }else{
            $this->sendError(__('api.err_something_went_wrong'), false);
        }

    }

    public function deleteComment(Request $request)
    {
        $rules = [
            'comment_id' => ['required'],
            'method' => ['required'],
        ];
        $this->directValidation($rules);
        $request->request->remove('method');

        $comment = Comment::find($request->comment_id);
        if ($comment) {
            $comment->delete();
            $this->sendResponse(200, __('api.suc_comment_delete'), false);
        } else {
            $this->sendError(__('api.err_comment_delete'), false);
        }
    }

    public function updateComment(Request $request)
    {
        $rules = [
            'comment_id' => ['required'],
            'comment_text' => ['required'],
            'method' => ['required'],
        ];
        $this->directValidation($rules);
        $request->request->remove('method');
        $comment = Comment::where('id', $request->comment_id)->update([
            'comment_text' => $request->comment_text,
        ]);
        if ($comment) {
            $this->sendResponse(200, __('api.suc_comment_update'), true);
        } else {
            $this->sendError(__('api.err_comment_update'), false);
        }
    }
    public function addInstruction(Request $request)
    {
        $user = $request->user();
        $rules = [
            'title'=>'required',
            'file' => ['required_if:method,add', 'file', 'mimes:jpeg,png,gif,bmp,svg,webp,mp4,avi,wmv,mov,flv'],
            'description' => ['required'],
            "post_id" => ['required', 'exists:posts,id'],
            'thumbnail'=>['required_if:method,add','file'],
            'type'=>['required','in:video,image'],
            'method'=>['required'],
            'instruction_id'=>['required_if:method,edit'],
        ];
        $messages = [
            'file.required' => "The image field is required",
        ];
        $this->directValidation($rules, $messages);
        $data = null;
        $order = $request->order ?? "1";

        $messages = __('api.suc_instruction_create', ['order' => $order]);
        if($request->instruction_id > 0){
            $data = $this->instruction_obj->find($request->instruction_id);
            if(!empty($data)){
                $thumbnail = $data->getRawOriginal('thumbnail');
                $up = $data->getRawOriginal('file');
            }
            $messages = __('api.suc_instruction_update');
        }
        if ($request->hasfile('file')) {
            $up = upload_file('file', 'user_instruction_image');
        }
        if ($request->hasfile('thumbnail')) {
            $thumbnail = upload_file('thumbnail', 'user_instruction_thumbnail');
        }
        $request_data = $request->all();
        $request_data['thumbnail'] = $thumbnail ?? "";
        $request_data['file'] = $up ?? "";
        $request_data['user_id'] = $user->id;
        $request_data['order'] = (int) ($order);
        $request_data['type'] = (strpos($request->file->getMimeType(), 'video') !== false) ? 'video' : 'image';
        unset($request_data['method']);
        $post = $this->instruction_obj->saveInstruction($request_data,0,$data);
        if($post){
            $this->sendResponse(200, $messages , $post);
        }
        $this->sendError(__('api.err_something_went_wrong'), false);
    }

    public function getTag()
    {
        $tags = Tag::all();
        $this->sendResponse(200, __('api.suc_tags'), $tags);
    }

    public function home(Request $request)
    {
        $user = null;
        $limit = $request->limit ?? 10;
        $offset = $request->offset ?? 0;

        $token = get_header_auth_token();
        if (!empty($token)) {
            $is_login = DeviceToken::where('token', $token)->with('user')->has('user')->first();
            if ($is_login) {
                $user = $is_login->user;
                $likes = PostLike::where('user_id', $user->id)->pluck('post_id')->toArray();
                $reported_post =  ReportStatus::where('user_id',$user->id)->pluck('post_id')->toArray();
            }
        }
        $p_instance = Post::with([
            'user' => function ($q) {
                $q->select("id", "username", "profile_image");
            },
            'ingredient' => function ($q) {
                $q->select("id", "post_id", "name", "type","measurement");
            },
            'instruction',
        ])
            ->withCount(["comment", 'postlike'])
            ->AvgRating()
           ->IsRating($user->id ?? 0);
        if (isset($user) && !empty($user)) {
            $c_instance = clone $p_instance;
            if($c_instance->where('user_id',$user->id)->exists() == true){
               $p_instance = $p_instance->where('not_interested', 0);
            }
        }
        $posts = $p_instance->orderBy('id', 'DESC')
            ->offset($offset)
            ->limit($limit)
            ->get();

        $post_data = [];
        foreach ($posts as $post) {

            $post->is_like = false;
            if (isset($likes) && count($likes) > 0) {
                if (in_array($post->id, $likes)) {
                    $post->is_like = true;
                }
            }
            $post->is_reported = false;
            if (isset($reported_post) && count($reported_post) > 0) {
                if (in_array($post->id, $reported_post)) {
                    $post->is_reported = true;
                }
            }
            $post_data[] = $post;
        }

        if (!empty($posts)) {
            $this->sendResponse(200, __('api.suc_posts'), $posts);
        } else {
            $this->sendError(__('api.err_post'), false);
        }
    }

    public function postDetails(Request $request)
    {
        $rules = [
            "post_id" => ['required', 'exists:posts,id'],
        ];
        $this->directValidation($rules);
        $token = get_header_auth_token();
        if (!empty($token)) {
            $is_login = DeviceToken::where('token', $token)->with('user')->has('user')->first();
            if ($is_login) {
                $user = $is_login->user;
                $likes = PostLike::where('user_id', $user->id)->pluck('post_id')->toArray();
                $reported_post =  ReportStatus::where('user_id',$user->id)->pluck('post_id')->toArray();
            }
        }
        $posts = Post::with([
            'user' => function ($q) {
                $q->select("id", "username", "profile_image");
            },
            'ingredient' => function ($q) {
                $q->select("id", "post_id", "name", "type", "measurement")->orderBy('order', 'ASC');
            },
            'instruction' => function ($q) {
                $q->select("id", "post_id", "title", "description", "file", "thumbnail", "type")->orderBy('order', 'ASC');
            },
        ])
            ->withCount(["comment", 'postlike'])
            ->AvgRating()
            ->IsRating($user->id ?? 0)
            ->orderBy('id', 'DESC')
            ->where('id', $request->post_id)
            ->first();
        $posts->is_like = false;
        if (isset($likes) && count($likes) > 0) {
            if (in_array($posts->id, $likes)) {
                $posts->is_like = true;
            }
        }
        $posts->is_reported = false;
        if (isset($reported_post) && count($reported_post) > 0) {
            if (in_array($posts->id, $reported_post)) {
                $posts->is_reported = true;
            }
        }
        if (!empty($posts)) {
            $this->sendResponse(200, __('api.suc_posts'), $posts);
        } else {
            $this->sendError(__('api.err_post'), false);
        }
    }

    public function getUserPosts(Request $request)
    {

        try {
            $token = get_header_auth_token();
            if (!empty($token)) {
                $is_login = DeviceToken::where('token', $token)->with('user')->has('user')->first();
                if ($is_login) {
                    $user = $is_login->user;
                    $commentlikes = CommentLike::where('user_id', $user->id)
                        ->where('post_id', $request->post_id)
                        ->pluck('comment_id')->toArray();
                }
            }

            $posts = Post::with([
                'user:id,username,profile_image',
                'ingredient:id,post_id,name,type,measurement',
                'instruction:id,post_id,title,description,file,thumbnail,type',
                'comment' => function ($q) {
                    $q->withCount(["commentlike"]);
                    $q->with(["user" => function ($q) {
                        $q->select("id", "username", "profile_image");
                    }]);
                },
            ])
            ->where('user_id', $user->id)
            ->orderBy('id', 'DESC')
            ->get();

            if (!empty($posts)) {
                $this->sendResponse(200, __('api.suc_posts'), $posts);
            } else {
                $this->sendError(__('api.err_post'), false);
            }
        } catch (\Throwable $th) {
            $this->sendError(__('api.err_post'), false);
        }
    }


    public function getUserLikedPosts(Request $request)
    {
        $user = $request->user();
        $likedPosts = Post::with([
            'user' => function ($q) {
                $q->select("id", "username", "profile_image");
            },
            'ingredient' => function ($q) {
                $q->select("id", "post_id", "name", "type","measurement");
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

        if (!empty($likedPosts)) {
            $this->sendResponse(200, __('api.suc_liked_posts'), $likedPosts);
        } else {
            $this->sendError(__('api.err_no_liked_posts'), false);
        }
    }


    public function getUserRecipesAndComments(Request $request)
    {
        $user = $request->user();

        // Get user's recipes
        $recipes = Post::with([
            'user' => function ($q) {
                $q->select("id", "username", "profile_image");
            },
            'ingredient' => function ($q) {
                $q->select("id", "post_id", "name", "type","measurement");
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

        // Get comments left for the user
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

        $data = [
            'recipes' => $recipes,
            'comments' => $comments
        ];

        $this->sendResponse(200, __('api.suc_my_recipes_and_comments'), $data);
    }
    public function postCommentReview(Request $request)
    {
        try {
            $user = $request->user();
            $rules = [
                'post_id' => ['required', 'exists:posts,id'],
                'comment_id' => ['nullable', 'numeric', 'exists:comments,id'],
                'comment_text' => ['required_if:type,0'],
                'type'=>['required'],
                'rating' => ['required_if:type,1'],
            ];
            $this->directValidation($rules);
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
                // ->when(!is_null($request->type) && $request->type != "", function ($q) use ($request) {
                //     $q->where('type', $request->type);
                // })
                ->get();
                $commentlikes = CommentLike::where('user_id', $user->id)
                ->where('post_id', $request->post_id)
                ->pluck('comment_id')->toArray();
            $comment_data = [];
            foreach ($comments as $comment) {
                $comment->is_commentlike = false;
                $comment->is_replylike = false;
                $comment->commentlike_count =  count($commentlikes);
                if (isset($commentlikes) && count($commentlikes) > 0) {
                    if (in_array($comment->id, $commentlikes)) {
                        $comment->is_commentlike = true;
                    }
                }

                $comment_data = [];
                $rep_comments = $comment->reply;
                foreach ($rep_comments as $replay) {
                    $replay->is_replaylike = false;
                    if (isset($commentlikes) && count($commentlikes) > 0) {
                        if (in_array($replay->id, $commentlikes)) {
                            $replay->is_replaylike = true;
                        }
                    }
                }
                $comment_data[] = $comment;
            }
            if (!empty($comments)) {
                $this->sendResponse(200, __('api.suc_comment_create'), $comments);
            } else {
                $this->sendError(__('api.err_comment'), false);
            }
        } catch (\Throwable $th) {
            $this->sendError(__('api.err_comment'), $th->getMessage());
        }
    }

    public function postLike(Request $request)
    {
        $user = $request->user();
        $rules = [
            'post_id' => ['required', 'exists:posts,id'],
        ];
        $this->directValidation($rules);
        $postlike = PostLike::where('user_id', $user->id)->where('post_id', $request->post_id)->exists();
        if ($postlike == true) {
            PostLike::where('user_id', $user->id)->where('post_id', $request->post_id)->delete();
            $this->sendResponse(200, __('api.suc_dislike'));
        } else {
            $like = PostLike::create([
                "user_id" => $user->id,
                "post_id" => $request->post_id,
            ]);
            if (!empty($like)) {
                $this->sendResponse(200, __('api.suc_like'), $like);
            } else {
                $this->sendError(__('api.err_like'), false);
            }
        }
    }
    public function commentLike(Request $request)
    {
        $user = $request->user();
        $rules = [
            'post_id' => ['required', 'exists:posts,id'],
            'comment_id' => 'required', 'exists:comments,id',

        ];

        $this->directValidation($rules);

        $commentlike = CommentLike::where('user_id', $user->id)
            ->where('post_id', $request->post_id)
            ->where('comment_id', $request->comment_id)
            ->exists();

        if ($commentlike == true) {
            CommentLike::where('user_id', $user->id)
                ->where('post_id', $request->post_id)
                ->where('comment_id', $request->comment_id)
                ->delete();
            $this->sendResponse(200, __('api.suc_comment_dislike'));
        } else {
            $like = CommentLike::create([
                "user_id" => $user->id,
                "post_id" => $request->post_id,
                "comment_id" => $request->comment_id,
            ]);
            if (!empty($like)) {
                $this->sendResponse(200, __('api.suc_comment_like'), $like);
            } else {
                $this->sendError(__('api.err_comment_like'), false);
            }
        }
    }
    public function commentList(Request $request)
    {
        $rules = [
            'post_id' => ['required', 'exists:posts,id'],
        ];

        $this->directValidation($rules);

        $limit = $request->limit ?? 10;
        $offset = $request->offset ?? 0;

        $token = get_header_auth_token();
        if (!empty($token)) {
            $is_login = DeviceToken::where('token', $token)->with('user')->has('user')->first();
            if ($is_login) {
                $user = $is_login->user;
                $commentlikes = CommentLike::where('user_id', $user->id)
                    ->where('post_id', $request->post_id)
                    ->pluck('comment_id')->toArray();
            }
        }
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
        ])->withCount(["commentlike"])
            ->where('post_id', $request->post_id)
            ->whereNull("comment_id")
            ->when(!is_null($request->type) && $request->type != "", function ($q) use ($request) {
                $q->where('type', $request->type);
            })
            ->orderBy('id', 'DESC')
            ->offset($offset)
            ->limit($limit)
            ->get();

        $comment_data = [];
        // dd($comments);
        foreach ($comments as $comment) {
            $comment->is_commentlike = false;
            $comment->is_replylike = false;
            if (isset($commentlikes) && count($commentlikes) > 0) {

                if (in_array($comment->id, $commentlikes)) {
                    $comment->is_commentlike = true;
                }
            }
            $comment_data = [];
            $rep_comments = $comment->reply;
            foreach ($rep_comments as $replay) {
                $replay->is_replaylike = false;
                if (isset($commentlikes) && count($commentlikes) > 0) {
                    if (in_array($replay->id, $commentlikes)) {
                        $replay->is_replaylike = true;
                    }
                }
            }
            $comment_data[] = $comment;
        }

        $this->sendResponse(200, __('api.suc_comment'), $comments);
    }

    public function postLikeList(Request $request)
    {
        $limit = $request->limit ?? 10;
        $offset = $request->offset ?? 0;
        $post_like =  PostLike::with(['user' => function ($q) {
            $q->select("id", "username", "profile_image");
        }])->offset($offset)
            ->limit($limit)
            ->get();
        $this->sendResponse(200, __('api.suc_post_like_list'), $post_like);
    }

    public function destory(Request $request)
    {
        $rules = [
            'post_id' => ['required', 'exists:posts,id'],
        ];
        $this->directValidation($rules);
        $bank_account = Post::destroy($request->post_id);
        $this->sendResponse(200, __('api.suc_post_delete'), false);
    }

    public function notInterested(Request $request)
    {
        $rules = [
            'post_id' => ['required', 'exists:posts,id'],
        ];
        $this->directValidation($rules);
        $post = Post::where('id', $request->post_id)->update([
            'not_interested' => 1,
        ]);
        $this->sendResponse(200, __('api.suc_not_interested'), false);
    }

    public function searchUsername(Request $request)
    {
        $username = $request->username;
        $user_name = User::where('username', 'LIKE', "%$username%")->get();
        $this->sendResponse(200, __('api.suc_username'), $user_name);
    }

    public function userTag(Request $request)
    {
        $rules = [
            'post_id' => ['required', 'exists:posts,id'],
            'comment_id' => ['required', 'exists:comments,id'],
            // 'tag_user_id' => ['required', 'string'],
        ];
        $this->directValidation($rules);
        $user = $request->user();
        $usertag = UserTag::create([
            "user_id" => $user->id,
            "post_id" => $request->post_id,
            "comment_id"=>$request->comment_id,
            "description"=>$request->description,
            "tag_user_id"=>$request->tag_user_id,
        ]);
        $userTags  = UserTag::where('user_id',$user->id)
        ->where('post_id',$request->post_id)
        ->where('comment_id',$request->comment_id)->get();
        if(!empty($request->tag_user_id))
        {
            $tagUserIds = explode(',', $request->tag_user_id);
            $users = User::whereIn('id', $tagUserIds)->get();
            $usertag->user = $users;
        }
        if (!empty($userTags)) {
            $this->sendResponse(200, __('api.suc_usertag'), $usertag);
        } else {
            $this->sendError(__('api.err_usertag'), false);
        }
    }
}

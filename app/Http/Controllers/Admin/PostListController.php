<?php

namespace App\Http\Controllers\Admin;

use App\Cuisine;
use App\Dietary;
use App\Tag;
use Illuminate\Http\Request;
use App\Http\Controllers\WebController;
use App\Post;
use DataTables;
use App\Ingredient;
use App\Instruction;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostListController extends WebController
{
    public $ingredient_obj;

    public function __construct()
    {
        $this->ingredient_obj = new Ingredient();
    }

    public function index()
    {
        $title = 'Post List';
        return view('admin.post.index', [
            'title' => $title,
            'breadcrumb' => breadcrumb([
                'Post' => route('admin.post.index'),
            ]),
        ]);
    }


    public function create()
    {
        $tags = Tag::all();
        $dietaries = Dietary::all();
        $cuisines = Cuisine::all();

        return view('admin.post.create', [
            'title' => 'Create Post',
            'tags' => $tags,
            'dietaries' => $dietaries,
            'cuisines' => $cuisines,
            'breadcrumb' => breadcrumb([
                'post' => route('admin.post.index'),
                'create' => route('admin.post.create')
            ]),
        ]);
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        $post = Post::with(['instruction', 'tags', 'dietaries', 'cuisines'])->find($id);

        if (!$post) {
            error_session('post not found');
            return redirect()->route('admin.post.index');
        }

        $ingredients = Ingredient::where('post_id', $id)->get();

        $instructions = Instruction::where('post_id', $id)->orderBy('id', 'ASC')->get();

        $post->tags = $post->tags()->pluck('name')->toArray();
        $post->dietaries = $post->dietaries()->pluck('name')->toArray();
        $post->cuisines = $post->cuisines()->pluck('name')->toArray();

        return view('admin.post.view', [
            'title' => 'View post',
            'data' => $post,
            'breadcrumb' => breadcrumb([
                'post' => route('admin.post.index'),
                'view' => route('admin.post.show', $id)
            ]),
            'ingredients' => $ingredients,
            'instructions' => $instructions,
        ]);
    }

    public function edit($id)
    {
        $post = Post::with(['tags', 'dietaries', 'cuisines'])->find($id);

        if (!$post) {
            error_session('Post not found');
            return redirect()->route('admin.post.index');
        }

        $tags = Tag::all();
        $dietaries = Dietary::all();
        $cuisines = Cuisine::all();
        $post->tags = $post->tags()->pluck('tags.id')->toArray();
        $post->dietaries = $post->dietaries()->pluck('dietaries.id')->toArray();
        $post->cuisines = $post->cuisines()->pluck('cuisines.id')->toArray();

        return view('admin.post.edit', [
            'title' => 'Edit post',
            'data' => $post,
            'tags' => $tags,
            'dietaries' => $dietaries,
            'cuisines' => $cuisines,
            'breadcrumb' => breadcrumb([
                'post' => route('admin.post.index'),
                'edit' => route('admin.post.edit', $id)
            ]),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $post = Post::find($id);

        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        $videoExtensions = ['mp4', 'avi', 'mov', 'mkv', 'wmv', 'flv'];

        $fileSrc = '';
        $thumbnailSrc = '';

        if ($post) {
            $request->validate([
                'title' => 'required',
                'file' => 'nullable|file',
                'thumbnail' => 'nullable|file',
                'hours' => 'required|numeric',
                'minutes' => 'required|numeric',
                'tags' => 'nullable|array',
                'dietaries' => 'nullable|array',
                'cuisines' => 'nullable|array',
            ]);

            if ($request->hasFile('file')) {

                $extension = strtolower($request->file('file')->getClientOriginalExtension());

                if (in_array($extension, $imageExtensions)) {
                    $path = $request->file('file')->store('uploads/posts/images', 's3');

                    if ($path) {

                        Storage::disk('s3')->setVisibility($path, 'public');

                        $fileSrc = $path;
                    }
                }

                if (in_array($extension, $videoExtensions)) {
                    $path = $request->file('file')->store('uploads/posts/videos', 's3');

                    if ($path) {

                        Storage::disk('s3')->setVisibility($path, 'public');

                        $fileSrc = $path;
                    }
                }
            }

            if ($request->hasFile('thumbnail')) {

                $extension = strtolower($request->file('thumbnail')->getClientOriginalExtension());

                if (in_array($extension, $imageExtensions)) {
                    $path = $request->file('thumbnail')->store('uploads/posts/thumbnails/images', 's3');

                    if ($path) {

                        Storage::disk('s3')->setVisibility($path, 'public');

                        $thumbnailSrc = $path;
                    }
                }

                if (in_array($extension, $videoExtensions)) {
                    $path = $request->file('thumbnail')->store('uploads/posts/thumbnails/videos', 's3');

                    if ($path) {

                        Storage::disk('s3')->setVisibility($path, 'public');

                        $thumbnailSrc = $path;
                    }
                }
            }

            $postData = [
                'title' => $request->title,
                'file' => $fileSrc ?: $post->file,
                'thumbnail' => $thumbnailSrc ?: $post->thumbnail,
                'hours' =>  $request->hours,
                'minutes' =>  $request->minutes,
            ];

            $post->update($postData);

            if ($request->has('tags')) {
                $post->tags()->sync($request->tags);
            } else {
                $post->tags()->detach();
            }

            if ($request->has('dietaries')) {
                $post->dietaries()->sync($request->dietaries);
            } else {
                $post->dietaries()->detach();
            }

            if ($request->has('cuisines')) {
                $post->cuisines()->sync($request->cuisines);
            } else {
                $post->cuisines()->detach();
            }

            return redirect()->route('admin.post.show', $post->id)
                ->with('success', 'Post updated successfully');
        } else {
            return redirect()->route('admin.post.index')
                ->with('error', 'Post not found');
        }
    }

    public function destroy($id)
    {
        $data = Post::where('id', $id)->first();
        if ($data) {
            $data->delete();
            success_session('Post deleted successfully');
        } else {
            error_session('Post not found');
        }
        return redirect()->route('admin.post.index');
    }

    public function listing()
    {
        $data = Post::select('posts.*', 'users.name as user_name')
            ->join('users', 'posts.user_id', '=', 'users.id')
            ->orderBy('posts.id', 'DESC')
            ->get();

        return Datatables::of($data)
            ->addIndexColumn()
            ->editColumn('user_name', function ($row) {
                return "<span title='$row->user_name'>{$row->user_name}</span>";
            })
            ->editColumn('created_at', function ($row) {
                return "<span title='$row->created_at'>{$row->created_at}</span>";
             })
            ->addColumn('status', function ($row) {
                $checked = $row->status ? 'checked' : '';
                return "<input type='checkbox' class='status-switch' data-id='{$row->id}' {$checked} />";
            })
            ->addColumn('action', function ($row) {
                $param = [
                    'id' => $row->id,
                    'url' => [
                        'delete' => route('admin.post.destroy', $row->id),
                        'edit' => route('admin.post.edit', $row->id),
                        'view' => route('admin.post.show', $row->id),
                    ]
                ];
                return $this->generate_actions_buttons($param);
            })
            ->rawColumns(['user_name','created_at','status','action'])
            ->make(true);
    }

    public function postDetails(Request $request)
    {
        $data = Ingredient::where('post_id',$request->id)->get();

        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $param = [
                    'id' => $row->id,
                    'url' => [
                        'edit' => route('admin.post.post_details_edit', $row->id),
                        'delete' => route('admin.post.post_details_destroy', $row->id),
                    ]
                ];
                return $this->generate_actions_buttons($param);
            })
            ->rawColumns(["action"])
            ->make(true);
    }

    public function postDetailsEdit($id)
    {
        $data = $this->ingredient_obj->find($id);

        if (isset($data) && !empty($data)) {
            return view('admin.post.create', [
                'title' => 'Ingredients Update',
                'breadcrumb' => breadcrumb([
                    'post' => route('admin.post.show', $data->post_id),
                    'edit' => route('admin.post.post_details_edit', $id),
                ]),
            ])->with(compact('data'));
        } else {
            return redirect()->route('admin.post.index')->with('error', 'Ingredient not found');
        }
    }

    public function postDetailsUpdate(Request $request, $id)
    {
        $data =$this->ingredient_obj->find($id);

        if (isset($data) && !empty($data)) {
            $return_data = $request->all();
            $this->ingredient_obj->saveIngredient($return_data,$id,$data);
            success_session('Ingredients updated successfully');
        } else {
            error_session('Ingredients not found');
        }
        return redirect()->route('admin.post.show',$data->post_id);
    }

    public function postDetailsDestroy($id)
    {
        $data = Ingredient::where('id', $id)->first();

        if ($data) {
            $data->delete();
            success_session('Ingredients Deleted successfully');
        } else {
            error_session('Ingredients not found');
        }
        return redirect()->route('admin.post.show',$data->post_id);
    }

    public function instruction(Request $request)
    {
        $data = Instruction::where("post_id",$request->id)->orderBy('id','ASC')->get();

        return Datatables::of($data)
            ->addIndexColumn()
            ->editColumn('title', function ($row) {
                return  !empty($row->title) ? $row->title : '-';
             })
             ->editColumn('type', function ($row) {
                return  !empty($row->type) ? $row->type : '-';
             })
             ->editColumn('file', function ($row) {
               return  '<a href="'.$row->file.'" target="_blank">Show File</a>';
            })
            ->editColumn('thumbnail', function ($row) {
                return  '<a href="'.$row->thumbnail.'" target="_blank">Show File</a>';
             })
            ->editColumn('description', function ($row) {
                return "<span title='$row->description'>".Str::limit($row->description, 50)."</span>";
             })
             ->rawColumns(['title','file',"description","thumbnail","type"])
            ->make(true);
    }

}

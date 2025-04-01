<?php

namespace App\Http\Controllers\Admin;

use App\Cuisine;
use App\Dietary;
use App\Tag;
use App\PostThumbnail;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\WebController;
use App\Post;
use DataTables;
use App\Ingredient;
use App\Instruction;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PostListController extends WebController
{
    public $ingredient_obj;

    public function __construct()
    {
        $this->ingredient_obj = new Ingredient();
    }

    public function index()
    {
        $tags = Tag::all();
        $dietaries = Dietary::all();
        $cuisines = Cuisine::all();

        return view('admin.post.index', [
            'tags' => $tags,
            'dietaries' => $dietaries,
            'cuisines' => $cuisines,
            'title' => 'Recipes',
            'breadcrumb' => breadcrumb([
                'Recipes' => route('admin.post.index'),
            ]),
        ]);
    }

    public function listing(Request $request)
    {
        $query = Post::select('posts.*', 'users.name as user_name')
            ->join('users', 'posts.user_id', '=', 'users.id')
            ->orderBy('posts.id', 'DESC');

        if ($request->has('verified') && $request->verified === '0') {
            $query->where('posts.verified', 0);
        }

        if ($request->has('tags')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->whereIn('tags.id', $request->tags);
            });
        }

        if ($request->has('dietaries')) {
            $query->whereHas('dietaries', function ($q) use ($request) {
                $q->whereIn('dietaries.id', $request->dietaries);
            });
        }

        if ($request->has('cuisines')) {
            $query->whereHas('cuisines', function ($q) use ($request) {
                $q->whereIn('cuisines.id', $request->cuisines);
            });
        }

        $data = $query->get();

        return Datatables::of($data)
            ->addIndexColumn()
            ->editColumn('user_name', function ($row) {
                return "<span title='$row->user_name'>{$row->user_name}</span>";
            })
            ->editColumn('created_at', function ($row) {
                return "<span title='$row->created_at'>{$row->created_at}</span>";
            })
            ->addColumn('verified', function ($row) {
                $checked = $row->verified ? 'checked' : '';
                return "<label class='switch'>
                <input type='checkbox' class='toggle-verified' data-id='{$row['id']}' {$checked}>
                <span class='slider slider-secondary round'></span>
            </label>";
            })
            ->addColumn('status', function ($row) {
                $checked = $row->status ? 'checked' : '';
                return "<label class='switch'>
                <input type='checkbox' class='toggle-status' data-id='{$row->id}' {$checked}>
                <span class='slider round'></span>
            </label>";
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
            ->rawColumns(['user_name','created_at','status','verified','action'])
            ->make(true);
    }

    public function create()
    {
        $tags = Tag::all();
        $dietaries = Dietary::all();
        $cuisines = Cuisine::all();
        $users = User::all();

        return view('admin.post.create', [
            'title' => 'Create Post',
            'users' => $users,
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
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        $videoExtensions = ['mp4', 'avi', 'mov', 'mkv', 'wmv', 'flv'];

        Log::info('Request files:', $request->all());

        $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'nullable|file',
            'thumbnails.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,avi,mov,mkv,wmv,flv|max:20480',
            'thumbnails' => 'array|max:4',
            'hours' => 'required|numeric',
            'minutes' => 'required|numeric',
            'serving_size' => 'nullable|numeric',
            'caption' => 'nullable|string',
            'tags' => 'nullable|array',
            'dietaries' => 'nullable|array',
            'cuisines' => 'nullable|array',
        ]);

        $fileSrc = '';
        if ($request->hasFile('file')) {
            $extension = strtolower($request->file('file')->getClientOriginalExtension());
            Log::info('File extension:', ['extension' => $extension]);
            if (in_array($extension, $imageExtensions)) {
                $path = $request->file('file')->store('uploads/posts/images', 's3');
            } elseif (in_array($extension, $videoExtensions)) {
                $path = $request->file('file')->store('uploads/posts/videos', 's3');
            }
            if (isset($path)) {
                Storage::disk('s3')->setVisibility($path, 'public');
                $fileSrc = $path;
                Log::info('File saved to S3:', ['path' => $path]);
            }
        }

        $post = Post::create([
            'title' => $request->title,
            'file' => $fileSrc,
            'hours' => $request->hours,
            'minutes' => $request->minutes,
            'serving_size' => $request->serving_size,
            'caption' => $request->caption,
            'user_id' => $request->user_id,
        ]);

        if ($request->hasFile('thumbnails')) {
            foreach ($request->file('thumbnails') as $thumbnail) {
                $extension = strtolower($thumbnail->getClientOriginalExtension());
                Log::info('Thumbnail extension:', ['extension' => $extension]);

                if (in_array($extension, $imageExtensions)) {
                    $path = $thumbnail->store('uploads/posts/thumbnails/images', 's3');
                    $type = 'image';
                } elseif (in_array($extension, $videoExtensions)) {
                    $path = $thumbnail->store('uploads/posts/thumbnails/videos', 's3');
                    $type = 'video';
                }

                if (isset($path)) {
                    Storage::disk('s3')->setVisibility($path, 'public');
                    PostThumbnail::create([
                        'post_id' => $post->id,
                        'thumbnail' => $path,
                        'type' => $type,
                    ]);
                    Log::info('Thumbnail saved to S3:', ['path' => $path, 'type' => $type]);
                }
            }
        }

        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        }

        if ($request->has('dietaries')) {
            $post->dietaries()->sync($request->dietaries);
        }

        if ($request->has('cuisines')) {
            $post->cuisines()->sync($request->cuisines);
        }

        Log::info('Post created successfully:', ['post_id' => $post->id]);

        return redirect()->route('admin.post.index')->with('success', 'Post created successfully.');
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
                'caption' => 'nullable|string',
                'serving_size' => 'nullable|numeric',
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
                'caption' => $request->caption,
                'serving_size' => $request->serving_size,
                'hours' =>  $request->hours,
                'minutes' =>  $request->minutes,
            ];

            if (!empty($fileSrc)) {
                $postData['file'] = $fileSrc;
            }

            if (!empty($thumbnailSrc)) {
                $postData['thumbnail'] = $thumbnailSrc;
            }

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
            return view('admin.post.post_details_edit', [
                'title' => 'Ingredients Update',
                'breadcrumb' => breadcrumb([
                    'post' => route('admin.post.show', $data->post_id),
                    'edit' => route('admin.post.post_details_edit', $id),
                ]),
                'data' => $data // Додаємо 'data' до масиву
            ]);
        } else {
            return redirect()->route('admin.post.index')->with('error', 'Ingredient not found');
        }
    }
    public function postDetailsUpdate(Request $request, $id)
    {
        $data = $this->ingredient_obj->find($id);

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

    public function uploadFile(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|file|max:2048',
            'type' => 'required|in:file,thumbnail'
        ]);

        $post = Post::findOrFail($id);

        $fileUrl = $this->handleFileUpload($request->file('file'), $request->type);

        if ($fileUrl) {
            if ($request->type === 'file') {
                $post->file = $fileUrl;
            } elseif ($request->type === 'thumbnail') {
                $post->thumbnail = $fileUrl;
            }

            $post->save();

            return response()->json([
                'success' => true,
                'file_url' => Storage::disk('s3')->url($fileUrl), // Correct URL
                'type' => $request->type
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'File upload failed'
        ]);
    }

    private function handleFileUpload($file, $type)
    {
        $imageExtensions = ['jpeg', 'png', 'jpg', 'gif', 'svg'];
        $videoExtensions = ['mp4', 'avi', 'mov', 'mkv', 'wmv', 'flv'];

        $extension = strtolower($file->getClientOriginalExtension());

        // Handle image files
        if (in_array($extension, $imageExtensions)) {
            $directory = ($type === 'file') ? 'uploads/posts/images' : 'uploads/posts/thumbnails/images'; // Handle file and thumbnail images
        }
        // Handle video files
        elseif (in_array($extension, $videoExtensions)) {
            $directory = ($type === 'file') ? 'uploads/posts/videos' : 'uploads/posts/thumbnails/videos'; // Handle file and thumbnail videos
        } else {
            return false; // Invalid file extension
        }

        // Store the file on S3 in the appropriate directory
        $path = $file->store($directory, 's3');

        if ($path) {
            // Set the file visibility to public on S3
            Storage::disk('s3')->setVisibility($path, 'public');

            // Return only the relative path (without full URL)
            return $path; // E.g., 'uploads/posts/images/thumb-88533FAC-...'
        }

        return false; // In case file upload fails
    }

    public function deleteFile(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        $fileType = $request->query('type');

        if ($fileType === 'file' && $post->file) {
            Storage::disk('s3')->delete($post->file);
            $post->file = null;
        } elseif ($fileType === 'thumbnail' && $post->thumbnail) {
            Storage::disk('s3')->delete($post->thumbnail);
            $post->thumbnail = null;
        } else {
            return response()->json(['success' => false, 'message' => 'Invalid file type'], 400);
        }

        $post->save();

        return response()->json(['success' => true]);
    }

    public function updateStatus(Request $request)
    {
        $post = Post::find($request->id);
        if (!$post) {
            return response()->json(['success' => false, 'message' => 'Post not found!']);
        }

        $post->status = !$post->status;
        $post->save();

        return response()->json(['success' => true, 'message' => 'Status updated!', 'status' => $post->status]);
    }

    public function updateVerified(Request $request)
    {
        $post = Post::find($request->id);
        if (!$post) {
            return response()->json(['success' => false, 'message' => 'Post not found!']);
        }

        $post->verified = !$post->verified;
        $post->save();

        return response()->json(['success' => true, 'message' => 'Verified updated!', 'verified' => $post->verified]);
    }

}

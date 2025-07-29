<?php

namespace App\Http\Controllers\Admin;

use App\Cuisine;
use App\Dietary;
use App\Ingredient;
use App\Tag;
use App\PostThumbnail;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\WebController;
use App\Post;
use DataTables;
use App\PostIngredient;
use App\Instruction;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Jobs\ConvertVideo;

class PostListController extends WebController
{
    public $ingredient_obj;

    public function __construct()
    {
        $this->ingredient_obj = new PostIngredient();
    }

    private function dispatchConvertJob($s3Key)
    {
        $localDir = storage_path('app/tmp');
        if (!file_exists($localDir)) {
            mkdir($localDir, 0755, true);
        }

        $fileName = basename($s3Key);
        $localPath = $localDir . '/' . $fileName;

        Storage::disk('s3')->getDriver()->getAdapter()->getClient()->getObject([
            'Bucket' => env('AWS_BUCKET'),
            'Key' => $s3Key,
            'SaveAs' => $localPath,
        ]);

        $extension = pathinfo($fileName, PATHINFO_EXTENSION);

        ConvertVideo::dispatch($localPath, $extension, $fileName);
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
        $ingredients = Ingredient::all();

        return view('admin.post.create', [
            'title' => 'Create Post',
            'users' => $users,
            'tags' => $tags,
            'dietaries' => $dietaries,
            'cuisines' => $cuisines,
            'ingredients' => $ingredients,
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

        $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'nullable|file|mimetypes:video/mp4,video/x-msvideo,video/quicktime,image/jpeg,image/png,image/gif|max:51200',
            'thumbnails' => 'nullable|array|max:4',
            'thumbnails.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,avi,mov,mkv,wmv,flv|max:20480',
            'thumbnails_files' => 'nullable|array|max:4',
            'thumbnails_files.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,avi,mov,mkv,wmv,flv|max:51200',
            'thumbnail_titles' => 'nullable|array|max:4',
            'thumbnail_titles.*' => 'nullable|string|max:255',
            'thumbnail_descriptions' => 'nullable|array|max:4',
            'thumbnail_descriptions.*' => 'nullable|string|max:1000',
            'hours' => 'required|numeric',
            'minutes' => 'required|numeric',
            'serving_size' => 'nullable|numeric',
            'caption' => 'nullable|string',
            'tags' => 'nullable|array',
            'dietaries' => 'nullable|array',
            'cuisines' => 'nullable|array',
        ]);

        $post = Post::create([
            'title' => $request->title,
            'file' => '',
            'type' => null,
            'hours' => $request->hours,
            'minutes' => $request->minutes,
            'serving_size' => $request->serving_size,
            'caption' => $request->caption,
            'user_id' => $request->user_id,
        ]);

        if ($request->hasFile('file')) {
            $extension = strtolower($request->file('file')->getClientOriginalExtension());

            if (in_array($extension, $videoExtensions)) {
                $tempPath = $request->file('file')->store('uploads/tmp');
                $localFullPath = storage_path('app/' . $tempPath);
                $convertedFileName = pathinfo($tempPath, PATHINFO_FILENAME) . '.mp4';

                ConvertVideo::dispatch($localFullPath, 'mp4', $convertedFileName, $post->id);

                $fileType = 'video';
                $fileSrc = 'uploads/posts/videos/' . $convertedFileName;
            } else {
                $fileSrc = Storage::disk('s3')->putFile('uploads/posts/images', $request->file('file'), 'public');
                $fileType = 'image';
            }

            $post->update([
                'file' => $fileSrc,
                'type' => $fileType,
            ]);
        }

        if ($request->hasFile('thumbnails')) {
            foreach ($request->file('thumbnails') as $index => $thumbnailFile) {
                $filePath = null;
                $fileType = null;

                $thumbExt = strtolower($thumbnailFile->getClientOriginalExtension());

                $postThumbnail = PostThumbnail::create([
                    'post_id' => $post->id,
                    'file' => '',
                    'thumbnail' => '',
                    'type' => '',
                    'file_type' => '',
                    'title' => $request->thumbnail_titles[$index] ?? null,
                    'description' => $request->thumbnail_descriptions[$index] ?? null,
                ]);

                if (in_array($thumbExt, $imageExtensions)) {
                    $thumbPath = Storage::disk('s3')->putFile('uploads/posts/thumbnails/images', $thumbnailFile, 'public');
                    $thumbType = 'image';

                    $postThumbnail->update([
                        'thumbnail' => $thumbPath,
                        'type' => $thumbType,
                    ]);
                } elseif (in_array($thumbExt, $videoExtensions)) {
                    $tempThumbPath = $thumbnailFile->store('uploads/tmp');
                    $localFullPath = storage_path('app/' . $tempThumbPath);
                    $convertedFileName = pathinfo($tempThumbPath, PATHINFO_FILENAME) . '.mp4';

                    ConvertVideo::dispatch($localFullPath, 'mp4', $convertedFileName, $post->id, $postThumbnail->id);

                    $thumbType = 'video';

                    $postThumbnail->update([
                        'type' => $thumbType,
                    ]);
                }

                if ($request->hasFile("thumbnails_files.$index")) {
                    $file = $request->file("thumbnails_files.$index");
                    $fileExt = strtolower($file->getClientOriginalExtension());

                    if (in_array($fileExt, $imageExtensions)) {
                        $filePath = Storage::disk('s3')->putFile('uploads/posts/thumbnails/files/images', $file, 'public');
                        $fileType = 'image';
                    } elseif (in_array($fileExt, $videoExtensions)) {
                        $tempFilePath = $file->store('uploads/tmp');
                        $localFullPath = storage_path('app/' . $tempFilePath);
                        $convertedFileName = pathinfo($tempFilePath, PATHINFO_FILENAME) . '.mp4';

                        ConvertVideo::dispatch($localFullPath, 'mp4', $convertedFileName, $post->id, $postThumbnail->id);
                        $filePath = 'uploads/posts/thumbnails/files/videos/' . $convertedFileName;
                        $fileType = 'video';
                    }

                    $postThumbnail->update([
                        'file' => $filePath,
                        'file_type' => $fileType,
                    ]);
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

        PostIngredient::where('post_id', $post->id)->delete();

        if ($request->has('ingredients') && is_array($request->ingredients)) {
            foreach ($request->ingredients as $ingredient) {
                $ingredientModel = Ingredient::find($ingredient['id']);

                PostIngredient::create([
                    'post_id' => $post->id,
                    'ingredient_id' => $ingredient['id'],
                    'measurement' => $ingredient['measurement'],
                    'user_id' => auth()->id(),
                    'name' => $ingredientModel->name ?? '',
                    'type' => $ingredientModel->type ?? '',
                ]);
            }
        }

        return redirect()->route('admin.post.index')->with('success', 'Post created successfully.');
    }

    public function show($id)
    {
        $post = Post::with(['instruction', 'tags', 'dietaries', 'cuisines'])->find($id);

        if (!$post) {
            error_session('post not found');
            return redirect()->route('admin.post.index');
        }

        $ingredients = PostIngredient::where('post_id', $id)->get();

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
        $ingredients = Ingredient::all();
        $postIngredients = PostIngredient::where('post_id', $post->id)->get();

        return view('admin.post.edit', [
            'title' => 'Edit post',
            'data' => $post,
            'tags' => $tags,
            'dietaries' => $dietaries,
            'cuisines' => $cuisines,
            'ingredients' => $ingredients,
            'postIngredients' => $postIngredients,
            'selectedTagIds' => $post->tags->pluck('id')->toArray(),
            'selectedDietaryIds' => $post->dietaries->pluck('id')->toArray(),
            'selectedCuisineIds' => $post->cuisines->pluck('id')->toArray(),
            'breadcrumb' => breadcrumb([
                'post' => route('admin.post.index'),
                'edit' => route('admin.post.edit', $id)
            ]),
            'urlPath' => parse_url($post->file, PHP_URL_PATH),
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
                'title' => 'required|string|max:255',
                'caption' => 'nullable|string',
                'serving_size' => 'nullable|numeric',
                'file' => 'nullable|file|mimetypes:video/mp4,video/x-msvideo,video/quicktime,image/jpeg,image/png,image/gif,image/webp|max:51200',
                'thumbnail' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,avi,mov,mkv,wmv,flv|max:20480',
                'hours' => 'required|numeric',
                'minutes' => 'required|numeric',
                'tags' => 'nullable|array',
                'dietaries' => 'nullable|array',
                'cuisines' => 'nullable|array',
            ]);

            if ($request->hasFile('file')) {
                $extension = strtolower($request->file('file')->getClientOriginalExtension());

                if (in_array($extension, $videoExtensions)) {
                    $tempPath = $request->file('file')->store('uploads/tmp');
                    $localFullPath = storage_path('app/' . $tempPath);
                    $convertedFileName = pathinfo($tempPath, PATHINFO_FILENAME) . '.mp4';

                    ConvertVideo::dispatch($localFullPath, 'mp4', $convertedFileName, $post->id);

                    $fileSrc = 'uploads/posts/videos/' . $convertedFileName;
                } elseif (in_array($extension, $imageExtensions)) {
                    $fileSrc = $request->file('file')->store('uploads/posts/images', 's3');
                }
            }

            if ($request->hasFile('thumbnail')) {
                $extension = strtolower($request->file('thumbnail')->getClientOriginalExtension());

                if (in_array($extension, $videoExtensions)) {
                    $tempPath = $request->file('thumbnail')->store('uploads/tmp');
                    $localFullPath = storage_path('app/' . $tempPath);
                    $convertedFileName = pathinfo($tempPath, PATHINFO_FILENAME) . '.mp4';

                    ConvertVideo::dispatch($localFullPath, 'mp4', $convertedFileName, $post->id);

                    $thumbnailSrc = 'uploads/posts/thumbnails/videos/' . $convertedFileName;
                } elseif (in_array($extension, $imageExtensions)) {
                    $thumbnailSrc = $request->file('thumbnail')->store('uploads/posts/thumbnails/images', 's3');
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

            PostIngredient::where('post_id', $post->id)->delete();

            if ($request->has('ingredients') && is_array($request->ingredients)) {
                foreach ($request->ingredients as $ingredient) {
                    $ingredientModel = Ingredient::find($ingredient['id']);

                    PostIngredient::create([
                        'post_id' => $post->id,
                        'ingredient_id' => $ingredient['id'],
                        'measurement' => $ingredient['measurement'],
                        'user_id' => auth()->id(),
                        'name' => $ingredientModel->name ?? '',
                        'type' => $ingredientModel->type ?? '',
                    ]);
                }
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
        $data = PostIngredient::where('post_id',$request->id)->get();

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

    public function postDetailsCreate($post_id)
    {
        return view('admin.post.post_details_create', [
            'title' => 'Create Ingredient',
            'post_id' => $post_id,
            'breadcrumb' => breadcrumb([
                'post' => route('admin.post.index'),
                'create' => route('admin.post.create')
            ]),
        ]);
    }

    public function postDetailsStore(Request $request, $post_id)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'type'        => 'nullable|string|max:255',
            'measurement' => 'nullable|string|max:255',
        ]);

        $ingredient = new PostIngredient();
        $ingredient->post_id = $post_id;
        $ingredient->user_id = auth()->id();
        $ingredient->name = $validated['name'];
        $ingredient->type = $validated['type'] ?? '';
        $ingredient->measurement = $validated['measurement'] ?? '';
        $ingredient->save();

        success_session('Ingredients updated successfully');

        return redirect()->route('admin.post.show', $post_id);
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
                'data' => $data
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
        $data = PostIngredient::where('id', $id)->first();

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

        if (in_array($extension, $imageExtensions)) {
            $directory = ($type === 'file') ? 'uploads/posts/images' : 'uploads/posts/thumbnails/images'; // Handle file and thumbnail images
        }
        elseif (in_array($extension, $videoExtensions)) {
            $directory = ($type === 'file') ? 'uploads/posts/videos' : 'uploads/posts/thumbnails/videos'; // Handle file and thumbnail videos
        } else {
            return false;
        }

        $path = $file->store($directory, 's3');

        if ($path) {
            return $path;
        }

        return false;
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

    public function destroyFile(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        if ($post->file && Storage::exists($post->file)) {
            Storage::delete($post->file);
        }

        Storage::disk('s3')->delete($post->file);
        $post->file = null;

        $post->save();

        return response()->json(['message' => 'File deleted successfully']);
    }

}

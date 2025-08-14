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
        $videoExtensions = ['mp4', 'avi', 'mov', 'mkv', 'wmv', 'flv', 'm4v'];

        $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,bmp,mp4,avi,mov,mkv,wmv,flv,m4v|max:51200',
            'thumbnail' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,bmp,mp4,avi,mov,mkv,wmv,flv,m4v|max:51200',
            'thumbnails' => 'nullable|array|max:4',
            'thumbnails.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,bmp,mp4,avi,mov,mkv,wmv,flv,m4v|max:40960',
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
            'thumbnail' => '',
            'type' => null,
            'hours' => $request->hours,
            'minutes' => $request->minutes,
            'serving_size' => $request->serving_size,
            'caption' => $request->caption,
            'user_id' => $request->user_id,
            'conversion_status' => null,
        ]);

        if ($request->hasFile('file')) {
            $extension = strtolower($request->file('file')->getClientOriginalExtension());

            if (in_array($extension, $videoExtensions)) {
                $file = $request->file('file');

                if (!$file->isValid()) {
                    return back()->withErrors(['file' => 'The file is corrupt or not downloaded.']);
                }

                $localFilename = uniqid('video_') . '.' . $extension;

                $localPath = $file->storeAs('public/tmp_for_processing', $localFilename);

                $post->update([
                    'file' => $localPath,
                    'type' => 'video',
                    'conversion_status' => 'processing',
                ]);

                ConvertVideo::dispatch(storage_path('app/' . $localPath), 'mp4', pathinfo($localFilename, PATHINFO_FILENAME) . '.mp4', $post->id);
            } else {
                $fileSrc = Storage::disk('s3')->putFile('uploads/posts/images', $request->file('file'), 'public');
                $post->update([
                    'file' => $fileSrc,
                    'type' => 'image',
                    'conversion_status' => null,
                ]);
            }
        }

        if ($request->hasFile('thumbnail')) {
            $extension = strtolower($request->file('thumbnail')->getClientOriginalExtension());

            if (in_array($extension, $videoExtensions)) {
                $thumbnailFile = $request->file('thumbnail');

                if (!$thumbnailFile->isValid()) {
                    return back()->withErrors(['thumbnail' => 'The thumbnail file is corrupt or not downloaded.']);
                }

                $localFilename = uniqid('video_thumb_') . '.' . $extension;

                $localPath = $thumbnailFile->storeAs('public/tmp_for_processing', $localFilename);

                $post->update([
                    'thumbnail' => $localPath,
                ]);

                //ConvertVideo::dispatch(storage_path('app/' . $localPath), 'mp4', pathinfo($localFilename, PATHINFO_FILENAME) . '.mp4', $post->id, null, true);
            } else {
                $fileSrc = Storage::disk('s3')->putFile('uploads/posts/thumbnails/images', $request->file('thumbnail'), 'public');
                $post->update([
                    'thumbnail' => $fileSrc,
                ]);
            }
        }

        if ($request->hasFile('thumbnails')) {
            foreach ($request->file('thumbnails') as $index => $thumbnailFile) {
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
                    $postThumbnail->update([
                        'thumbnail' => $thumbPath,
                        'type' => 'image',
                    ]);
                } elseif (in_array($thumbExt, $videoExtensions)) {
                    if (!$thumbnailFile->isValid()) {
                        return back()->withErrors(['thumbnail' => 'The thumbnail video is corrupt or not downloaded.']);
                    }

                    $localFilename = uniqid('thumb_', true) . '.' . $thumbExt;

                    $localPath = $thumbnailFile->storeAs('public/tmp_for_processing', $localFilename);

                    $postThumbnail->update([
                        'thumbnail' => $localPath,
                        'type' => 'video',
                    ]);

                    ConvertVideo::dispatch(storage_path('app/' . $localPath), 'mp4', pathinfo($localFilename, PATHINFO_FILENAME) . '.mp4', $post->id, $postThumbnail->id);
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
        $videoExtensions = ['mp4', 'avi', 'mov', 'mkv', 'wmv', 'flv', 'm4v'];

        if (!$post) {
            return redirect()->route('admin.post.index')
                ->with('error', 'Post not found');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,bmp,mp4,avi,mov,mkv,wmv,flv,m4v|max:51200',
            'thumbnail' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,bmp,mp4,avi,mov,mkv,wmv,flv,m4v|max:51200',
            'thumbnails' => 'nullable|array|max:4',
            'thumbnails.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,bmp,mp4,avi,mov,mkv,wmv,flv,m4v|max:40960',
            'hours' => 'required|numeric',
            'minutes' => 'required|numeric',
            'serving_size' => 'nullable|numeric',
            'caption' => 'nullable|string',
            'tags' => 'nullable|array',
            'dietaries' => 'nullable|array',
            'cuisines' => 'nullable|array',
        ]);

        $postData = [
            'title' => $request->title,
            'caption' => $request->caption,
            'serving_size' => $request->serving_size,
            'hours' => $request->hours,
            'minutes' => $request->minutes,
        ];

        // Обробка video/image для поля file
        if ($request->hasFile('file')) {
            $extension = strtolower($request->file('file')->getClientOriginalExtension());

            if (in_array($extension, $videoExtensions)) {
                $originalFileName = uniqid('video_', true) . '.' . $extension;
                $s3TempPath = 'uploads/tmp/' . $originalFileName;

                // Завантажуємо оригінал відео на S3
                Storage::disk('s3')->putFileAs('uploads/tmp', $request->file('file'), $originalFileName, 'public');

                $postData['file'] = $s3TempPath;
                $postData['type'] = 'video';
                $postData['conversion_status'] = 'processing';

                // Локальне кешування для конвертації
                $localFullPath = storage_path('app/tmp_for_processing/' . $originalFileName);
                if (!file_exists(dirname($localFullPath))) {
                    mkdir(dirname($localFullPath), 0755, true);
                }
                file_put_contents($localFullPath, file_get_contents(Storage::disk('s3')->url($s3TempPath)));

                $convertedFileName = pathinfo($originalFileName, PATHINFO_FILENAME) . '.mp4';

                ConvertVideo::dispatch($localFullPath, 'mp4', $convertedFileName, $post->id);
            } elseif (in_array($extension, $imageExtensions)) {
                $fileSrc = $request->file('file')->store('uploads/posts/images', 's3');
                $postData['file'] = $fileSrc;
                $postData['type'] = 'image';
                $postData['conversion_status'] = null;
            }
        }

        // Обробка thumbnail
        if ($request->hasFile('thumbnail')) {
            $extension = strtolower($request->file('thumbnail')->getClientOriginalExtension());

            if (in_array($extension, $videoExtensions)) {
                $originalFileName = uniqid('video_thumb_', true) . '.' . $extension;
                $s3TempPath = 'uploads/tmp/' . $originalFileName;

                Storage::disk('s3')->putFileAs('uploads/tmp', $request->file('thumbnail'), $originalFileName, 'public');

                $postData['thumbnail'] = $s3TempPath;

                $localFullPath = storage_path('app/tmp_for_processing/' . $originalFileName);
                if (!file_exists(dirname($localFullPath))) {
                    mkdir(dirname($localFullPath), 0755, true);
                }
                file_put_contents($localFullPath, file_get_contents(Storage::disk('s3')->url($s3TempPath)));

                $convertedFileName = pathinfo($originalFileName, PATHINFO_FILENAME) . '.mp4';

                ConvertVideo::dispatch($localFullPath, 'mp4', $convertedFileName, $post->id);
            } elseif (in_array($extension, $imageExtensions)) {
                $fileSrc = Storage::disk('s3')->putFile('uploads/posts/thumbnails/images', $request->file('thumbnail'), 'public');
                $postData['thumbnail'] = $fileSrc;
            }
        }

        $post->update($postData);

        // Обробка thumbnails (нові)
        if ($request->hasFile('thumbnails')) {
            foreach ($request->file('thumbnails') as $index => $thumbnailFile) {
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
                    $postThumbnail->update([
                        'thumbnail' => $thumbPath,
                        'type' => 'image',
                    ]);
                } elseif (in_array($thumbExt, $videoExtensions)) {
                    if (!$thumbnailFile->isValid()) {
                        return back()->withErrors(['thumbnail' => 'The thumbnail video is corrupt or not downloaded.']);
                    }

                    $localFilename = uniqid('thumb_', true) . '.' . $thumbExt;
                    $localPath = $thumbnailFile->storeAs('public/tmp_for_processing', $localFilename);

                    $postThumbnail->update([
                        'thumbnail' => $localPath,
                        'type' => 'video',
                    ]);

                    ConvertVideo::dispatch(storage_path('app/' . $localPath), 'mp4', pathinfo($localFilename, PATHINFO_FILENAME) . '.mp4', $post->id, $postThumbnail->id);
                }
            }
        }

        // Синхронізація тегів, дієт, кухонь
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

        // Оновлення інгредієнтів
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
            $directory = ($type === 'file') ? 'uploads/posts/images' : 'uploads/posts/thumbnails/images';
        }
        elseif (in_array($extension, $videoExtensions)) {
            $directory = ($type === 'file') ? 'uploads/posts/videos' : 'uploads/posts/thumbnails/videos';
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

    public function destroyThumbnail(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        if ($post->thumbnail && Storage::exists($post->thumbnail)) {
            Storage::delete($post->thumbnail);
        }

        Storage::disk('s3')->delete($post->thumbnail);
        $post->thumbnail = null;

        $post->save();

        return response()->json(['message' => 'thumbnail deleted successfully']);
    }

}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostThumbnailResource;
use App\PostThumbnail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostThumbnailController extends Controller
{


    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpeg,png,jpg,gif,mp4,avi,mov,mkv,wmv,flv|max:51200',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'post_id' => 'required|exists:posts,id',
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
//        $videoExtensions = ['mp4', 'avi', 'mov', 'mkv', 'wmv', 'flv'];

        $type = in_array($extension, $imageExtensions) ? 'image' : 'video';

        $folder = $type === 'image'
            ? 'uploads/posts/thumbnails/images'
            : 'uploads/posts/thumbnails/videos';

        $path = Storage::disk('s3')->putFile($folder, $file, 'public');

        PostThumbnail::create([
            'post_id' => $request->post_id,
            'title' => $request->title,
            'description' => $request->description,
            'type' => $type,
            'thumbnail' => Storage::disk('s3')->url($path),
        ]);

        return back()->with('success', 'Thumbnail added.');
    }

    public function update(Request $request, $id)
    {
        $thumbnail = PostThumbnail::findOrFail($id);

        $request->validate([
            'file' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,avi,mov,mkv,wmv,flv|max:51200',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $thumbnail->title = $request->title;
        $thumbnail->description = $request->description;

        if ($request->hasFile('file')) {
            if ($thumbnail->thumbnail) {
                $oldPath = parse_url($thumbnail->thumbnail, PHP_URL_PATH);
                $oldPath = ltrim($oldPath, '/');
                Storage::disk('s3')->delete($oldPath);
            }

            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());

            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
//            $videoExtensions = ['mp4', 'avi', 'mov', 'mkv', 'wmv', 'flv'];

            $type = in_array($extension, $imageExtensions) ? 'image' : 'video';

            $folder = $type === 'image'
                ? 'uploads/posts/thumbnails/images'
                : 'uploads/posts/thumbnails/videos';

            $path = Storage::disk('s3')->putFile($folder, $file, 'public');

            $thumbnail->type = $type;
            $thumbnail->thumbnail = Storage::disk('s3')->url($path);
        }

        $thumbnail->save();

        return back()->with('success', 'Thumbnail updated.');
    }

    public function delete($id)
    {
        $thumbnail = PostThumbnail::findOrFail($id);

        if ($thumbnail->thumbnail) {
            $path = parse_url($thumbnail->thumbnail, PHP_URL_PATH);
            $path = ltrim($path, '/'); 
            Storage::disk('s3')->delete($path);
        }

        $thumbnail->delete();

        return back()->with('success', 'Thumbnail deleted.');
    }

}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ConvertVideo;
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
        $isImage = in_array($extension, $imageExtensions);
        $type = $isImage ? 'image' : 'video';

        // Створюємо запис без шляху, щоб отримати ID
        $postThumbnail = PostThumbnail::create([
            'post_id' => $request->post_id,
            'title' => $request->title,
            'description' => $request->description,
            'type' => $type,
            'thumbnail' => '',
        ]);

        if ($isImage) {
            $path = Storage::disk('s3')->putFile('uploads/posts/thumbnails/images', $file, 'public');
            $postThumbnail->thumbnail = Storage::disk('s3')->url($path);
            $postThumbnail->save();
        } else {
            $tempPath = $file->store('uploads/tmp');
            $localFullPath = storage_path('app/' . $tempPath);
            $convertedFileName = Str::random(40) . '.mp4';

            // Запускаємо компресію відео
            ConvertVideo::dispatch($localFullPath, 'mp4', $convertedFileName, $request->post_id, $postThumbnail->id);

            // Тимчасово зберігаємо майбутній шлях
            $futurePath = 'uploads/posts/thumbnails/videos/' . $convertedFileName;
            $postThumbnail->thumbnail = Storage::disk('s3')->url($futurePath);
            $postThumbnail->save();
        }

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
            $isImage = in_array($extension, $imageExtensions);
            $type = $isImage ? 'image' : 'video';
            $thumbnail->type = $type;

            if ($isImage) {
                $path = Storage::disk('s3')->putFile('uploads/posts/thumbnails/images', $file, 'public');
                $thumbnail->thumbnail = Storage::disk('s3')->url($path);
            } else {
                $tempPath = $file->store('uploads/tmp');
                $localFullPath = storage_path('app/' . $tempPath);
                $convertedFileName = Str::random(40) . '.mp4';

                // Запускаємо компресію відео
                ConvertVideo::dispatch($localFullPath, 'mp4', $convertedFileName, $thumbnail->post_id, $thumbnail->id);

                $futurePath = 'uploads/posts/thumbnails/videos/' . $convertedFileName;
                $thumbnail->thumbnail = Storage::disk('s3')->url($futurePath);
            }
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

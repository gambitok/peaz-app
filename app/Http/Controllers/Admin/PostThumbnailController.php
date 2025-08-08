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
            'file' => 'required|file|mimes:jpeg,png,jpg,gif,bmp,webp,mp4,avi,mov,mkv,wmv,flv,m4v|max:51200',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'post_id' => 'required|exists:posts,id',
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        $isImage = in_array($extension, $imageExtensions);

        $postThumbnail = PostThumbnail::create([
            'post_id' => $request->post_id,
            'title' => $request->title,
            'description' => $request->description,
            'type' => $isImage ? 'image' : 'video',
            'thumbnail' => '',
        ]);

        if ($isImage) {
            // Зберігаємо зображення безпосередньо в S3
            $path = Storage::disk('s3')->putFile('uploads/posts/thumbnails/images', $file, 'public');
            $postThumbnail->thumbnail = $path; // зберігаємо відносний шлях, без URL
            $postThumbnail->save();
        } else {
            // Для відео — завантажуємо оригінал у тимчасову папку S3
            $tempOriginalName = uniqid('thumb_', true) . '.' . $extension;
            $s3TempPath = 'uploads/tmp/' . $tempOriginalName;

            Storage::disk('s3')->put($s3TempPath, file_get_contents($file), 'public');

            // Створюємо локальну копію для ffmpeg конвертації
            $localFullPath = storage_path('app/tmp_for_processing/' . $tempOriginalName);
            if (!file_exists(dirname($localFullPath))) {
                mkdir(dirname($localFullPath), 0755, true);
            }
            file_put_contents($localFullPath, file_get_contents(Storage::disk('s3')->url($s3TempPath)));

            $convertedFileName = Str::random(40) . '.mp4';

            // Запускаємо конвертацію через job
            ConvertVideo::dispatch($localFullPath, 'mp4', $convertedFileName, $request->post_id, $postThumbnail->id);

            // Зберігаємо в базу тимчасовий шлях до оригінального файлу на S3 (не URL)
            $postThumbnail->thumbnail = $s3TempPath;
            $postThumbnail->type = 'video';
            $postThumbnail->save();
        }

        return back()->with('success', 'Thumbnail added.');
    }

    public function update(Request $request, $id)
    {
        $thumbnail = PostThumbnail::findOrFail($id);

        $request->validate([
            'file' => 'nullable|file|mimes:jpeg,png,jpg,gif,bmp,webp,mp4,avi,mov,mkv,wmv,flv,m4v|max:51200',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $thumbnail->title = $request->title;
        $thumbnail->description = $request->description;

        if ($request->hasFile('file')) {
            // Видаляємо старий файл із S3, якщо він є
            if ($thumbnail->thumbnail) {
                Storage::disk('s3')->delete($thumbnail->thumbnail);
            }

            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());

            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
            $isImage = in_array($extension, $imageExtensions);

            $thumbnail->type = $isImage ? 'image' : 'video';

            if ($isImage) {
                $path = Storage::disk('s3')->putFile('uploads/posts/thumbnails/images', $file, 'public');
                $thumbnail->thumbnail = $path; // відносний шлях
                $thumbnail->save();
            } else {
                // Для відео — завантажуємо оригінал у тимчасову папку S3
                $tempOriginalName = uniqid('thumb_', true) . '.' . $extension;
                $s3TempPath = 'uploads/tmp/' . $tempOriginalName;

                Storage::disk('s3')->put($s3TempPath, file_get_contents($file), 'public');

                // Локальна копія для конвертації
                $localFullPath = storage_path('app/tmp_for_processing/' . $tempOriginalName);
                if (!file_exists(dirname($localFullPath))) {
                    mkdir(dirname($localFullPath), 0755, true);
                }
                file_put_contents($localFullPath, file_get_contents(Storage::disk('s3')->url($s3TempPath)));

                $convertedFileName = Str::random(40) . '.mp4';

                ConvertVideo::dispatch($localFullPath, 'mp4', $convertedFileName, $thumbnail->post_id, $thumbnail->id);

                $thumbnail->thumbnail = $s3TempPath; // тимчасовий шлях до оригіналу
                $thumbnail->save();
            }
        } else {
            $thumbnail->save();
        }

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

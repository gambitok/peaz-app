<?php

namespace App\Services;

use App\Billboard;
use Illuminate\Support\Facades\Storage;

class BillboardService
{
    public function store(array $data)
    {
        $data['link'] = $data['link'] ?? '';
        $data['status'] = $data['status'] ?? false;

        $billboard = Billboard::create($data);

        if (isset($data['file'])) {
            $data['file'] = $this->uploadFile($data['file'], $billboard->id);
        }
        if (isset($data['logo_file'])) {
            $data['logo_file'] = $this->uploadFile($data['logo_file'], $billboard->id);
        }
        if (isset($data['horizontal_file'])) {
            $data['horizontal_file'] = $this->uploadFile($data['horizontal_file'], $billboard->id);
        }
        if (isset($data['video_file'])) {
            $data['video_file'] = $this->uploadFile($data['video_file'], $billboard->id);
        }

        $billboard->update($data);
        return $billboard;
    }

    public function update(Billboard $billboard, array $data)
    {
        if (isset($data['file'])) {
            Storage::disk('s3')->delete($billboard->file);
            $data['file'] = $this->uploadFile($data['file'], $billboard->id);
        }
        if (isset($data['logo_file'])) {
            Storage::disk('s3')->delete($billboard->logo_file);
            $data['logo_file'] = $this->uploadFile($data['logo_file'], $billboard->id);
        }
        if (isset($data['horizontal_file'])) {
            Storage::disk('s3')->delete($billboard->horizontal_file);
            $data['horizontal_file'] = $this->uploadFile($data['horizontal_file'], $billboard->id);
        }
        if (isset($data['video_file'])) {
            Storage::disk('s3')->delete($billboard->video_file);
            $data['video_file'] = $this->uploadFile($data['video_file'], $billboard->id);
        }

        $billboard->update($data);
        return $billboard;
    }

    public function delete(Billboard $billboard)
    {
        Storage::disk('s3')->delete($billboard->file);
        Storage::disk('s3')->delete($billboard->logo_file);
        Storage::disk('s3')->delete($billboard->horizontal_file);
        Storage::disk('s3')->delete($billboard->video_file);
        $billboard->delete();
    }

    private function uploadFile($file, $billboardId)
    {
        $path = $file->storeAs("uploads/billboards/$billboardId", time() . '.' . $file->getClientOriginalExtension(), 's3');
        Storage::disk('s3')->setVisibility($path, 'public');

        return $path;
    }
}

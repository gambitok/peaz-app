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

        if (isset($data['file'])) {
            $data['file'] = $this->uploadFile($data['file']);
        }

        return Billboard::create($data);
    }

    public function update(Billboard $billboard, array $data)
    {
        if (isset($data['file'])) {
            Storage::disk('s3')->delete($billboard->file);
            $data['file'] = $this->uploadFile($data['file']);
        }

        $billboard->update($data);
        return $billboard;
    }

    public function delete(Billboard $billboard)
    {
        Storage::disk('s3')->delete($billboard->file);
        $billboard->delete();
    }

    private function uploadFile($file)
    {
        $path = $file->storeAs('uploads/billboards', time() . '.' . $file->getClientOriginalExtension(), 's3');
        Storage::disk('s3')->setVisibility($path, 'public');

        return $path;
    }
}

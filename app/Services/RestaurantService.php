<?php

namespace App\Services;

use App\Restaurant;
use Illuminate\Support\Facades\Storage;

class RestaurantService
{
    public function store(array $data)
    {
        $data['link'] = $data['link'] ?? '';
        $data['status'] = $data['status'] ?? false;

        $restaurant = Restaurant::create($data);

        if (isset($data['file'])) {
            $data['file'] = $this->uploadFile($data['file'], $restaurant->id);
        }

        $restaurant->update($data);
        return $restaurant;
    }

    public function update(Restaurant $restaurant, array $data)
    {
        if (isset($data['file'])) {
            Storage::disk('s3')->delete($restaurant->file);
            $data['file'] = $this->uploadFile($data['file'], $restaurant->id);
        }

        $restaurant->update($data);
        return $restaurant;
    }

    public function delete(Restaurant $restaurant)
    {
        Storage::disk('s3')->delete($restaurant->file);
        $restaurant->delete();
    }

    private function uploadFile($file, $restaurantId)
    {
        $path = $file->storeAs("uploads/restaurants/$restaurantId", time() . '.' . $file->getClientOriginalExtension(), 's3');
        Storage::disk('s3')->setVisibility($path, 'public');

        return $path;
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RestaurantResource;
use App\Restaurant;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    public function index()
    {
        return RestaurantResource::collection(Restaurant::all());
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'file' => 'nullable|string|max:255',
            'link' => 'nullable|string|max:255',
            'status' => 'boolean',
        ]);

        $restaurant = Restaurant::create($validatedData);
        return new RestaurantResource($restaurant);
    }

    public function show(Restaurant $restaurant)
    {
        return new RestaurantResource($restaurant);
    }

    public function update(Request $request, Restaurant $restaurant)
    {
        $validatedData = $request->validate([
            'file' => 'nullable|string|max:255',
            'link' => 'nullable|string|max:255',
            'status' => 'boolean',
        ]);

        $restaurant->update($validatedData);
        return new RestaurantResource($restaurant);
    }

    public function destroy(Restaurant $restaurant)
    {
        $restaurant->delete();
        return response()->noContent();
    }
}

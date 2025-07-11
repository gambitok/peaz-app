<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Restaurant;
use App\Http\Requests\RestaurantRequest;
use App\Services\RestaurantService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class RestaurantViewController extends Controller
{
    protected $restaurantService;

    public function __construct(RestaurantService $restaurantService)
    {
        $this->restaurantService = $restaurantService;
    }

    public function index()
    {
        $restaurants = Restaurant::with('user')->get();
        return view('admin.restaurants.index', [
            'restaurants' => $restaurants,
            'title' => 'Restaurants',
            'breadcrumb' => breadcrumb([
                'Restaurants' => route('admin.restaurants.index'),
            ]),
        ]);
    }

    public function show($id)
    {
        $restaurant = Restaurant::with('user')->findOrFail($id);
        return view('admin.restaurants.show', compact('restaurant'));
    }

    public function create()
    {
        return view('admin.restaurants.create');
    }

    public function edit($id)
    {
        $restaurant = Restaurant::findOrFail($id);
        return view('admin.restaurants.edit', compact('restaurant'));
    }

    public function store(RestaurantRequest $request)
    {
        $validatedData = $request->validated();
        $userId = $request->user()->id;

        $validatedData['status'] = isset($validatedData['status']) ? (int) filter_var($validatedData['status'], FILTER_VALIDATE_BOOLEAN) : 0;

        $restaurant = Restaurant::create(['user_id' => $userId] + $validatedData);
        $restaurantId = $restaurant->id;

        $fileFields = ['file'];

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $relativePath = Storage::disk('s3')->putFile("uploads/restaurants/$restaurantId", $request->file($field), 'public');
                Log::info("File uploaded to S3: $relativePath");
                Log::info("File rest_id: $restaurantId");
                $validatedData[$field] = $relativePath;
            }
        }

        $restaurant->update($validatedData);

        return redirect()->route('admin.restaurants.index')->with('success', 'Restaurant created successfully.');
    }

    public function update(RestaurantRequest $request, $id)
    {
        $restaurant = Restaurant::findOrFail($id);
        $validatedData = $request->validated();
        $restaurantId = $restaurant->id;

        $validatedData['status'] = isset($validatedData['status']) ? (int) filter_var($validatedData['status'], FILTER_VALIDATE_BOOLEAN) : 0;

        $fileFields = ['file'];

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $path = Storage::disk('s3')->putFile("uploads/restaurants/$restaurantId", $request->file($field), 'public');
                $validatedData[$field] = $path;
            } else {
                $validatedData[$field] = $restaurant->getRawOriginal($field);
            }
        }

        $validatedData['status'] = filter_var($validatedData['status'] ?? false, FILTER_VALIDATE_BOOLEAN);

        try {
            $restaurant->update($validatedData);
            return redirect()->route('admin.restaurants.index')->with('success', 'Restaurant updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to update Restaurant.']);
        }
    }

    public function destroy($id)
    {
        $restaurant = Restaurant::findOrFail($id);
        $this->restaurantService->delete($restaurant);

        return redirect()->route('admin.restaurants.index')->with('success', 'Restaurant deleted successfully.');
    }
}

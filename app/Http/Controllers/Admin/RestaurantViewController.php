<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Restaurant;
use App\Http\Requests\RestaurantRequest;
use App\Services\RestaurantService;
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

        // Convert status to boolean and then to an integer (0 or 1)
        $validatedData['status'] = isset($validatedData['status']) ? (int) filter_var($validatedData['status'], FILTER_VALIDATE_BOOLEAN) : 0;


        // Create a new Restaurant entry to get its ID
        $restaurant = Restaurant::create(['user_id' => $userId] + $validatedData);
        $restaurantId = $restaurant->id;

        // Array to store file fields
        $fileFields = ['file'];

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $path = $file->storeAs("uploads/restaurants/$restaurantId", time() . '.' . $file->getClientOriginalExtension(), 's3');
                Storage::disk('s3')->setVisibility($path, 'public');
                $validatedData[$field] = $path;
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

        // Convert status to boolean and then to an integer (0 or 1)
        $validatedData['status'] = isset($validatedData['status']) ? (int) filter_var($validatedData['status'], FILTER_VALIDATE_BOOLEAN) : 0;

        // Array to update file fields
        $fileFields = ['file'];

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $path = $file->storeAs("uploads/restaurants/$restaurantId", time() . '.' . $file->getClientOriginalExtension(), 's3');
                Storage::disk('s3')->setVisibility($path, 'public');
                $validatedData[$field] = $path;
            } else {
                $validatedData[$field] = $restaurant->getRawOriginal($field);
            }
        }

        // Convert status to boolean
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

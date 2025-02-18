<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\WebController;
use App\Http\Controllers\Api\RestaurantController as ApiRestaurantController;
use App\Restaurant;
use App\Http\Resources\RestaurantResource;
use Illuminate\Http\Request;

class RestaurantViewController extends WebController
{
    protected $apiRestaurantController;

    public function __construct(ApiRestaurantController $apiRestaurantController)
    {
        $this->apiRestaurantController = $apiRestaurantController;
    }

    public function index()
    {
        $restaurants = $this->apiRestaurantController->index()->toArray(request());

        return view('admin.restaurants.index', compact('restaurants'));
    }

    public function show($id)
    {
        $restaurant = $this->apiRestaurantController->show(Restaurant::findOrFail($id))->toArray(request());

        return view('admin.restaurants.show', compact('restaurant'));
    }

    public function create()
    {
        return view('admin.restaurants.create');
    }

    public function store(Request $request)
    {
        $request->merge([
            'status' => $request->has('status'),
        ]);

        $response = $this->apiRestaurantController->store($request);

        if ($response->getStatusCode() === 201) {
            return redirect()->route('admin.restaurants.index')->with('success', 'Restaurant created successfully.');
        } else {
            $error = $response->json();
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to create restaurant.', 'api_error' => $error]);
        }
    }

    public function edit($id)
    {
        $restaurant = $this->apiRestaurantController->show(Restaurant::findOrFail($id))->toArray(request());

        return view('admin.restaurants.edit', compact('restaurant'));
    }

    public function update(Request $request, $id)
    {
        $restaurant = Restaurant::findOrFail($id);

        $request->merge([
            'status' => $request->has('status'),
        ]);

        $response = $this->apiRestaurantController->update($request, $restaurant);

        if ($response instanceof RestaurantResource) {
            return redirect()->route('admin.restaurants.index')->with('success', 'Restaurant updated successfully.');
        } else {
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to update restaurant.']);
        }
    }

    public function destroy($id)
    {
        $restaurant = Restaurant::findOrFail($id);
        $response = $this->apiRestaurantController->destroy($restaurant);

        if ($response->getStatusCode() === 204) {
            return redirect()->route('admin.restaurants.index')->with('success', 'Restaurant deleted successfully.');
        } else {
            return redirect()->back()->withErrors(['error' => 'Failed to delete restaurant.']);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\WebController;
use App\Http\Controllers\Api\CuisineController as ApiCuisineController;
use App\Cuisine;
use App\Http\Resources\CuisineResource;
use Illuminate\Http\Request;

class CuisineViewController extends WebController
{
    protected $apiCuisineController;

    public function __construct(ApiCuisineController $apiCuisineController)
    {
        $this->apiCuisineController = $apiCuisineController;
    }

    public function index()
    {
        $cuisines = $this->apiCuisineController->index()->toArray(request());

        return view('admin.cuisine.index', compact('cuisines'));
    }

    public function show($id)
    {
        $cuisine = $this->apiCuisineController->show(Cuisine::findOrFail($id))->toArray(request());

        return view('admin.cuisine.show', compact('cuisine'));
    }

    public function create()
    {
        return view('admin.cuisine.create');
    }

    public function store(Request $request)
    {
        $response = $this->apiCuisineController->store($request);

        if ($response->getStatusCode() === 201) {
            return redirect()->route('admin.cuisine.index')->with('success', 'Cuisine created successfully.');
        } else {
            $error = $response->json();
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to create Cuisine.', 'api_error' => $error]);
        }
    }

    public function edit($id)
    {
        $cuisine = $this->apiCuisineController->show(Cuisine::findOrFail($id))->toArray(request());

        return view('admin.cuisine.edit', compact('cuisine'));
    }

    public function update(Request $request, $id)
    {
        $cuisine = Cuisine::findOrFail($id);

        $response = $this->apiCuisineController->update($request, $cuisine);

        if ($response instanceof CuisineResource) {
            return redirect()->route('admin.cuisine.index')->with('success', 'Cuisine updated successfully.');
        } else {
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to update Cuisine.']);
        }
    }

    public function destroy($id)
    {
        $cuisine = Cuisine::findOrFail($id);
        $response = $this->apiCuisineController->destroy($cuisine);

        if ($response->getStatusCode() === 204) {
            return redirect()->route('admin.cuisine.index')->with('success', 'Cuisine deleted successfully.');
        } else {
            return redirect()->back()->withErrors(['error' => 'Failed to delete Cuisine.']);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\WebController;
use App\Http\Controllers\Api\DietaryController as ApiDietaryController;
use App\Dietary;
use App\Http\Resources\DietaryResource;
use Illuminate\Http\Request;

class DietaryViewController extends WebController
{
    protected $apiDietaryController;

    public function __construct(ApiDietaryController $apiDietaryController)
    {
        $this->apiDietaryController = $apiDietaryController;
    }

    public function index()
    {
        $dietaries = $this->apiDietaryController->index()->toArray(request());

        return view('admin.dietary.index', [
            'dietaries' => $dietaries,
            'title' => 'Dietaries',
            'breadcrumb' => breadcrumb([
                'Dietaries' => route('admin.dietary.index'),
            ]),
        ]);
    }

    public function show($id)
    {
        $dietary = $this->apiDietaryController->show(Dietary::findOrFail($id))->toArray(request());

        return view('admin.dietary.show', compact('dietary'));
    }

    public function create()
    {
        return view('admin.dietary.create');
    }

    public function store(Request $request)
    {
        $response = $this->apiDietaryController->store($request);

        if ($response->getStatusCode() === 201) {
            return redirect()->route('admin.dietary.index')->with('success', 'Dietary created successfully.');
        } else {
            $error = $response->json();
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to create Dietary.', 'api_error' => $error]);
        }
    }

    public function edit($id)
    {
        $dietary = $this->apiDietaryController->show(Dietary::findOrFail($id))->toArray(request());

        return view('admin.dietary.edit', compact('dietary'));
    }

    public function update(Request $request, $id)
    {
        $dietary = Dietary::findOrFail($id);

        $response = $this->apiDietaryController->update($request, $dietary);

        if ($response instanceof DietaryResource) {
            return redirect()->route('admin.dietary.index')->with('success', 'Dietary updated successfully.');
        } else {
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to update Dietary.']);
        }
    }

    public function destroy($id)
    {
        $dietary = Dietary::findOrFail($id);
        $response = $this->apiDietaryController->destroy($dietary);

        if ($response->getStatusCode() === 204) {
            return redirect()->route('admin.dietary.index')->with('success', 'Dietary deleted successfully.');
        } else {
            return redirect()->back()->withErrors(['error' => 'Failed to delete Dietary.']);
        }
    }
}

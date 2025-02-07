<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\WebController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BillboardViewController extends WebController
{
    public function index()
    {
        $response = Http::get('https://peaz.app/api/billboards');
        $billboards = $response->json()['data'];

        return view('admin.billboards.index', compact('billboards'));
    }

    public function show($id)
    {
        $response = Http::get("https://peaz.app/api/billboards/{$id}");
        $billboard = $response->json()['data'];

        return view('admin.billboards.show', compact('billboard'));
    }

    public function create()
    {
        return view('admin.billboards.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'caption' => 'nullable|string|max:255',
            'file' => 'nullable|string|max:255',
            'link' => 'nullable|string|max:255',
            'tag_id' => 'nullable|exists:tags,id',
            'verified' => 'boolean',
            'status' => 'boolean',
        ]);

        Http::post('https://peaz.app/api/billboards', $validatedData);

        return redirect()->route('admin.billboards.index');
    }

    public function edit($id)
    {
        $response = Http::get("https://peaz.app/api/billboards/{$id}");
        $billboard = $response->json()['data'];

        return view('admin.billboards.edit', compact('billboard'));
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'caption' => 'nullable|string|max:255',
            'file' => 'nullable|string|max:255',
            'link' => 'nullable|string|max:255',
            'tag_id' => 'nullable|exists:tags,id',
            'verified' => 'boolean',
            'status' => 'boolean',
        ]);

        Http::put("https://peaz.app/api/billboards/{$id}", $validatedData);

        return redirect()->route('admin.billboards.index');
    }

    public function destroy($id)
    {
        Http::delete("https://peaz.app/api/billboards/{$id}");

        return redirect()->route('admin.billboards.index');
    }
}

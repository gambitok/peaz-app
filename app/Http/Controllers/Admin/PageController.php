<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::all();
        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        $pages = Page::all();
        return view('admin.pages.create', [
            'title' => 'Page Add',
            'pages'=> $pages,
            'breadcrumb' => breadcrumb([
                'Page' => route('admin.pages.index'),
            ]),
        ]);
    }

    public function edit($id)
    {
        $page = Page::findOrFail($id);

        return view('admin.pages.edit', [
            'title' => 'Page Update',
            'page' => $page,
            'breadcrumb' => breadcrumb([
                'Page' => route('admin.pages.index'),
            ]),
        ]);
    }

    public function update(Request $request, $id)
    {
        $page = Page::findOrFail($id);
        $page->update($request->only('title', 'content'));
        return redirect()->route('admin.pages.index')->with('success', 'Page updated.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'slug' => 'required|string|unique:pages,slug',
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
        ]);

        Page::create($request->only('slug', 'title', 'content'));

        return redirect()->route('admin.pages.index')->with('success', 'Page created.');
    }

    public function destroy($id)
    {
        $page = Page::findOrFail($id);
        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', 'Page deleted.');
    }

}

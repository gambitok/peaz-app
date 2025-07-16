<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function show($slug)
    {
        $page = Page::where('slug', $slug)->firstOrFail();
        return response()->json([
            'title' => $page->title,
            'content' => $page->content,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'slug' => 'required|string|unique:pages,slug',
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
        ]);

        $page = \App\Page::create($validated);

        return response()->json($page, 201);
    }
}

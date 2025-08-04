<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\WebController;
use App\Ingredient;
use Illuminate\Http\Request;
use DataTables;

class IngredientListController extends WebController
{
    public function index()
    {
        $ingredients = Ingredient::all();
        return view('admin.ingredient.index', [
            'ingredients' => $ingredients,
            'title' => 'Ingredients',
            'breadcrumb' => breadcrumb([
                'Ingredients' => route('admin.ingredient.index'),
            ]),
        ]);
    }

    public function listing(Request $request)
    {
        $query = Ingredient::query()->orderBy('name', 'ASC');

        if ($request->has('name') && $request->name !== null) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        if ($request->has('type') && $request->type !== null) {
            $query->where('type', 'like', '%' . $request->type . '%');
        }

        $data = $query->get();

        return datatables()->of($data)
            ->addIndexColumn()
            ->editColumn('name', function ($row) {
                return "<span title='$row->name'>{$row->name}</span>";
            })
            ->editColumn('type', function ($row) {
                return "<span title='$row->type'>{$row->type}</span>";
            })
            ->editColumn('weight', function ($row) {
                return "<span title='$row->weight'>{$row->weight}</span>";
            })
            ->addColumn('action', function ($row) {
                $param = [
                    'id' => $row->id,
                    'url' => [
                        'delete' => route('admin.ingredient.destroy', $row->id),
                        'edit' => route('admin.ingredient.edit', $row->id),
                        'view' => route('admin.ingredient.show', $row->id),
                    ]
                ];
                return $this->generate_actions_buttons($param);
            })
            ->rawColumns(['name', 'type', 'weight', 'action'])
            ->make(true);
    }

    public function create()
    {
        return view('admin.ingredient.create', [
            'title' => 'Create Ingredient',
            'breadcrumb' => breadcrumb([
                'ingredient' => route('admin.ingredient.index'),
                'create' => route('admin.ingredient.create')
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:ingredients,name',
            'type' => 'required|string|max:255',
            'weight' => 'required|numeric',
        ]);

        Ingredient::create([
            'name' => $request->name,
            'type' => $request->type,
            'weight' => $request->weight,
        ]);

        return redirect()->route('admin.ingredient.index')->with('success', 'Ingredient created successfully.');
    }

    public function show($id)
    {
        $ingredient = Ingredient::find($id);

        if (!$ingredient) {
            error_session('Ingredient not found');
            return redirect()->route('admin.ingredient.index');
        }

        return view('admin.ingredient.view', [
            'title' => 'View Ingredient',
            'data' => $ingredient,
            'breadcrumb' => breadcrumb([
                'ingredient' => route('admin.ingredient.index'),
                'view' => route('admin.ingredient.show', $id)
            ]),
        ]);
    }

    public function edit($id)
    {
        $ingredient = Ingredient::find($id);

        if (!$ingredient) {
            error_session('Ingredient not found');
            return redirect()->route('admin.ingredient.index');
        }

        return view('admin.ingredient.edit', [
            'title' => 'Edit Ingredient',
            'data' => $ingredient,
            'breadcrumb' => breadcrumb([
                'ingredient' => route('admin.ingredient.index'),
                'edit' => route('admin.ingredient.edit', $id)
            ]),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $ingredient = Ingredient::find($id);

        if ($ingredient) {
            $request->validate([
                'name' => 'required|string|max:255|unique:ingredients,name,' . $id,
                'type' => 'required|string|max:255',
                'weight' => 'required|numeric',
            ]);

            $ingredient->update([
                'name' => $request->name,
                'type' => $request->type,
                'weight' => $request->weight,
            ]);

            return redirect()->route('admin.ingredient.show', $ingredient->id)
                ->with('success', 'Ingredient updated successfully');
        } else {
            return redirect()->route('admin.ingredient.index')
                ->with('error', 'Ingredient not found');
        }
    }

    public function destroy($id)
    {
        $ingredient = Ingredient::find($id);
        if ($ingredient) {
            $ingredient->delete();
            success_session('Ingredient deleted successfully');
        } else {
            error_session('Ingredient not found');
        }
        return redirect()->route('admin.ingredient.index');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\WebController;
use App\InterestedList;
use DataTables;

class InterestedListController extends WebController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = 'Interest List';
        return view('admin.interested.index', [
            'title' => $title,
            'breadcrumb' => breadcrumb([
                'Interest' => route('admin.interestedlist.index'),
            ]),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::all();
        return view('admin.interested.add', [
            'title' => 'Interest Add',
            'categories'=>$categories,
            'breadcrumb' => breadcrumb([
                'Interest' => route('admin.interestedlist.index'),
            ]),
        ]);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => ['required'],
            'title' => ['required'],

            'image'=>['required'],
        ]);

        if ($request->hasFile('image')) {
            $up = upload_file('image', 'interest_image');
        }
        $data = new InterestedList();
        $data->type = $request->type;
        $data->title = $request->title;
        $data->category_id = $request->category;
        $data->image = $up ?? "";
        $data->save();
        success_session('Interest insert successfully');

        return redirect()->route('admin.interestedlist.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $categories = Category::all();
        $data = InterestedList::find($id);

        if ($data) {
            $title = "Update Interest";

            return view('admin.interested.edit', [
                'title' => $title,
                'data' => $data,
                'categories'=>$categories,
                'breadcrumb' => breadcrumb([
                    'Interest' => route('admin.interestedlist.index'),
                    'edit' => route('admin.interestedlist.edit', $data->id)
                ]),
            ]);
        }
        error_session('Interest  not found');

        return redirect()->route('admin.interestedlist.index');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'type' => ['required'],
            'title' => ['required'],
        ]);

        $data = InterestedList::find($id);

        if ($data) {
            $image = $data->getRawOriginal('image');

            if ($request->hasFile('image')) {
                $up = upload_file('image', 'interest_image');

                if ($up) {
                    $image = $up;
                }
            }

            $data->type = $request->type;
            $data->title = $request->title;
            $data->category_id = $request->category;
            $data->image =  $image;
            $data->update();
            success_session('Interest update successfully');
        } else {
            error_session('Interest not found');
        }

        return redirect()->route('admin.interestedlist.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = InterestedList::where('id', $id)->first();

        if ($data) {
            $data->delete();
            success_session('Interest deleted successfully');
        } else {
            error_session('Interest not found');
        }

        return redirect()->route('admin.interestedlist.index');
    }

    public function listing()
    {
        $data = InterestedList::all();

        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('image', function ($row) {
                return get_fancy_box_html_new($row->image);
            })
            ->addColumn('action', function ($row) {
                $param = [
                    'id' => $row->id,
                    'url' => [
                        'delete' => route('admin.interestedlist.destroy', $row->id),
                        'edit' => route('admin.interestedlist.edit', $row->id),
                    ]
                ];

                return $this->generate_actions_buttons($param);
            })
            ->addColumn('category_id', function ($row) {
              if ($row->category_id == null) {
                return "-";
              } else {
                 return $row->category->name ?: '-';
              }
            })
            ->addColumn('type', function ($row) {
                if ($row->type == 1) {
                    $name = "Cuisines";
                }
                if ($row->type == 2) {
                    $name = "Food And Drink";
                }
                if ($row->type == 3) {
                    $name = "Diet";
                }
                return $name;
            })
            ->rawColumns(['image','category_id','type','action'])
            ->make(true);
    }

}

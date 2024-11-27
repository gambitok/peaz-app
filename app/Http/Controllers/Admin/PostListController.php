<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\WebController;
use App\Post;
use DataTables;
use App\Ingredient;
use App\Instruction;
use Illuminate\Support\Str;

class PostListController extends WebController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public $ingredient_obj;
    public function __construct()
    {
        $this->ingredient_obj = new Ingredient();
    }

    public function index()
    {
        $title = 'Post List';
        return view('admin.post.index', [
            'title' => $title,
            'breadcrumb' => breadcrumb([
                'Post' => route('admin.post.index'),
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
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Post::with(['instruction'])->find($id);
        if ($data) {
            return view('admin.post.view', [
                'title' => 'View post',
                'data' => $data,
                'breadcrumb' => breadcrumb([
                    'post' => route('admin.post.index'),
                    'view' => route('admin.post.show', $id)
                ]),
            ]);
        }
        error_session('post not found');
        return redirect()->route('admin.post.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = Post::where('id', $id)->first();
        if ($data) {
            $data->delete();
            success_session('Post removed!');
        } else {
            error_session('Post not found');
        }
        return redirect()->route('admin.post.index');
    }

    public function listing()
    {
        $data = Post::orderBy('id', 'DESC')->get();
        return Datatables::of($data)
            ->addIndexColumn()
            ->editColumn('caption', function ($row) {
                return "<span title='$row->caption'>".Str::limit($row->caption, 50)."</span>";
             })
            ->addColumn('action', function ($row) {
                $param = [
                    'id' => $row->id,
                    'url' => [
                        'delete' => route('admin.post.destroy', $row->id),
                        // 'edit' => route('admin.interestedlist.edit', $row->id),
                        'view' => route('admin.post.show', $row->id),
                    ]
                ];
                return $this->generate_actions_buttons($param);
            })
            ->rawColumns(['caption','action'])
            ->make(true);
    } 

    public function postDetails(Request $request)
    {
        $data = Ingredient::where('post_id',$request->id)->get();
        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $param = [
                    'id' => $row->id,
                    'url' => [
                        'edit' => route('admin.post.post_details_edit', $row->id),
                        'delete' => route('admin.post.post_details_destroy', $row->id),
                    ]
                ];
                return $this->generate_actions_buttons($param);
            })
            ->rawColumns(["action"])
            ->make(true);
    }

    public function postDetailsEdit($id){
      
        $data = $this->ingredient_obj->find($id);
        if (isset($data) && !empty($data)) {
            return view('admin.post.create', [
                'title' => 'Ingredients Update',
                'breadcrumb' => breadcrumb([
                    'post' => route('admin.post.show',$data->post_id),
                    'edit' => route('admin.post.post_details_edit', $id),
                ]),
            ])->with(compact('data'));
        }
        return redirect()->route('admin.post.show',$data->post_id);
    }

    public function postDetailsUpdate(Request $request ,$id){
        $data =$this->ingredient_obj->find($id);
        if(isset($data) && !empty($data)){
            $return_data = $request->all();
            $this->ingredient_obj->saveIngredient($return_data,$id,$data);
            success_session('Ingredients updated successfully');
        }
        else{
            error_session('Ingredients not found');
        }
        return redirect()->route('admin.post.show',$data->post_id);
    }


    public function postDetailsDestroy($id){
        $data = Ingredient::where('id', $id)->first();
        if ($data) {
            $data->delete();
            success_session('Ingredients Deleted successfully');
        } else {
            error_session('Ingredients not found');
        }
        return redirect()->route('admin.post.show',$data->post_id);
    }

    public function instruction(Request $request)
    {
        $data = Instruction::where("post_id",$request->id)->orderBy('id','ASC')->get();
        return Datatables::of($data)
            ->addIndexColumn()
            ->editColumn('title', function ($row) {
                return  !empty($row->title) ? $row->title : '-';
             })
             ->editColumn('type', function ($row) {
                return  !empty($row->type) ? $row->type : '-';
             })
             ->editColumn('file', function ($row) {
               return  '<a href="'.$row->file.'" target="_blank">Show File</a>';
            })
            ->editColumn('thumbnail', function ($row) {
                return  '<a href="'.$row->thumbnail.'" target="_blank">Show File</a>';
             })
            ->editColumn('description', function ($row) {
                return "<span title='$row->description'>".Str::limit($row->description, 50)."</span>";
             })
             
             ->rawColumns(['title','file',"description","thumbnail","type"])
            ->make(true);
    }

}

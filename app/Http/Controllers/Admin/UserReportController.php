<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\ReportStatus;
use Illuminate\Http\Request;
use DataTables;

class UserReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = 'User Report';
        return view('admin.report.index', [
            'title' => $title,
            'breadcrumb' => breadcrumb([
                'Report' => route('admin.user_report.index'),
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
        //
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
        //
    }

    public function listing()
    {
        $data = ReportStatus::all();
        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('user_id', function ($row) {
                return $row->user->username ? $row->user->username : '-';
            })
            ->addColumn('post_id', function ($row) {
                return $row->post->title ? $row->post->title : '-';
            })
            ->addColumn('report_id', function ($row) {
                
                return $row->report->title ? $row->report->title : '-';
            })
            ->addColumn('status', function ($row) {
                if($row->status == "resolve"){
                    return  "<span class='badge badge-success'>Reported</span>";
                }else{
                    return  "<span class='badge badge-success'>" . ucfirst($row->status) . "</span>";
                }
            })
            ->rawColumns(['user_id',"post_id",'report_id','status'])
            ->make(true);
    }
}

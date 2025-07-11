<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\ResponseController;
use App\Report;
use App\ReportStatus;
use Illuminate\Http\Request;

class ReportController extends ResponseController
{
    public function reportList()
    {
        $reportList = Report::all();

        return response()->json([
            'status' => 'success',
            'message' => __('api.suc_report_list'),
            'data' => $reportList,
        ], 200);
    }

    public function reportStatus(Request $request)
    {
        $user = $request->user();

        $rules = [
            "post_id" => ['required', 'exists:posts,id'],
            'report_id' => ['required', 'exists:reports,id'],
        ];

        $request->validate($rules);

        $reportStatus = ReportStatus::create([
            "user_id" => $user->id,
            "post_id" => $request->post_id,
            "report_id" => $request->report_id,
            "status" => "resolve",
        ]);

        $data = ReportStatus::with('report')->where('id', $reportStatus->id)->first();

        return response()->json([
            'status' => 'success',
            'message' => __('api.suc_report_status_list'),
            'data' => $data,
        ], 200);
    }

}
